<?php

$cmd = "cd /data/wwwroot/default/api &&sudo git pull origin v3";
$res = shell_exec($cmd);
if($res)
{
    echo "success";
}else{
    echo "error";
}
exit;