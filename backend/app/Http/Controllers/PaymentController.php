<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\PaymentService;
use App\Services\SystemLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $systemLogService;

    public function __construct(PaymentService $paymentService, SystemLogService $systemLogService)
    {
        $this->paymentService = $paymentService;
        $this->systemLogService = $systemLogService;
    }

    /**
     * @OA\Post(
     *     path="/payments/create",
     *     summary="Create a payment intent",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_type"},
     *             @OA\Property(property="payment_type", type="string", enum={"COURSE", "MEMBERSHIP"}),
     *             @OA\Property(property="course_id", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="membership_plan", type="string", enum={"PREMIUM"}, nullable=true),
     *             @OA\Property(property="payment_method", type="string", enum={"STRIPE"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment created successfully")
     * )
     */
    public function createPayment(Request $request)
    {
        // Debug log
        Log::info('Payment request headers:', $request->headers->all());
        Log::info('Authorization header:', ['auth' => $request->header('Authorization')]);
        
        $request->validate([
            'payment_type' => 'required|in:COURSE,MEMBERSHIP',
            'course_id' => 'required_if:payment_type,COURSE|uuid|exists:courses,id',
            'membership_plan' => 'required_if:payment_type,MEMBERSHIP|in:PREMIUM',
            'payment_method' => 'required|in:STRIPE,PAYPAL',
        ]);

        Log::info('Payment request:', (array) $request->all());

        $user = $request->user();
        $amount = 0;

        // Calculate amount
        if ($request->payment_type === 'COURSE') {
            $course = Course::findOrFail($request->course_id);
            // Sử dụng giá sau giảm giá nếu có discount
            $amount = $course->getDiscountedPrice() ?? 0;
            if ($amount == 0) {
                return response()->json([
                    'message' => 'This course is free'
                ], 400);
            }
        } else {
            $amount = 24.00;
        }

        // Nếu là STRIPE thì xử lý qua payment method id từ Stripe Elements
        if ($request->payment_method === 'STRIPE') {
            $stripePaymentMethodId = $request->input('stripe_payment_method_id');
            if (!$stripePaymentMethodId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thiếu Stripe payment method id.',
                ], 400);
            }
            Log::info('Stripe payment method id:', ['id' => $stripePaymentMethodId]);
            // Tạo bản ghi Payment trước khi tạo Stripe Intent
            $payment = Payment::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'payment_type' => $request->payment_type,
                'course_id' => $request->course_id,
                'membership_plan' => $request->membership_plan,
                'payment_method' => $request->payment_method,
                'amount' => $amount,
                'currency' => 'USD',
                'status' => 'PENDING',
            ]);
            try {
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
                $intent = $stripe->paymentIntents->create([
                    'amount' => intval($amount * 100),
                    'currency' => 'usd',
                    'payment_method' => $stripePaymentMethodId,
                    'confirm' => true,
                    'automatic_payment_methods' => [
                        'enabled' => true,
                        'allow_redirects' => 'never',
                    ],
                    'metadata' => [
                        'payment_id' => $payment->id
                    ],
                ]);
                Log::info('Stripe intent:', (array) $intent);
                // Có thể cập nhật payment với intent id nếu cần
                // Map status Stripe về đúng giá trị hợp lệ của DB
                $stripeStatus = strtolower($intent->status);
                if ($stripeStatus === 'succeeded') {
                    $dbStatus = 'COMPLETED';
                } elseif ($stripeStatus === 'canceled') {
                    $dbStatus = 'FAILED';
                } else {
                    $dbStatus = 'PENDING';
                }
                $payment->update([
                    'transaction_id' => $intent->id,
                    'status' => $dbStatus,
                    'paid_at' => $dbStatus === 'COMPLETED' ? now() : null,
                ]);
                
                // If payment succeeded and it's a course payment, create enrollment
                if ($dbStatus === 'COMPLETED' && $request->payment_type === 'COURSE' && $request->course_id) {
                    Enrollment::firstOrCreate([
                        'student_id' => $user->id,
                        'course_id' => $request->course_id,
                    ], [
                        'id' => Str::uuid(),
                        'status' => 'ACTIVE',
                        'progress' => 0,
                        'enrolled_at' => now(),
                    ]);
                    
                    Log::info('Enrollment created after successful payment', [
                        'user_id' => $user->id,
                        'course_id' => $request->course_id,
                        'payment_id' => $payment->id,
                    ]);
                }
                
                // If payment succeeded and it's a membership payment, upgrade to Premium
                if ($dbStatus === 'COMPLETED' && $request->payment_type === 'MEMBERSHIP' && $request->membership_plan === 'PREMIUM') {
                    $user->membership_tier = 'PREMIUM';
                    $user->membership_expires_at = now()->addDays(30);
                    $user->save();
                    
                    Log::info('User upgraded to Premium membership', [
                        'user_id' => $user->id,
                        'expires_at' => $user->membership_expires_at,
                        'payment_id' => $payment->id,
                    ]);
                }
                
                return response()->json([
                    'success' => true,
                    'payment' => $payment->fresh(['course', 'user']),
                    'amount' => $amount,
                    'stripe_intent' => $intent,
                    'enrolled' => $dbStatus === 'COMPLETED' && $request->payment_type === 'COURSE',
                ]);
            } catch (\Exception $e) {
                Log::error('Stripe error: ' . $e->getMessage(), []);
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
        }
        elseif ($request->payment_method === 'PAYPAL') {
            Log::info('Processing PayPal payment');
            
            // Create pending payment record
            $payment = Payment::create([
                'id' => Str::uuid(),
                'user_id' => $user->id,
                'payment_type' => $request->payment_type,
                'course_id' => $request->course_id,
                'membership_plan' => $request->membership_plan,
                'payment_method' => 'PAYPAL',
                'amount' => $amount,
                'currency' => 'USD',
                'status' => 'PENDING',
            ]);

            // Create PayPal Order
            $result = $this->paymentService->createPayPalOrder($amount, 'USD');

            if ($result['success']) {
                $payment->update([
                    'transaction_id' => $result['order_id'],
                    'payment_details' => [
                        'order_id' => $result['order_id'],
                        'links' => $result['links']
                    ]
                ]);

                return response()->json([
                    'success' => true,
                    'payment' => $payment,
                    'paypal_order_id' => $result['order_id'],
                    'links' => $result['links']
                ]);
            } else {
                $payment->update(['status' => 'FAILED']);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create PayPal order: ' . ($result['error'] ?? 'Unknown error')
                ], 500);
            }
        }
    }


    /**
     * @OA\Post(
     *     path="/payments/{id}/paypal/capture",
     *     summary="Capture PayPal payment",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="paypal_order_id", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment captured")
     * )
     */
    public function capturePayPalPayment(Request $request, $id)
    {
        $request->validate([
            'paypal_order_id' => 'required|string',
        ]);

        $payment = Payment::findOrFail($id);
        $user = $request->user();

        if ($payment->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            Log::info('Capturing PayPal payment', ['payment_id' => $id, 'order_id' => $request->paypal_order_id]);
            
            $result = $this->paymentService->capturePayPalOrder($request->paypal_order_id);

            if ($result['success'] && $result['status'] === 'COMPLETED') {
                $payment->update([
                    'status' => 'COMPLETED',
                    'paid_at' => now(),
                    'payment_details' => array_merge($payment->payment_details ?? [], [
                        'capture_id' => $result['id'],
                        'payer' => $result['payer']
                    ])
                ]);

                // Enroll user if course payment
                if ($payment->payment_type === 'COURSE' && $payment->course_id) {
                    Enrollment::firstOrCreate([
                        'student_id' => $user->id,
                        'course_id' => $payment->course_id,
                    ], [
                        'id' => Str::uuid(),
                        'status' => 'ACTIVE',
                        'progress' => 0,
                        'enrolled_at' => now(),
                    ]);
                }

                // Upgrade to Premium if membership payment
                if ($payment->payment_type === 'MEMBERSHIP' && $payment->membership_plan === 'PREMIUM') {
                    $user->membership_tier = 'PREMIUM';
                    $user->membership_expires_at = now()->addDays(30);
                    $user->save();
                    
                    Log::info('User upgraded to Premium membership via PayPal', [
                        'user_id' => $user->id,
                        'expires_at' => $user->membership_expires_at,
                        'payment_id' => $payment->id,
                    ]);
                }

                // Log completion
                if (isset($this->systemLogService)) {
                    $course = $payment->course_id ? Course::find($payment->course_id) : null;
                    $this->systemLogService->logAction(
                        'INFO',
                        'PayPal Payment Completed',
                        $user->id,
                        [
                            'payment_id' => $payment->id,
                            'amount' => $payment->amount,
                            'currency' => $payment->currency,
                            'paypal_order_id' => $request->paypal_order_id
                        ],
                        $request->ip(),
                        $request->userAgent()
                    );
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed successfully',
                    'payment' => $payment->fresh(['course', 'user'])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment capture failed or not completed',
                    'details' => $result
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('PayPal capture failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to capture payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/payments/{id}/stripe/create-intent",
     *     summary="Create Stripe Payment Intent",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Payment intent created")
     * )
     */
    public function createStripeIntent(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $paymentIntent = $this->paymentService->createStripeIntent(
                $payment->amount,
                strtolower($payment->currency),
                [
                    'payment_id' => $payment->id,
                    'user_id' => $payment->user_id,
                    'payment_type' => $payment->payment_type,
                ]
            );

            // Save payment intent ID
            $payment->update([
                'transaction_id' => $paymentIntent->id,
                'payment_details' => [
                    'payment_intent_id' => $paymentIntent->id,
                    'client_secret' => $paymentIntent->client_secret,
                ],
            ]);

            return response()->json([
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe payment intent creation failed', (array) [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/payments/{id}/stripe/complete",
     *     summary="Complete Stripe payment",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_intent_id", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment completed")
     * )
     */
    public function completeStripePayment(Request $request, $id)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $payment = Payment::findOrFail($id);
        
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $paymentIntentId = $request->payment_intent_id;
            $isTestMode = str_starts_with($paymentIntentId, 'TEST_STRIPE_');
            
            if ($isTestMode) {
                // TEST MODE: Auto-approve without calling Stripe API
                Log::info('Processing Stripe payment in TEST MODE', (array) [
                    'payment_id' => $payment->id,
                    'test_intent_id' => $paymentIntentId,
                ]);
                
                $payment->update([
                    'status' => 'COMPLETED',
                    'paid_at' => now(),
                    'transaction_id' => $paymentIntentId,
                    'payment_details' => [
                        'payment_intent_id' => $paymentIntentId,
                        'test_mode' => true,
                        'note' => 'Test payment - no real charge',
                    ],
                ]);
            } else {
                // PRODUCTION MODE: Verify with Stripe API
                $paymentIntent = $this->paymentService->retrieveStripeIntent($paymentIntentId);

                if ($paymentIntent->status !== 'succeeded') {
                    return response()->json([
                        'message' => 'Payment not completed',
                        'status' => $paymentIntent->status,
                    ], 400);
                }
                
                $payment->update([
                    'status' => 'COMPLETED',
                    'paid_at' => now(),
                    'transaction_id' => $paymentIntent->id,
                    'payment_details' => [
                        'payment_intent_id' => $paymentIntent->id,
                        'charge_id' => $paymentIntent->latest_charge ?? null,
                        'payment_method_types' => $paymentIntent->payment_method_types,
                        'amount_received' => $paymentIntent->amount_received,
                    ],
                ]);
                
                Log::info('Stripe payment completed successfully (PRODUCTION)', (array) [
                    'payment_id' => $payment->id,
                    'payment_intent_id' => $paymentIntent->id,
                    'amount' => $payment->amount,
                ]);
            }

            // If course payment, enroll user
            if ($payment->payment_type === 'COURSE' && $payment->course_id) {
                Enrollment::firstOrCreate([
                    'student_id' => $payment->user_id,
                    'course_id' => $payment->course_id,
                ], [
                    'id' => Str::uuid(),
                    'status' => 'ACTIVE',
                    'progress' => 0,
                    'enrolled_at' => now(),
                ]);
            }

            // Log payment completion to system_logs
            $course = $payment->course_id ? Course::find($payment->course_id) : null;
            if (isset($this->systemLogService)) {
                $this->systemLogService->logAction(
                    'INFO',
                    'Payment Completed',
                    $payment->user_id,
                    [
                        'payment_id' => $payment->id,
                        'payment_type' => $payment->payment_type,
                        'amount' => $payment->amount,
                        'currency' => $payment->currency,
                        'course_id' => $payment->course_id,
                        'course_title' => $course?->title,
                        'transaction_id' => $payment->transaction_id,
                        'test_mode' => $isTestMode,
                    ],
                    $request->ip(),
                    $request->userAgent()
                );
            }

            return response()->json([
                'message' => 'Payment completed successfully',
                'payment' => $payment->fresh(['course', 'user']),
                'test_mode' => $isTestMode,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe payment completion failed', (array) [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to complete payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/payments/{id}",
     *     summary="Get payment details",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Payment details")
     * )
     */
    public function show(Request $request, $id)
    {
        $payment = Payment::with(['course', 'user'])->findOrFail($id);
        
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($payment);
    }

    /**
     * @OA\Get(
     *     path="/payments",
     *     summary="Get user's payment history",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Payment history")
     * )
     */
    public function index(Request $request)
    {
        $payments = Payment::with(['course'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($payments);
    }

        /**
         * Stripe Webhook: Xử lý sự kiện từ Stripe
         */
        public function stripeWebhook(Request $request)
        {
            $payload = $request->getContent();
            $sigHeader = $request->header('Stripe-Signature');
            $webhookSecret = env('STRIPE_WEBHOOK_SECRET');

            try {
                $event = \Stripe\Webhook::constructEvent(
                    $payload,
                    $sigHeader,
                    $webhookSecret
                );
            } catch (\UnexpectedValueException $e) {
                // Invalid payload
                return response('Invalid payload', 400);
            } catch (\Stripe\Exception\SignatureVerificationException $e) {
                // Invalid signature
                return response('Invalid signature', 400);
            }

            // Xử lý sự kiện payment_intent.succeeded
            if ($event->type === 'payment_intent.succeeded') {
                $intent = $event->data->object;
                $transactionId = $intent->id;
                $metadata = $intent->metadata ?? [];
                $paymentId = $metadata['payment_id'] ?? null;

                if ($paymentId) {
                    $payment = Payment::where('id', $paymentId)->first();
                    if ($payment) {
                        $payment->status = 'SUCCEEDED';
                        $payment->transaction_id = $transactionId;
                        $payment->paid_at = now();
                        $payment->save();
                    }
                }
            }

            return response('Webhook received', 200);
        }
}
