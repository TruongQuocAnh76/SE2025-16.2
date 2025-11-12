<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use PaypalServerSdkLib\PaypalServerSdkClient;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Models\OrderRequest;
use PaypalServerSdkLib\Models\PurchaseUnitRequest;
use PaypalServerSdkLib\Models\AmountWithBreakdown;
use PaypalServerSdkLib\Controllers\OrdersController;
use PaypalServerSdkLib\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function __construct()
    {
        // Initialize Stripe
        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    }

    // ==========================================
    // STRIPE METHODS
    // ==========================================

    /**
     * Create Stripe Payment Intent
     */
    public function createStripeIntent($amount, $currency = 'usd', $metadata = [])
    {
        return PaymentIntent::create([
            'amount' => $amount * 100, // Convert to cents
            'currency' => $currency,
            'automatic_payment_methods' => [
                'enabled' => true,
            ],
            'metadata' => $metadata,
        ]);
    }

    /**
     * Retrieve Stripe Payment Intent
     */
    public function retrieveStripeIntent($paymentIntentId)
    {
        return PaymentIntent::retrieve($paymentIntentId);
    }

    /**
     * Confirm Stripe Payment Intent
     */
    public function confirmStripeIntent($paymentIntentId, $paymentMethodId)
    {
        return PaymentIntent::update($paymentIntentId, [
            'payment_method' => $paymentMethodId,
        ]);
    }

    // ==========================================
    // PAYPAL METHODS
    // ==========================================

    /**
     * Create PayPal Order
     */
    public function createPayPalOrder($amount, $currency = 'USD', $metadata = [])
    {
        try {
            $paypalClient = PaypalServerSdkClientBuilder::init()
                ->clientCredentialsAuthCredentials(
                    env('PAYPAL_CLIENT_ID'),
                    env('PAYPAL_CLIENT_SECRET')
                )
                ->environment(env('PAYPAL_MODE', 'sandbox'))
                ->build();
            $paypalOrdersController = $paypalClient->getOrdersController();

            // Create purchase unit with amount
            $amountWithBreakdown = new AmountWithBreakdown($currency, (string)$amount);
            $purchaseUnit = new PurchaseUnitRequest($amountWithBreakdown);
            $purchaseUnit->setDescription($metadata['description'] ?? 'CertChain Payment');
            $purchaseUnit->setReferenceId($metadata['payment_id'] ?? uniqid('payment_'));

            // Create order request
            $orderRequest = new OrderRequest('CAPTURE', [$purchaseUnit]);

            // Create order via PayPal API
            $response = $paypalOrdersController->ordersCreate($orderRequest);
            $result = $response->getResult();

            Log::info('PayPal order created', [
                'order_id' => $result->getId(),
                'status' => $result->getStatus(),
                'amount' => $amount,
            ]);

            return [
                'success' => true,
                'order_id' => $result->getId(),
                'status' => $result->getStatus(),
                'links' => $result->getLinks(),
            ];
        } catch (ApiException $e) {
            Log::error('PayPal create order failed', [
                'error' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Capture PayPal Order
     */
    public function capturePayPalOrder($orderId)
    {
        try {
            $paypalClient = PaypalServerSdkClientBuilder::init()
                ->clientCredentialsAuthCredentials(
                    env('PAYPAL_CLIENT_ID'),
                    env('PAYPAL_CLIENT_SECRET')
                )
                ->environment(env('PAYPAL_MODE', 'sandbox'))
                ->build();
            $paypalOrdersController = $paypalClient->getOrdersController();

            $response = $paypalOrdersController->ordersCapture($orderId);
            $result = $response->getResult();

            Log::info('PayPal order captured', [
                'order_id' => $result->getId(),
                'status' => $result->getStatus(),
            ]);

            return [
                'success' => true,
                'order_id' => $result->getId(),
                'status' => $result->getStatus(),
                'payer' => $result->getPayer(),
                'purchase_units' => $result->getPurchaseUnits(),
            ];
        } catch (ApiException $e) {
            Log::error('PayPal capture order failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
                'response' => $e->getResponseBody(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get PayPal Order Details
     */
    public function getPayPalOrderDetails($orderId)
    {
        try {
            $paypalClient = PaypalServerSdkClientBuilder::init()
                ->clientCredentialsAuthCredentials(
                    env('PAYPAL_CLIENT_ID'),
                    env('PAYPAL_CLIENT_SECRET')
                )
                ->environment(env('PAYPAL_MODE', 'sandbox'))
                ->build();
            $paypalOrdersController = $paypalClient->getOrdersController();

            $response = $paypalOrdersController->ordersGet($orderId);
            $result = $response->getResult();

            return [
                'success' => true,
                'order_id' => $result->getId(),
                'status' => $result->getStatus(),
                'payer' => $result->getPayer(),
                'purchase_units' => $result->getPurchaseUnits(),
                'create_time' => $result->getCreateTime(),
                'update_time' => $result->getUpdateTime(),
            ];
        } catch (ApiException $e) {
            Log::error('PayPal get order details failed', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
