<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Decides which X-Forwarded-* headers to believe (ADR-025).
 *
 * Browsers reach the API through the web app (ADR-004). The web app proves it is the web app
 * with a shared secret (WEB_PROXY_SECRET) and tells us the visitor's IP in its own header.
 * Anyone else is a "direct" caller (Stripe, the cron service, or someone poking at the API):
 * for them we only believe what Render's own proxy adds, so nobody can fake their IP to
 * get around rate limits, or fake the host that links in emails are built from.
 *
 * Replaces Laravel's TrustProxies in bootstrap/app.php.
 */
class TrustWebProxy extends TrustProxies
{
    /** The web app sends this on every request; the value is WEB_PROXY_SECRET. */
    public const SECRET_HEADER = 'X-Web-Proxy-Secret';

    /** The visitor's IP, as the web app's host (Vercel) saw it. Missing when the web app's server asks for itself. */
    public const CLIENT_IP_HEADER = 'X-Web-Client-Ip';

    /** Who sent the request, kept on the request as the "via" attribute. */
    public const VIA_BROWSER = 'browser';

    public const VIA_WEB_SERVER = 'web-server';

    public const VIA_DIRECT = 'direct';

    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.web_proxy.secret');
        $sentSecret = (string) $request->headers->get(self::SECRET_HEADER);
        $clientIp = filter_var($request->headers->get(self::CLIENT_IP_HEADER), FILTER_VALIDATE_IP) ?: null;

        // Never pass the secret on: not to logs, error reports or controllers.
        $request->headers->remove(self::SECRET_HEADER);
        $request->headers->remove(self::CLIENT_IP_HEADER);

        // No secret set yet (local set-up, or before it was added in Render): trust the forwarded
        // headers like before, so nothing breaks while the secret is being set up.
        if ($secret === '') {
            $request->attributes->set('via', self::VIA_DIRECT);
            $this->proxies = '*';

            return parent::handle($request, $next);
        }

        if ($sentSecret !== '' && hash_equals($secret, $sentSecret)) {
            // The web app. Its own header says who the visitor is; whatever else is in
            // X-Forwarded-For (Vercel's and Render's addresses) is thrown away.
            if ($clientIp !== null) {
                $request->headers->set('X-Forwarded-For', $clientIp);
            } else {
                $request->headers->remove('X-Forwarded-For');
            }

            $request->attributes->set('via', $clientIp !== null ? self::VIA_BROWSER : self::VIA_WEB_SERVER);
            $request::setTrustedProxies(['0.0.0.0/0', '::/0'], $this->getTrustedHeaderNames());

            return $next($request);
        }

        // Anyone else. Believe only Render's own proxy (a private address) and only for the
        // caller's IP and http/https. The rightmost public address in X-Forwarded-For is
        // the one Render saw, so addresses the caller wrote in themselves are ignored.
        $request->headers->remove('X-Forwarded-Host');
        $request->headers->remove('X-Forwarded-Port');
        $request->headers->remove('X-Forwarded-Prefix');

        $request->attributes->set('via', self::VIA_DIRECT);
        $request::setTrustedProxies(['PRIVATE_SUBNETS'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO);

        return $next($request);
    }
}
