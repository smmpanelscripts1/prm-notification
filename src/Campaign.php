<?php

namespace Prm\Notification;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\User\User;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property string|null $link_url
 * @property string|null $link_label
 * @property bool $audience_new_users
 * @property bool $audience_members
 * @property bool $audience_guests
 * @property bool $guests_search_only
 * @property bool $is_active
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property int|null $actor_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read User|null $actor
 */
class Campaign extends AbstractModel
{
    protected $table = 'prm_notification_campaigns';

    public $timestamps = true;

    protected $casts = [
        'audience_new_users' => 'bool',
        'audience_members' => 'bool',
        'audience_guests' => 'bool',
        'guests_search_only' => 'bool',
        'is_active' => 'bool',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function dismissals()
    {
        return $this->hasMany(Dismissal::class, 'campaign_id');
    }

    public function isCurrentlyScheduled(?Carbon $now = null): bool
    {
        $now = $now ?: Carbon::now();

        if ($this->starts_at && $now->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && $now->gt($this->ends_at)) {
            return false;
        }

        return true;
    }
}
