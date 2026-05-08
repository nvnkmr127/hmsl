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
        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) !== false;
    }
}
