<?php

namespace App\Services\LiqPay;

use Illuminate\Support\Facades\Http;

class LiqPayClient
{
    private const CHECKOUT_URL = 'https://www.liqpay.ua/api/3/checkout';

    private const REQUEST_URL = 'https://www.liqpay.ua/api/request';

    public function __construct(
        private readonly string $publicKey,
        private readonly string $privateKey,
    ) {}

    /**
     * Build the {data, signature} pair used both for the hosted checkout form
     * and for direct server-to-server API calls (LiqPay uses the same scheme).
     */
    public function sign(array $params): array
    {
        $params['public_key'] = $this->publicKey;
        $params['version'] ??= 3;

        $data = base64_encode(json_encode($params));
        $signature = base64_encode(sha1($this->privateKey.$data.$this->privateKey, true));

        return [$data, $signature];
    }

    /**
     * Verify a signature received from LiqPay (webhook callback).
     */
    public function verifySignature(string $data, string $signature): bool
    {
        $expected = base64_encode(sha1($this->privateKey.$data.$this->privateKey, true));

        return hash_equals($expected, $signature);
    }

    public function decode(string $data): array
    {
        return json_decode(base64_decode($data), true) ?? [];
    }

    /**
     * Build the auto-submit checkout form fields for the customer to pay an order.
     */
    public function checkoutFields(array $params): array
    {
        [$data, $signature] = $this->sign($params);

        return ['url' => self::CHECKOUT_URL, 'data' => $data, 'signature' => $signature];
    }

    /**
     * Server-to-server call, e.g. action=paytocard for executor payouts.
     */
    public function request(array $params): array
    {
        [$data, $signature] = $this->sign($params);

        $response = Http::asForm()->timeout(15)->post(self::REQUEST_URL, [
            'data' => $data,
            'signature' => $signature,
        ]);

        return $response->json() ?? [];
    }
}
