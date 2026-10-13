<?php

namespace Prm\Notification;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;

/**
 * @property int $id
 * @property int $campaign_id
 * @property int|null $user_id
 * @property string|null $guest_key
 * @property Carbon $read_at
 */
class Read extends AbstractModel
{
    protected $table = 'prm_notification_reads';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
