<?php

namespace Illuminate\Foundation\Http;

use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Cookie;

class MaintenanceModeBypassCookie
{
    /**
     * Create a new maintenance mode bypass cookie.
     *
     * @param  string  $key
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    public static function create($key)
    {
        $key = cast_to_string($key);

        $expiresAt = Carbon::now()->addHours(12);

        return new Cookie('laravel_maintenance', base64_encode(json_encode([
            'expires_at' => $expiresAt->getTimestamp(),
            'mac' => hash_hmac('SHA256', $expiresAt->getTimestamp(), $key),
        ])), $expiresAt);
    }

    /**
     * Determine if the given maintenance mode bypass cookie is valid.
     *
     * @param  string  $cookie
     * @param  string  $key
     * @return bool
     */
    public static function isValid($cookie, $key)
    {
        $cookie = cast_to_string($cookie);

        $key = cast_to_string($key);

        $payload = backport_json_decode(base64_decode($cookie), true);

        return is_array($payload) &&
            is_numeric(isset($payload['expires_at']) ? $payload['expires_at'] : null) &&
            isset($payload['mac']) &&
            hash_equals(hash_hmac('SHA256', $payload['expires_at'], $key), $payload['mac']) &&
            (int) $payload['expires_at'] >= Carbon::now()->getTimestamp();
    }
}
