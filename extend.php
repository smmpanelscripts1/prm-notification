<?php

namespace Prm\Notification;

use Flarum\Api\Resource as CoreResource;
use Flarum\Extend;
use Prm\Notification\Access\CampaignPolicy;
use Prm\Notification\Api\Controller\DismissController;
use Prm\Notification\Api\Controller\ListInboxController;
use Prm\Notification\Api\Controller\MarkReadController;
use Prm\Notification\Api\ForumResourceFields;
use Prm\Notification\Api\Resource\CampaignResource;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js')
        ->css(__DIR__.'/less/admin.less'),

    new Extend\Locales(__DIR__.'/locale'),

    (new Extend\Settings())
        ->default('prm-notification.new_user_hours', 72),

    (new Extend\Routes('api'))
        ->get('/prm-notifications', 'prm-notifications.index', ListInboxController::class)
        ->post('/prm-notifications/read', 'prm-notifications.read', MarkReadController::class)
        ->post('/prm-notifications/{id}/dismiss', 'prm-notifications.dismiss', DismissController::class),

    // Guests dismiss / mark read without a session CSRF token (identified by X-PRM-Guest-Key).
    (new Extend\Csrf())
        ->exemptRoute('prm-notifications.dismiss')
        ->exemptRoute('prm-notifications.read'),

    new Extend\ApiResource(CampaignResource::class),

    (new Extend\ApiResource(CoreResource\ForumResource::class))
        ->fields(ForumResourceFields::class),

    (new Extend\Policy())
        ->modelPolicy(Campaign::class, CampaignPolicy::class),
];
