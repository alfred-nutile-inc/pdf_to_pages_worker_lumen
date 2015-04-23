# PDF 2 Images

### Step 1)

Gets the PDF from S3 and breaks it into pages

### Step 2)

Turns the pages to images

### Step 3)

Uploads all images back to S3

### Step 4)

Posts to queue `diff_tool_file_uploads_ready_to_compare` that work is on this file. If error will post that back as well for the recipient to deal with.

Approve will listen for this Queue via push and deal with the status as needed

  1) Error
  2) Set A and B done
  3) Only one Set done



That is the workflow of the worker.

---


## Testing

phpunit

There is a test marked skipped that you can run to see the whole process from start to end un-mocked.



## Update worker (codeship will do this for you btw)

You need the iron cli client to do this see docs http://dev.iron.io/worker/beta/cli/

This is version 2.0

You need docker setup as well see docs https://docs.docker.com/installation/mac/

Very easy install make sure to remove any docker from brew install prior.

Now you are ready

When the worker is done being edited zip it up

~~~
zip -r PDF2PagesWorker.zip . -x *.git*
~~~

Then upload

first in your home directory make a .iron.json file

~~~
{
  "token": "bar"
}
~~~

Then

~~~
IRON_PROJECT_ID=foo iron worker upload --stack php-5.6 PDF2PagesWorker.zip php workers/PDF2PagesWorker.php
~~~

What is really cool is that all along you can be testing this

~~~
docker run --rm -v "$(pwd)":/worker -w /worker iron/images:php-5.6 sh -c "php /worker/workers/PDF2PagesWorker.php -payload tests/testspayload.json"
~~~

And as with docker you can get inside if needed

~~~
docker run -it -v "$(pwd)":/worker -w /worker iron/images:php-5.6 /bin/bash
~~~

And run the remote like this to test the queue or go to the web ui

~~~
iron worker queue --wait -payload-file testspayload.json PDF2PagesWorker
~~~

