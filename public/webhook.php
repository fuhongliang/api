<?php

$cmd = "cd /data/wwwroot/api &&sudo git pull";
$res = shell_exec($cmd);
var_dump($res);
exit;