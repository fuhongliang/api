<?php

namespace app\http\middleware;
use app\v1\controller\Base;
use app\v1\model\Token as TokenModel;


class checkToken
{
    public function handle($request, \Closure $next)
    {

        $token=input('server.HTTP_TOKEN');
        if (!$token)
        {
            return Base::jsonReturn(3000, null, 'token缺失');
        }else{
            $token=TokenModel::getTokenField(['token'=>$token],['expire_time']);
            if (empty($token))
            {
                return Base::jsonReturn(3001, null, 'token伪造');
            }else{
                $time=time();
                if($time>$token['expire_time'])
                {
                    return Base::jsonReturn(3002, null, 'token已过期');
                }
            }
        }
        return $next($request);
    }
}
