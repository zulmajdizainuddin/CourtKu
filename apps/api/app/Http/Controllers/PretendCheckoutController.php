<?php

namespace App\Http\Controllers;

use App\Payments\CheckoutStatus;
use App\Payments\PretendGateway;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/**
 * The "pretend Stripe" page for the browser tests (ADR-034), at /api/pretend-checkout/{id}.
 * It answers 404 unless PretendGateway::enabled(), so never works on the live site.
 * Plain links instead of forms: the API's security headers allow no styles or scripts, and
 * a link needs no CSRF token.
 */
class PretendCheckoutController extends Controller
{
    public function __construct(private readonly PretendGateway $gateway) {}

    public function show(string $id): Response
    {
        abort_unless(PretendGateway::enabled(), 404);
        $page = $this->gateway->page($id) ?? abort(404);
        $open = $page['status'] === CheckoutStatus::Open->value;
        $amount = e(Money::format($page['amount_sen']));
        $body = '<h1>Pretend Stripe</h1>'
            .'<p>Test payments only. No real money, no card details.</p>'
            .'<p>'.e($page['description']).'</p>'
            .'<p>Amount: <strong>'.$amount.'</strong></p>'
            .($open
                ? '<p><a href="'.e(url("/api/pretend-checkout/{$id}/pay")).'">Pay '.$amount.'</a></p>'
                    .'<p><a href="'.e($page['return_url']).'">Back without paying</a></p>'
                : '<p>This payment page is '.e($page['status']).'.</p><p><a href="'.e($page['return_url']).'">Back to CourtKu</a></p>');

        return response('<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Pretend Stripe</title></head><body>'.$body.'</body></html>')
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    public function pay(string $id): RedirectResponse
    {
        abort_unless(PretendGateway::enabled(), 404);
        $page = $this->gateway->page($id) ?? abort(404);
        $this->gateway->pay($id);

        // Back to the web app's status page, like Stripe's success_url.
        return redirect()->away($page['return_url']);
    }
}
