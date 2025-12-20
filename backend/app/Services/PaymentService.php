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

    /**
     * Get PayPal Order Details
     * @param string $orderId
     * @return array
     */
    public function getPayPalOrderDetails($orderId)
    {
        try {
            $authBuilder = \PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder::init(
                env('PAYPAL_CLIENT_ID'),
                env('PAYPAL_CLIENT_SECRET')
            );
            $mode = strtolower(env('PAYPAL_MODE', 'sandbox')) === 'live'
                ? \PaypalServerSdkLib\Environment::LIVE
                : \PaypalServerSdkLib\Environment::SANDBOX;
            $paypalClient = PaypalServerSdkClientBuilder::init()
                ->clientCredentialsAuthCredentials($authBuilder)
                ->environment($mode)
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
                'full_response' => $result,
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
        } catch (\Exception $ex) {
            Log::error('PayPal get order details unknown exception', [
                'order_id' => $orderId,
                'error' => $ex->getMessage(),
                'trace' => $ex->getTraceAsString(),
            ]);
            return [
                'success' => false,
                'error' => $ex->getMessage(),
            ];
        }
    }

    /**
     * Get PayPal Client
     */
    public function getPayPalClient()
    {
        $authBuilder = \PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder::init(
            env('PAYPAL_CLIENT_ID'),
            env('PAYPAL_CLIENT_SECRET')
        );

        $mode = strtolower(env('PAYPAL_MODE', 'sandbox')) === 'live'
            ? \PaypalServerSdkLib\Environment::LIVE
            : \PaypalServerSdkLib\Environment::SANDBOX;

        return PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials($authBuilder)
            ->environment($mode)
            ->build();
    }

    /**
     * Create PayPal Order
     */
    public function createPayPalOrder($amount, $currency = 'USD')
    {
        try {
            $client = $this->getPayPalClient();
            $ordersController = $client->getOrdersController();

            $orderRequest = new OrderRequest(
                \PaypalServerSdkLib\Models\CheckoutPaymentIntent::CAPTURE,
                [
                    new PurchaseUnitRequest(
                        new AmountWithBreakdown($currency, number_format($amount, 2, '.', ''))
                    )
                ]
            );

            $response = $ordersController->createOrder(['body' => $orderRequest]);
            $result = $response->getResult();

            return [
                'success' => true,
                'order_id' => $result->getId(),
                'status' => $result->getStatus(),
                'links' => $result->getLinks()
            ];

        } catch (\Exception $e) {
            Log::error('PayPal create order failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Capture PayPal Order
     */
    public function capturePayPalOrder($orderId)
    {
        try {
            $client = $this->getPayPalClient();
            $ordersController = $client->getOrdersController();

            $response = $ordersController->captureOrder([
                'id' => $orderId,
                'prefer' => 'return=representation'
            ]);
            $result = $response->getResult();

            return [
                'success' => true,
                'status' => $result->getStatus(),
                'id' => $result->getId(),
                'payer' => $result->getPayer(),
                'purchase_units' => $result->getPurchaseUnits()
            ];

        } catch (\Exception $e) {
            Log::error('PayPal capture order failed: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
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
}
