<?php

use Flarum\Database\Migration;
use Flarum\Group\Group;

return Migration::addPermissions([
    'prm-notification.manage' => Group::ADMINISTRATOR_ID,
]);
