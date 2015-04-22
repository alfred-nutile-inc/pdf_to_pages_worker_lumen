<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/22/15
 * Time: 12:54 PM
 */

namespace App\Providers;


use Illuminate\Support\ServiceProvider;

class PusherServiceProvider extends ServiceProvider {

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if(getenv('APP_ENV') == 'testing' || getenv('MOCK_PUSHER') == 'true' )
        {
            $this->app->singleton('App\Interfaces\WebsocketInterface', function($app){
                $public = getenv('PUSHER_PUBLIC');
                $secret = getenv('PUSHER_SECRET');
                $app_id = getenv('PUSHER_APP_ID');
                $debug_setting = ($app->environment() == 'local') ? true : false;
                $timeout = ($app->environment() == 'local') ? 900 : 30;
                return new PusherServiceMock($public, $secret, $app_id, $debug = $debug_setting, $host = 'https://api.pusherapp.com', $port = '443', $timeout);
            });
        }
        else
        {
            $this->app->singleton('App\Interfaces\WebsocketInterface', function($app){
                $public = getenv('PUSHER_PUBLIC');
                $secret = getenv('PUSHER_SECRET');
                $app_id = getenv('PUSHER_APP_ID');
                $debug_setting = ($app->environment() == 'local') ? true : false;
                $timeout = ($app->environment() == 'local') ? 900 : 30;
                return new PusherService($public, $secret, $app_id, $debug = $debug_setting, $host = 'https://api.pusherapp.com', $port = '443', $timeout);
            });
        }
    }
}