<?php

require_once 'server/api/_config.php';

$user = $db_params['root'];
$database = $db_params['calenderApp'];

shell_exec("mysqldump -u $user -p $database > database/dump.sql");
