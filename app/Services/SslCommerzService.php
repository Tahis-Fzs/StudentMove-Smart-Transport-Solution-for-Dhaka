<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslCommerzService
{
    public function isConfigured(): bool
    {
        return filled(config('services.sslcommerz.store_id'))
            && filled(config('services.sslcommerz.store_password'));
    }

    public function isSandbox(): bool
    {
        return (bool) config('services.sslcommerz.sandbox', true);
    }

    /** @return array{configured:bool,sandbox:bool,currency:string,store_id_hint:?string,init_url:string} */
    public function configSummary(): array
    {
        $storeId = (string) config('services.sslcommerz.store_id', '');

        return [
            'configured' => $this->isConfigured(),
            'sandbox' => $this->isSandbox(),
            'currency' => (string) config('services.sslcommerz.currency', 'BDT'),
            'store_id_hint' => $storeId !== ''
                ? substr($storeId, 0, min(4, strlen($storeId))) . '***'
                : null,
            'init_url' => $this->initUrl(),
        ];
    }

    /**
     * Ping SSLCommerz with a throwaway session (does not charge).
     *
     * @return array{ok:bool,gateway_url?:string,error?:string}
     */
    public function probe(): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'SSLCommerz is not configured.'];
        }

        $tranId = 'SMCHK' . strtoupper(bin2hex(random_bytes(4)));

        $result = $this->initiate([
            'tran_id' => $tranId,
            'amount' => 10,
            'product_name' => 'StudentMove connectivity check',
            'success_url' => route('subscription.ssl.success'),
            'fail_url' => route('subscription.ssl.fail'),
            'cancel_url' => route('subscription.ssl.cancel'),
            'customer' => [
                'name' => 'Connectivity Check',
                'email' => 'check@studentmove.test',
                'phone' => '01700000000',
                'address' => 'Dhaka',
                'city' => 'Dhaka',
            ],
        ]);

        if ($result['ok']) {
            return ['ok' => true, 'gateway_url' => $result['gateway_url'] ?? null];
        }

        return ['ok' => false, 'error' => $result['error'] ?? 'Probe failed.'];
    }

    protected function initUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
            : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
    }

    protected function validationUrl(): string
    {
        return $this->isSandbox()
            ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';
    }

    /**
     * Start a hosted checkout session (same pattern as Star Cineplex / SSLCommerz Easy Checkout).
     *
     * @param  array{tran_id:string,amount:float|int|string,customer:array,product_name:string,success_url:string,fail_url:string,cancel_url:string,ipn_url?:string}  $payload
     * @return array{ok:bool,gateway_url?:string,sessionkey?:string,error?:string,raw?:array}
     */
    public function initiate(array $payload): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'SSLCommerz is not configured.'];
        }

        $customer = $payload['customer'] ?? [];

        $body = [
            'store_id' => config('services.sslcommerz.store_id'),
            'store_passwd' => config('services.sslcommerz.store_password'),
            'total_amount' => number_format((float) $payload['amount'], 2, '.', ''),
            'currency' => config('services.sslcommerz.currency', 'BDT'),
            'tran_id' => $payload['tran_id'],
            'success_url' => $payload['success_url'],
            'fail_url' => $payload['fail_url'],
            'cancel_url' => $payload['cancel_url'],
            'cus_name' => $customer['name'] ?? 'Student',
            'cus_email' => $customer['email'] ?? 'student@example.com',
            'cus_add1' => $customer['address'] ?? 'Dhaka',
            'cus_city' => $customer['city'] ?? 'Dhaka',
            'cus_country' => 'Bangladesh',
            'cus_phone' => $customer['phone'] ?? '01700000000',
            'shipping_method' => 'NO',
            'product_name' => $payload['product_name'] ?? 'StudentMove Pass',
            'product_category' => 'Subscription',
            'product_profile' => 'non-physical-goods',
        ];

        if (!empty($payload['ipn_url'])) {
            $body['ipn_url'] = $payload['ipn_url'];
        }

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->post($this->initUrl(), $body);

            $data = $response->json() ?? [];

            if (($data['status'] ?? '') === 'SUCCESS' && !empty($data['GatewayPageURL'])) {
                return [
                    'ok' => true,
                    'gateway_url' => $data['GatewayPageURL'],
                    'sessionkey' => $data['sessionkey'] ?? null,
                    'raw' => $data,
                ];
            }

            $error = $data['failedreason'] ?? $data['error'] ?? 'Could not start SSLCommerz session.';
            Log::warning('SSLCommerz initiate failed', ['response' => $data]);

            return ['ok' => false, 'error' => $error, 'raw' => $data];
        } catch (\Throwable $e) {
            Log::error('SSLCommerz initiate exception', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'Payment gateway unavailable. Please try again.'];
        }
    }

    /**
     * Validate payment with SSLCommerz using val_id from the success/IPN callback.
     *
     * @return array{ok:bool,data?:array,error?:string}
     */
    public function validateByValId(string $valId): array
    {
        if (!$this->isConfigured()) {
            return ['ok' => false, 'error' => 'SSLCommerz is not configured.'];
        }

        try {
            $response = Http::asForm()
                ->timeout(30)
                ->get($this->validationUrl(), [
                    'val_id' => $valId,
                    'store_id' => config('services.sslcommerz.store_id'),
                    'store_passwd' => config('services.sslcommerz.store_password'),
                    'format' => 'json',
                ]);

            $data = $response->json() ?? [];
            $status = strtoupper((string) ($data['status'] ?? ''));

            if (in_array($status, ['VALID', 'VALIDATED'], true)) {
                return ['ok' => true, 'data' => $data];
            }

            return [
                'ok' => false,
                'error' => 'Payment validation failed: ' . ($data['status'] ?? 'unknown'),
                'data' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('SSLCommerz validation exception', ['error' => $e->getMessage()]);

            return ['ok' => false, 'error' => 'Could not verify payment with SSLCommerz.'];
        }
    }
}
