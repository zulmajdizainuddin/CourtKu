<?php

namespace App\Payments;

use App\Exceptions\ApiException;

/**
 * The gateway couldn't be reached or refused the request. Answers 502 PAYMENT_GATEWAY_ERROR in
 * the standard error shape; the booking itself is unaffected.
 */
class PaymentGatewayException extends ApiException
{
    public function __construct(string $message = 'The payment service didn\'t answer. Please try again in a minute.')
    {
        parent::__construct('PAYMENT_GATEWAY_ERROR', $message, 502);
    }
}
