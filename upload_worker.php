<?php

require_once "phar://../iron_worker.phar";


$worker = new IronWorker();

$worker->upload("workers/", 'PDF2PagesWorker.php', "PDF2PagesWorker");
