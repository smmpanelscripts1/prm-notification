<?php

namespace Prm\Notification;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\User\User;

/**
 * @property int $id
 * @property int $campaign_id
 * @property int|null $user_id
 * @property string|null $guest_key
 * @property Carbon $dismissed_at
 */
class Dismissal extends AbstractModel
{
    protected $table = 'prm_notification_dismissals';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'dismissed_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
