<?php

$cmd = "cd /data/wwwroot/default/hook/api &&sudo git pull";
$res = shell_exec($cmd);
var_dump($res);
exit;