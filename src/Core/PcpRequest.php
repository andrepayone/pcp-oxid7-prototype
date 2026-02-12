<?php

namespace Payone\PcpPrototype\Core;

class PcpRequest
{
    /**
     * Creates a checkout on the PAYONE Commerce Platform.
     *
     * @param array $requestData
     * @param array $config
     * @return string
     */
    public function createCheckout(array $requestData, array $config): string
    {
        $payload = json_encode($requestData);
        $apiKey = $config['pcpApiKey'];
        $apiSecret = $config['pcpApiSecret'];

        $hmac = hash_hmac('sha256', $payload, $apiSecret);

        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $apiKey,
            'X-Api-Auth-Hmac: ' . $hmac,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $config['pcpApiEndpoint'] . '/v1/checkouts');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}