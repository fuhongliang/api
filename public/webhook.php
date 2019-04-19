<?php

$cmd = "cd /data/wwwroot/api &&sudo git pull origin v2";
$res = shell_exec($cmd);
var_dump($res);
exit;