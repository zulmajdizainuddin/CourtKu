<?php

namespace App\Payments;

use RuntimeException;

/** A webhook whose signature is missing, wrong or too old: not from the gateway, so it changes nothing. */
class InvalidWebhookSignature extends RuntimeException {}
