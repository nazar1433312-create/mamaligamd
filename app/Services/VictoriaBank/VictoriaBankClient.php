<?php

namespace App\Services\VictoriaBank;

use Fruitware\VictoriaBankGateway\VictoriaBank\ResponseInterface;
use Fruitware\VictoriaBankGateway\VictoriaBankGateway;

class VictoriaBankClient
{
    private VictoriaBankGateway $gateway;

    public function __construct()
    {
        $this->gateway = new VictoriaBankGateway();
        $this->gateway->configureFromEnv(config('services.victoriabank.cert_dir'));
    }

    /**
     * Redirects the browser to Victoriabank's hosted payment page for this charge.
     *
     * The underlying library echoes a complete auto-submitting HTML page and
     * calls exit() itself — it is meant to be invoked directly from a
     * controller action with nothing expected to run afterwards.
     */
    public function authorize(string $orderCode, float $amount, string $backRefUrl, string $description): never
    {
        $this->gateway->requestAuthorization($orderCode, $amount, $backRefUrl, 'MDL', $description);

        exit; // unreachable: requestAuthorization() always exits, this satisfies the `never` return type
    }

    /**
     * Parses and cryptographically validates a POST payload received on the
     * BACKREF callback URL. Throws on malformed data; call isValid() on the
     * result to check whether the transaction itself was approved.
     */
    public function parseResponse(array $post): ResponseInterface
    {
        return $this->gateway->getResponseObject($post);
    }

    /**
     * Captures (settles) a previously authorized transaction. This is a
     * direct server-to-server call and returns instead of exiting.
     */
    public function requestCompletion(string $orderCode, float $amount, string $rrn, string $intRef): mixed
    {
        return $this->gateway->requestCompletion($orderCode, $amount, $rrn, $intRef, 'MDL');
    }
}
