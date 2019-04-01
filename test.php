<?php

$cmd = "cd /data/wwwroot/default/api &&sudo git pull ";
$res = shell_exec($cmd);
var_dump($res);
exit;





