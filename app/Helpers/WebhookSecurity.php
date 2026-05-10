<?php

namespace App\Helpers;

class WebhookSecurity
{
    /**
     * Prevent SSRF by checking if a URL resolves to a private IP.
     */
    public static function isSafeUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return false;

        // 1. Block literal IP addresses (IPv4 and IPv6)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return self::isPublicIp($host);
        }

        // 2. Resolve DNS and check all IPs
        $ips = gethostbynamel($host);
        if (!$ips) return false;

        foreach ($ips as $ip) {
            if (!self::isPublicIp($ip)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if an IP is a public, non-reserved address.
     */
    public static function isPublicIp(string $ip): bool
    {
        // Allow private IPs in local environment for testing
        if (config('app.env') === 'local') {
            return true;
        }

        // Specifically block localhost, link-local, and AWS/GCP metadata IPs
        $blocked = [
            '127.0.0.1', '::1',
            '169.254.169.254', // AWS/GCP Metadata
            'metadata.google.internal',
        ];

        if (in_array($ip, $blocked)) return false;

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }

    /**
     * Verify the HMAC signature of an incoming request.
     */
    public static function verifySignature(string $payload, string $signature, string $secret, ?string $timestamp = null): bool
    {
        if ($timestamp) {
            // Check for replay attacks using timestamp tolerance (default 5 minutes)
            $tolerance = 300; 
            if (abs(time() - (int)$timestamp) > $tolerance) {
                return false;
            }
            $dataToSign = $timestamp . '.' . $payload;
        } else {
            $dataToSign = $payload;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $dataToSign, $secret);

        return hash_equals($expected, $signature);
    }
}
