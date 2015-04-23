<?php


use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;

require_once __DIR__ . '/libs/bootstrap.php';

$payload = getPayload(true);

fire($payload);

function fire($payload)
{
    try
    {

        file_put_contents(storage_path('logs/lumen.log'), "");
        if(!File::exists(storage_path('bundle/mock-project-1')))
            File::deleteDirectory(storage_path('bundle/mock-project-1'));

        $dto = new \AlfredNutileInc\DiffTool\DiffToolDTO(
            $payload['project_id'],
            $payload['request_uuid'],
            $stage = (isset($payload['stage'])) ? $payload['stage'] : 0,
            $driver = false,
            $payload['set'],
            $payload['user_id']
        );


        if($dto->stage === 'ready_to_diff')
        {
            $handler = new \App\DiffImagesHandler();
            $handler->handle($dto);
        } else {
            $handler = new \App\PDF2FilesHandler();
            $handler->handle($dto);
        }

        $ironmq = new \IronMQ(array(
            'token' => env('IRON_TOKEN'),
            'project_id' => env('IRON_PROJECT_ID')
        ));

        if($dto->stage == 'ready_to_diff')
        {
            //NOT SURE WHAT QUEUE
            $queue_name = 'diff_tool_file_uploads_ready_to_compareFOO';
        }
        else
        {
            $queue_name = 'diff_tool_file_uploads_ready_to_compare';
        }

        $ironmq->postMessage(
                    $queue_name,
                    json_encode($dto),
                    $options = ['timeout' => 600, 'retries' => 1])->id;

        $content = file_get_contents(storage_path('logs/lumen.log'));
        $handler->setResults("Now for the log");
        $handler->setResults($content);


        exec("which gs", $out);
        $handler->setResults($out);

        exec("gs", $out);
        $handler->setResults($out);

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