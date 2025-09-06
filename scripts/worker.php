<?php
require __DIR__ . '/../app/Bootstrap.php';
require __DIR__ . '/../app/Worker.php';

$queue = $argv[1] ?? 'default';
$worker = new Worker($queue, $config);
$worker->run();
