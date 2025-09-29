<?php
use Illuminate\Support\Facades\Schedule;

Schedule::command('articles:aggregate business')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('articles:aggregate entertainment')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('articles:aggregate general')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('articles:aggregate health')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('articles:aggregate science')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('articles:aggregate sports')
    ->hourly()
    ->withoutOverlapping();

Schedule::command('articles:aggregate technology')
    ->hourly()
    ->withoutOverlapping();
