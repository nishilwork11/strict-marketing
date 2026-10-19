<?php

namespace App\Support;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

// The admin panel has one shared password, ADMIN_PASSWORD. Signing in sets a cookie
// holding an expiry time and a signature over it. The signing key comes from the
// password, so changing the password signs everyone out.
class AdminSession
{
    public static function passwordIsSet(): bool
    {
        return static::password() !== '';
    }

    // Hashing both sides gives equal lengths, so the comparison takes the same time whatever was typed
    public static function isPassword(string $input): bool
    {
        return static::passwordIsSet()
            && hash_equals(hash('sha256', static::password(), true), hash('sha256', $input, true));
    }

    public static function check(Request $request): bool
    {
        return static::isValidToken($request->cookie(config('admin.cookie')));
    }

    // Scoped to /admin, so the rest of the site never sends it
    public static function cookie(): Cookie
    {
        return cookie(
            config('admin.cookie'),
            static::createToken(),
            intdiv(config('admin.max_age'), 60),
            '/admin',
            null,
            app()->isProduction(),
            true,
            false,
            'lax',
        );
    }

    public static function forget(): Cookie
    {
        return cookie()->forget(config('admin.cookie'), '/admin');
    }

    public static function createToken(): string
    {
        $expires = (string) (now()->getTimestamp() + config('admin.max_age'));

        return $expires.'.'.static::sign($expires);
    }

    public static function isValidToken(mixed $token): bool
    {
        if (! is_string($token) || ! static::passwordIsSet()) {
            return false;
        }

        $parts = explode('.', $token);

        if (count($parts) !== 2 || ! ctype_digit($parts[0]) || (int) $parts[0] <= now()->getTimestamp()) {
            return false;
        }

        return hash_equals(static::sign($parts[0]), $parts[1]);
    }

    // Keyed by the password and the app key, so a copy of the cookie is no help in guessing either
    protected static function sign(string $expires): string
    {
        $key = hash_hmac('sha256', static::password(), config('app.key'), true);

        return rtrim(strtr(base64_encode(hash_hmac('sha256', $expires, $key, true)), '+/', '-_'), '=');
    }

    protected static function password(): string
    {
        return (string) config('admin.password');
    }
}
