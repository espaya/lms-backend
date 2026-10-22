<?php

use Illuminate\Support\Facades\Schedule;


Schedule::command('quizzes:activate-scheduled')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->onOneServer();
