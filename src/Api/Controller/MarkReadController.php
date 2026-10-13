<?php

namespace Prm\Notification\Api\Controller;

use Carbon\Carbon;
use Flarum\Foundation\ValidationException;
use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\EmptyResponse;
use Prm\Notification\AudienceMatcher;
use Prm\Notification\Read;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MarkReadController implements RequestHandlerInterface
{
    public function __construct(
        protected AudienceMatcher $matcher
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $body = (array) $request->getParsedBody();

        $guestKey = trim((string) ($request->getHeaderLine('X-PRM-Guest-Key') ?: Arr::get($body, 'guestKey', '')));

        if ($actor->isGuest() && ($guestKey === '' || ! preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $guestKey))) {
            throw new ValidationException(['guestKey' => 'Guest key required.']);
        }

        $ids = array_map('intval', (array) Arr::get($body, 'ids', []));
        $now = Carbon::now();

        foreach ($this->matcher->matchingFor($actor, $guestKey ?: null) as $campaign) {
            if ($ids && ! in_array((int) $campaign->id, $ids, true)) {
                continue;
            }

            $key = $actor->isGuest()
                ? ['campaign_id' => $campaign->id, 'guest_key' => $guestKey]
                : ['campaign_id' => $campaign->id, 'user_id' => $actor->id];

            Read::query()->firstOrCreate($key, ['read_at' => $now]);
        }

        return new EmptyResponse();
    }
}
