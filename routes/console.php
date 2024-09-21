<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:reply-messages')
    ->name('ReplyMessagesCommand')
    ->withoutOverlapping(1)
    ->everyMinute();

Schedule::command('app:sync-messages')
    ->name('SyncMessagesCommand')
    ->withoutOverlapping(1)
    ->everySecond();
