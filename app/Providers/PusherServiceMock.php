<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 11/30/14
 * Time: 7:31 AM
 */

namespace App\Providers;

use Illuminate\Support\Facades\Log;

class PusherServiceMock extends PusherService {

    public function notifyChannelOfJob($channel, $event_name, $message)
    {
        Log::info("Pusher Mocker Service in use");
        return true;
    }


} 