<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

return [
    'up' => function (Builder $schema) {
        if ($schema->hasTable('prm_notification_reads')) {
            return;
        }

        $schema->create('prm_notification_reads', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('campaign_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('guest_key', 64)->nullable();
            $table->dateTime('read_at');

            $table->foreign('campaign_id')->references('id')->on('prm_notification_campaigns')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['campaign_id', 'user_id']);
            $table->unique(['campaign_id', 'guest_key']);
            $table->index('guest_key');
        });
    },
    'down' => function (Builder $schema) {
        $schema->dropIfExists('prm_notification_reads');
    },
];
