<?php

namespace Prm\Notification;

use Carbon\Carbon;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Builder;

class AudienceMatcher
{
    public function __construct(
        protected SettingsRepositoryInterface $settings
    ) {
    }

    public function newUserHours(): int
    {
        return max(1, (int) $this->settings->get('prm-notification.new_user_hours', 72));
    }

    public function hasAnyActiveGuestCampaign(): bool
    {
        return $this->activeCampaignQuery()->where('audience_guests', true)->exists();
    }

    /**
     * @return list<Campaign>
     */
    public function matchingFor(User $actor, ?string $guestKey = null): array
    {
        $campaigns = $this->activeCampaignQuery()->orderByDesc('created_at')->get();
        $matched = [];

        foreach ($campaigns as $campaign) {
            if (! $this->actorMatches($campaign, $actor)) {
                continue;
            }

            if ($this->isDismissed($campaign, $actor, $guestKey)) {
                continue;
            }

            $matched[] = $campaign;
        }

        return $matched;
    }

    public function actorMatches(Campaign $campaign, User $actor): bool
    {
        // Guests: any visitor who is not logged in, however they reached the site.
        if ($actor->isGuest()) {
            return (bool) $campaign->audience_guests;
        }

        $isNew = $this->isNewUser($actor);
        $matched = false;

        if ($campaign->audience_new_users && $isNew) {
            $matched = true;
        }

        // "Ad package": all logged-in members (including returning visitors).
        if ($campaign->audience_members) {
            $matched = true;
        }

        return $matched;
    }

    public function isNewUser(User $user): bool
    {
        if ($user->isGuest() || ! $user->joined_at) {
            return false;
        }

        return $user->joined_at->gt(Carbon::now()->subHours($this->newUserHours()));
    }

    /**
     * @param list<int> $campaignIds
     * @return list<int>
     */
    public function readIds(array $campaignIds, User $actor, ?string $guestKey): array
    {
        if (! $campaignIds || ($actor->isGuest() && ! $guestKey)) {
            return [];
        }

        $q = Read::query()->whereIn('campaign_id', $campaignIds);
        $q = $actor->isGuest() ? $q->where('guest_key', $guestKey) : $q->where('user_id', $actor->id);

        return $q->pluck('campaign_id')->map(fn ($id) => (int) $id)->all();
    }

    protected function isDismissed(Campaign $campaign, User $actor, ?string $guestKey): bool
    {
        $q = Dismissal::query()->where('campaign_id', $campaign->id);

        if (! $actor->isGuest()) {
            return $q->where('user_id', $actor->id)->exists();
        }

        if (! $guestKey) {
            return false;
        }

        return $q->where('guest_key', $guestKey)->exists();
    }

    protected function activeCampaignQuery(): Builder
    {
        $now = Carbon::now();

        return Campaign::query()
            ->where('is_active', true)
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            });
    }
}
