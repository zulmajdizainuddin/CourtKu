<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A signed webhook from the gateway, saved before it is acted on (plan section 9). The unique event_key
 * means the same webhook sent twice is acted on once; processed_at null means it still has to be.
 *
 * @property string $gateway
 * @property string $event_key the gateway's event id, e.g. "evt_..."
 * @property string $type paid, failed or expired
 * @property string|null $account_id
 * @property array<string, mixed> $payload
 * @property Carbon|null $processed_at
 */
class WebhookEvent extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'processed_at' => 'datetime',
        ];
    }
}
