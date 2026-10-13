<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if (! $schema->hasTable('prm_notification_campaigns')) {
            $schema->create('prm_notification_campaigns', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 200);
                $table->text('body');
                $table->string('link_url', 500)->nullable();
                $table->string('link_label', 120)->nullable();
                $table->boolean('audience_new_users')->default(false);
                $table->boolean('audience_members')->default(false);
                $table->boolean('audience_guests')->default(false);
                $table->boolean('guests_search_only')->default(true);
                $table->boolean('is_active')->default(true);
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('ends_at')->nullable();
                $table->unsignedInteger('actor_id')->nullable();
                $table->timestamps();

                $table->foreign('actor_id')->references('id')->on('users')->onDelete('set null');
                $table->index(['is_active', 'starts_at', 'ends_at']);
            });
        }

        if (! $schema->hasTable('prm_notification_dismissals')) {
            $schema->create('prm_notification_dismissals', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('campaign_id');
                $table->unsignedInteger('user_id')->nullable();
                $table->string('guest_key', 64)->nullable();
                $table->dateTime('dismissed_at');

                $table->foreign('campaign_id')->references('id')->on('prm_notification_campaigns')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['campaign_id', 'user_id']);
                $table->unique(['campaign_id', 'guest_key']);
                $table->index('guest_key');
            });
        }
    },
    'down' => function (Builder $schema) {
        $schema->dropIfExists('prm_notification_dismissals');
        $schema->dropIfExists('prm_notification_campaigns');
    },
];
