<?php

$res = shell_exec("cd /data/wwwroot/default/hook/api && git pull");
var_dump($res);  