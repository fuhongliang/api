<?php

$cmd = "cd /data/wwwroot/default/hook/api/master &&sudo git pull";
$res = shell_exec($cmd);
var_dump($res);
exit;