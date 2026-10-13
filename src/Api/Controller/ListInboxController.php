<?php

namespace Prm\Notification\Api\Controller;

use Flarum\Http\RequestUtil;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Prm\Notification\AudienceMatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ListInboxController implements RequestHandlerInterface
{
    public function __construct(
        protected AudienceMatcher $matcher
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $guestKey = $this->guestKey($request);
        $campaigns = $this->matcher->matchingFor($actor, $guestKey);
        $readIds = $this->matcher->readIds(array_map(fn ($c) => (int) $c->id, $campaigns), $actor, $guestKey);

        $data = array_map(function ($campaign) use ($readIds) {
            return [
                'type' => 'prm-notification-campaigns',
                'id' => (string) $campaign->id,
                'attributes' => [
                    'title' => $campaign->title,
                    'body' => $campaign->body,
                    'linkUrl' => $campaign->link_url,
                    'linkLabel' => $campaign->link_label,
                    'createdAt' => $campaign->created_at?->toAtomString(),
                    'isRead' => in_array((int) $campaign->id, $readIds, true),
                ],
            ];
        }, $campaigns);

        return new JsonResponse(['data' => array_values($data)]);
    }

    protected function guestKey(ServerRequestInterface $request): ?string
    {
        $key = $request->getHeaderLine('X-PRM-Guest-Key');
        if ($key === '') {
            $key = (string) Arr::get($request->getQueryParams(), 'guestKey', '');
        }

        $key = trim($key);

        return $key !== '' && preg_match('/^[a-zA-Z0-9_-]{8,64}$/', $key) ? $key : null;
    }
}
