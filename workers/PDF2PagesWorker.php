<?php


use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Queue;

require_once __DIR__ . '/libs/bootstrap.php';

$payload = getPayload(true);

fire($payload);

function fire($payload)
{
    try
    {
        $handler = new \App\PDF2FilesHandler();

        $dto = new \AlfredNutileInc\DiffTool\DiffToolDTO(
            $payload['project_id'],
            $payload['request_uuid'],
            $stage = 0,
            $driver = false,
            $payload['set'],
            $payload['user_id']
        );

        $handler->handle($dto);

        $ironmq = new \IronMQ(array(
            'token' => env('IRON_TOKEN'),
            'project_id' => env('IRON_PROJECT_ID')
        ));

        /**
         * Needed a bit more control over the queue
         */
        $ironmq->postMessage(
                    'diff_tool_file_uploads_ready_to_compare',
                    json_encode($dto),
                    $options = ['timeout' => 600, 'retries' => 1])->id;

        echo implode($handler->getResults());
    }

    catch(\Exception $e)
    {
        $message = sprintf("Error with the process %s project id %s request id %s set %s message: \n %s",
            $e->getMessage(),
            $payload['project_id'],
            $payload['request_uuid'],
            $payload['set'],
            $e->getMessage());

        $ironmq = new \IronMQ(array(
            'token' => env('IRON_TOKEN'),
            'project_id' => env('IRON_PROJECT_ID')
        ));

        /**
         * Needed a bit more control over the queue
         */
        $ironmq->postMessage(
            'diff_tool_file_uploads_ready_to_compare',
            json_encode(['error' => true, 'message' => $message]),
            $options = ['timeout' => 600, 'retries' => 1])->id;

        echo $message;
    }


}