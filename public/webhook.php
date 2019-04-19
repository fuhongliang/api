<?php

$cmd = "cd /data/wwwroot/api &&sudo git pull origin v2";
$res = shell_exec($cmd);
if($res)
{
    echo "success";
}else{
    echo "error";
}
exit;