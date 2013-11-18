<?php

// */1 * * * * /usr/local/bin/php /home/www/htdocs/app/cron/min.php > /dev/null
// 0 */1 * * * /usr/local/bin/php /home/www/htdocs/app/cron/hour.php > /dev/null
// 0 0 */1 * * /usr/local/bin/php /home/www/htdocs/app/cron/day.php > /dev/null

use Fuga\Component\Scheduler\Scheduler;

require_once('../init.php');

$scheduler = new Scheduler();
$scheduler->processTasks('minute');
