touch PDF2PagesWorker.zip
rm PDF2PagesWorker.zip
zip -r PDF2PagesWorker.zip . -x *.git*
iron worker upload --stack php-5.6 PDF2PagesWorker.zip php workers/PDF2PagesWorker.php
