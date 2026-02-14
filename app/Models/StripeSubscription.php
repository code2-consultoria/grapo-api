<?php

namespace App\Models;

use Laravel\Cashier\Subscription as CashierSubscription;

class StripeSubscription extends CashierSubscription
{
    protected $table = 'stripe_subscriptions';

    // Usa pessoa_id ao invés de user_id
    protected $foreignKey = 'pessoa_id';

    public function owner(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Pessoa::class, 'pessoa_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StripeSubscriptionItem::class, 'subscription_id');
    }
}
