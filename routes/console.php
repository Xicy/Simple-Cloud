<?php

use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('queue:clear', function () {
    \Illuminate\Support\Facades\DB::table("jobs")->delete();
})->describe('Queue clear');


Artisan::command('sync:all', function () {
    dispatch(new \App\Jobs\SyncWithdraws());
    dispatch(new \App\Jobs\SyncBlocks());
    dispatch(new \App\Jobs\WalletAddressCreater());
})->describe('Sync all');

Artisan::command('sync:blocks', function () {
    dispatch(new \App\Jobs\SyncBlocks());
})->describe('Sync coin blocks');

Artisan::command('sync:addressCreater', function () {
    dispatch(new \App\Jobs\WalletAddressCreater());
})->describe('Sync address');

Artisan::command('sync:withdraws', function () {
    dispatch(new \App\Jobs\SyncWithdraws());
})->describe('Sync withdraws');
