<?php

namespace App\Traits;

/**
 * Simple HMAC signing/verification with freshness and replay protection hook.
 */
trait SecureSignature
{
    /**
     * Secret key from env (never hardcode).
     */
    private function getSecret(): string
    {
        return env('MQTT_SECRET_KEY', 'default_secret_hay_doi_ngay');
    }

    /**
     * Build a signed wrapper for outbound payloads.
     */
    public function signPayload(array $data): array
    {
        $data['ts'] = time(); // timestamp to mitigate replay
        ksort($data);

        $signature = hash_hmac('sha256', json_encode($data), $this->getSecret());

        return [
            'payload' => $data,
            'signature' => $signature,
        ];
    }

    /**
     * Verify and return the inner payload, or null if invalid/expired.
     */
    public function verifyPayload(string $payloadJson): ?array
    {
        $wrapper = json_decode($payloadJson, true);
        if (!is_array($wrapper) || !isset($wrapper['signature'], $wrapper['payload'])) {
            return null;
        }

        $data = $wrapper['payload'];
        if (!is_array($data) || !isset($data['ts'])) {
            return null;
        }

        if (time() - (int) $data['ts'] > 60) {
            return null; // expired
        }

        ksort($data);
        $expectedSig = hash_hmac('sha256', json_encode($data), $this->getSecret());
        $receivedSig = $wrapper['signature'];

        if (!hash_equals($expectedSig, $receivedSig)) {
            return null;
        }

        return $data;
    }
}
