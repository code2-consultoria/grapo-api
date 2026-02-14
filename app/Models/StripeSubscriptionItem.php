<?php

namespace App\Models;

use Laravel\Cashier\SubscriptionItem as CashierSubscriptionItem;

class StripeSubscriptionItem extends CashierSubscriptionItem
{
    protected $table = 'stripe_subscription_items';

    public function subscription(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StripeSubscription::class);
    }
}
