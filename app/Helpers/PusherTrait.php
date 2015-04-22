<?php
/**
 * Created by PhpStorm.
 * User: alfrednutile
 * Date: 4/9/15
 * Time: 8:40 PM
 */

namespace App\Helpers;


use App\Providers\PusherService;
use Illuminate\Support\Facades\App;

trait PusherTrait {
    protected $message;
    protected $channel;
    protected $websockets;
    protected $event_name;

    /**
     * @return mixed
     */
    public function getEventName()
    {
        return $this->event_name;
    }

    /**
     * @param mixed $event_name
     */
    public function setEventName($event_name)
    {
        $this->event_name = $event_name;
    }

    /**
     * @return mixed
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @param mixed $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    protected function pushNotice()
    {
        $this->getWebsockets()
            ->notifyChannelOfJob($this->getChannel(), $this->getEventName(), $this->getMessage());
    }

    /**
     * @return PusherService
     */
    public function getWebsockets()
    {
        if($this->websockets == null)
            $this->setWebsockets();
        return $this->websockets;
    }

    /**
     * @param mixed $websockets
     */
    public function setWebsockets($websockets = null)
    {
        if($websockets == null)
        {
            $websockets = App::make('App\Interfaces\WebsocketInterface');
        }
        $this->websockets = $websockets;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
