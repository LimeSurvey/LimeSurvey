<?php

namespace LimeSurvey\Models\Services;

use Yii;

/**
 * Reads the active subscription tier and exposes feature-gating helpers.
 */
class SubscriptionService
{
    /**
     * Whether the current installation is on a free-tier subscription.
     *
     * @return bool
     */
    public function isFreeUser(): bool
    {
        $subscriptionAlias = strtolower((string) Yii::app()->getConfig('subscription_alias', ''));
        return strpos($subscriptionAlias, 'free') === 0;
    }
}
