<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
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
     *             @OA\Property(property="payment_method", type="string", enum={"PAYPAL", "VNPAY"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment created successfully")
     * )
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'payment_type' => 'required|in:COURSE,MEMBERSHIP',
            'course_id' => 'required_if:payment_type,COURSE|uuid|exists:courses,id',
            'membership_plan' => 'required_if:payment_type,MEMBERSHIP|in:PREMIUM',
            'payment_method' => 'required|in:PAYPAL,VNPAY,STRIPE',
        ]);

    Log::info('Payment request:', (array) $request->all());

        $user = $request->user();
        $amount = 0;

        // Calculate amount
        if ($request->payment_type === 'COURSE') {
            $course = Course::findOrFail($request->course_id);
            $amount = $course->price ?? 0;
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
                ]);
                return response()->json([
                    'success' => true,
                    'payment' => $payment,
                    'amount' => $amount,
                    'stripe_intent' => $intent,
                ]);
            } catch (\Exception $e) {
                Log::error('Stripe error: ' . $e->getMessage(), []);
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
        }
        // ...existing code...
        if ($request->payment_method !== 'STRIPE') {
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
            return response()->json([
                'success' => true,
                'payment' => $payment,
                'amount' => $amount,
            ]);
        }
    }

    /**
     * @OA\Post(
     *     path="/payments/{id}/paypal/create-order",
     *     summary="Create PayPal Order",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="PayPal order created")
     * )
     */
    public function createPayPalOrder(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->paymentService->createPayPalOrder(
                $payment->amount,
                $payment->currency,
                [
                    'payment_id' => $payment->id,
                    'description' => $payment->payment_type === 'COURSE' 
                        ? 'Course Payment: ' . ($payment->course->title ?? 'N/A')
                        : 'Premium Membership',
                ]
            );

            if (!$result['success']) {
                return response()->json([
                    'message' => 'Failed to create PayPal order',
                    'error' => $result['error'],
                ], 500);
            }

            // Update payment with PayPal order ID
            $payment->update([
                'transaction_id' => $result['order_id'],
                'payment_details' => [
                    'paypal_order_id' => $result['order_id'],
                    'status' => $result['status'],
                ],
            ]);

            return response()->json([
                'order_id' => $result['order_id'],
                'status' => $result['status'],
                'links' => $result['links'],
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal order creation failed', (array) [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to create PayPal order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/payments/{id}/paypal/capture",
     *     summary="Capture PayPal Order",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="order_id", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Payment captured successfully")
     * )
     */
    public function capturePayPalOrder(Request $request, $id)
    {
        $request->validate([
            'order_id' => 'required|string',
        ]);

        $payment = Payment::findOrFail($id);
        
        if ($payment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            $result = $this->paymentService->capturePayPalOrder($request->order_id);

            if (!$result['success']) {
                return response()->json([
                    'message' => 'Failed to capture PayPal order',
                    'error' => $result['error'],
                ], 500);
            }

            // Update payment status
            $payment->update([
                'status' => 'COMPLETED',
                'paid_at' => now(),
                'transaction_id' => $result['order_id'],
                'payment_details' => [
                    'paypal_order_id' => $result['order_id'],
                    'status' => $result['status'],
                    'payer' => $result['payer'],
                    'purchase_units' => $result['purchase_units'],
                ],
            ]);

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

            return response()->json([
                'message' => 'Payment completed successfully',
                'payment' => $payment->fresh(['course', 'user']),
            ]);
        } catch (\Exception $e) {
            Log::error('PayPal order capture failed', (array) [
                'payment_id' => $payment->id,
                'order_id' => $request->order_id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to capture PayPal order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/payments/{id}/vnpay/callback",
     *     summary="VNPay callback",
     *     tags={"Payments"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string")),
     *     @OA\Response(response=200, description="Payment processed")
     * )
     */
    public function vnpayCallback(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        // Verify VNPay signature
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = $request->except('vnp_SecureHash');
        ksort($inputData);
        
        $hashData = urldecode(http_build_query($inputData));
        $vnpHashSecret = env('VNPAY_HASH_SECRET');
        $secureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);

        if ($secureHash === $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                // Payment successful
                $payment->update([
                    'status' => 'COMPLETED',
                    'transaction_id' => $request->vnp_TransactionNo,
                    'paid_at' => now(),
                    'payment_details' => $inputData,
                ]);

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

                return redirect(env('FRONTEND_URL') . '/payment/success?payment_id=' . $payment->id);
            } else {
                // Payment failed
                $payment->update([
                    'status' => 'FAILED',
                    'payment_details' => $inputData,
                ]);

                return redirect(env('FRONTEND_URL') . '/payment/failed?payment_id=' . $payment->id);
            }
        }

        return response()->json(['message' => 'Invalid signature'], 400);
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
