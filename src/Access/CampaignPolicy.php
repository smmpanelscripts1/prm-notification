<?php

namespace Prm\Notification\Access;

use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;
use Prm\Notification\Campaign;

class CampaignPolicy extends AbstractPolicy
{
    public function create(User $actor): bool
    {
        return $actor->hasPermission('prm-notification.manage');
    }

    public function update(User $actor, Campaign $campaign): bool
    {
        return $actor->hasPermission('prm-notification.manage');
    }

    public function delete(User $actor, Campaign $campaign): bool
    {
        return $actor->hasPermission('prm-notification.manage');
    }
}
