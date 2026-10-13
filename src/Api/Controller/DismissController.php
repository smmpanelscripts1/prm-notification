<?php

namespace Prm\Notification\Api\Controller;

use Carbon\Carbon;
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Prm\Notification\AudienceMatcher;
use Prm\Notification\Campaign;
use Prm\Notification\Dismissal;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DismissController implements RequestHandlerInterface
{
    public function __construct(
        protected AudienceMatcher $matcher
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $id = (int) Arr::get($request->getQueryParams(), 'id');
        $campaign = Campaign::query()->findOrFail($id);

        $guestKey = trim((string) ($request->getHeaderLine('X-PRM-Guest-Key')
            ?: Arr::get($request->getParsedBody(), 'guestKey', '')));

        if ($actor->isGuest()) {
            if ($guestKey === '' || ! preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $guestKey)) {
                throw new ValidationException(['guestKey' => 'Guest key required.']);
            }
        }

        if (! $this->matcher->actorMatches($campaign, $actor)) {
            throw new ValidationException(['campaign' => 'Not available.']);
        }

        $attrs = [
            'campaign_id' => $campaign->id,
            'dismissed_at' => Carbon::now(),
        ];

        if ($actor->isGuest()) {
            $attrs['guest_key'] = $guestKey;
            $attrs['user_id'] = null;
            Dismissal::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'guest_key' => $guestKey],
                $attrs
            );
        } else {
            $attrs['user_id'] = $actor->id;
            $attrs['guest_key'] = null;
            Dismissal::query()->updateOrCreate(
                ['campaign_id' => $campaign->id, 'user_id' => $actor->id],
                $attrs
            );
        }

        return new EmptyResponse();
    }
}
