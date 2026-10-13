<?php

namespace Prm\Notification\Api;

use Flarum\Api\Context;
use Flarum\Api\Schema;
use Prm\Notification\AudienceMatcher;

class ForumResourceFields
{
    public function __construct(
        protected AudienceMatcher $matcher
    ) {
    }

    public function __invoke(): array
    {
        return [
            Schema\Boolean::make('prmNotificationShowForGuests')
                ->get(fn () => $this->matcher->hasAnyActiveGuestCampaign()),
            Schema\Integer::make('prmNotificationNewUserHours')
                ->get(fn () => $this->matcher->newUserHours()),
            Schema\Boolean::make('canManagePrmNotifications')
                ->get(fn ($model, Context $context) => $context->getActor()->hasPermission('prm-notification.manage')),
        ];
    }
}
