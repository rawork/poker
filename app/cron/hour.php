<?php

use Fuga\Component\Scheduler\Scheduler;

require_once('../init.php');

$scheduler = new Scheduler();
$scheduler->processTasks('hour');