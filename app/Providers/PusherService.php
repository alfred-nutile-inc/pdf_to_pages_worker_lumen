<?php

namespace App\Providers;

use App\Interfaces\WebsocketInterface;
use Illuminate\Support\Facades\Log;
use Pusher;

class PusherService extends Pusher implements WebsocketInterface
{

    protected $message;
    protected $channel;
    protected $eventNames = [];
    protected $dto;
    protected $event_name;

    public function __construct($public, $secret, $app_id, $debug = false, $host = 'http://api.pusherapp.com', $port = '80', $timeout = 30)
    {
        parent::__construct($public, $secret, $app_id, $debug, $host, $port, $timeout);
    }

    public function notifyChannelOfJob($channel, $event_name, $message)
    {
        try
        {
            $results = $this->trigger($channel, $event_name, $message);
            if($results !== true)
            {
                //@TODO log this
            }
        }
        catch(\Exception $e)
        {
            $message = "Error pushing " . $e->getMessage();
            Log::info($message);
            throw new \Exception($message);
        }
    }

    public function setMessage($message, $type = 'text', $status = true)
    {
        $this->message = ['message' => $message, 'messageType' => $type, 'status' => $status];
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }


    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setAnEventName($event_name)
    {
        $this->event_name = $event_name;
        return $this;
    }

    public function getAnEventName()
    {
        return $this->event_name;
    }

    public function setEventName($key,  $event)
    {
        $this->eventNames[$key] = $event;
        return $this;
    }

    public function getEventName($key)
    {
        return $this->eventNames[$key];
    }


}
