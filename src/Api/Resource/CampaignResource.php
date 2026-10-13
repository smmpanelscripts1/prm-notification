<?php

namespace Prm\Notification\Api\Resource;

use Flarum\Api\Context as FlarumContext;
use Flarum\Api\Endpoint;
use Flarum\Api\Resource\AbstractDatabaseResource;
use Flarum\Api\Schema;
use Flarum\Api\Sort\SortColumn;
use Flarum\Foundation\ValidationException;
use Illuminate\Database\Eloquent\Builder;
use Prm\Notification\Campaign;
use Tobyz\JsonApiServer\Context;

/**
 * @extends AbstractDatabaseResource<Campaign>
 */
class CampaignResource extends AbstractDatabaseResource
{
    public function type(): string
    {
        return 'prm-notification-campaigns';
    }

    public function model(): string
    {
        return Campaign::class;
    }

    public function scope(Builder $query, Context $context): void
    {
        $actor = $context->getActor();

        if (! $actor->hasPermission('prm-notification.manage')) {
            $query->whereRaw('0 = 1');
        }
    }

    public function endpoints(): array
    {
        return [
            Endpoint\Index::make()
                ->authenticated()
                ->defaultInclude(['actor'])
                ->defaultSort('-createdAt')
                ->paginate(20, 50),
            Endpoint\Show::make()
                ->authenticated()
                ->defaultInclude(['actor']),
            Endpoint\Create::make()
                ->authenticated()
                ->visible(fn (FlarumContext $context) => $context->getActor()->can('create', Campaign::class))
                ->defaultInclude(['actor']),
            Endpoint\Update::make()
                ->authenticated()
                ->visible(fn (Campaign $campaign, FlarumContext $context) => $context->getActor()->can('update', $campaign))
                ->defaultInclude(['actor']),
            Endpoint\Delete::make()
                ->authenticated()
                ->visible(fn (Campaign $campaign, FlarumContext $context) => $context->getActor()->can('delete', $campaign)),
        ];
    }

    public function fields(): array
    {
        return [
            Schema\Str::make('title')
                ->requiredOnCreate()
                ->writable()
                ->minLength(1)
                ->maxLength(200),
            Schema\Str::make('body')
                ->requiredOnCreate()
                ->writable()
                ->minLength(1)
                ->maxLength(5000),
            Schema\Str::make('linkUrl')
                ->writable()
                ->nullable()
                ->maxLength(500)
                ->set(function (Campaign $campaign, ?string $value) {
                    $value = is_string($value) ? trim($value) : '';
                    $campaign->link_url = $value !== '' ? $value : null;
                }),
            Schema\Str::make('linkLabel')
                ->writable()
                ->nullable()
                ->maxLength(120)
                ->set(function (Campaign $campaign, ?string $value) {
                    $value = is_string($value) ? trim($value) : '';
                    $campaign->link_label = $value !== '' ? $value : null;
                }),
            Schema\Boolean::make('audienceNewUsers')
                ->writable()
                ->default(false),
            Schema\Boolean::make('audienceMembers')
                ->writable()
                ->default(false),
            Schema\Boolean::make('audienceGuests')
                ->writable()
                ->default(false),
            Schema\Boolean::make('guestsSearchOnly')
                ->writable()
                ->default(true),
            Schema\Boolean::make('isActive')
                ->writable()
                ->default(true),
            Schema\DateTime::make('startsAt')
                ->writable()
                ->nullable(),
            Schema\DateTime::make('endsAt')
                ->writable()
                ->nullable(),
            Schema\DateTime::make('createdAt'),
            Schema\DateTime::make('updatedAt'),

            Schema\Relationship\ToOne::make('actor')
                ->type('users')
                ->includable(),
        ];
    }

    public function sorts(): array
    {
        return [
            SortColumn::make('createdAt'),
        ];
    }

    public function creating(object $model, Context $context): ?object
    {
        /** @var Campaign $model */
        $actor = $context->getActor();
        $model->actor_id = $actor->id;

        $this->assertAudience($model);

        return $model;
    }

    public function updating(object $model, Context $context): ?object
    {
        /** @var Campaign $model */
        $this->assertAudience($model);

        return $model;
    }

    protected function assertAudience(Campaign $model): void
    {
        if (! $model->audience_new_users && ! $model->audience_members && ! $model->audience_guests) {
            throw new ValidationException([
                'audience' => 'Select at least one audience.',
            ]);
        }

        if ($model->starts_at && $model->ends_at && $model->ends_at->lt($model->starts_at)) {
            throw new ValidationException([
                'endsAt' => 'End date must be after start date.',
            ]);
        }
    }
}
