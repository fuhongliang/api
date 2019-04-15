<?php

namespace App\Http\Middleware;
use App\Http\Controllers\BaseController;
use App\model\V1\Token;
use App\model\V2\Store;
use Illuminate\Http\Request;
use Closure;
use App\Http\Controllers\BaseController as Base;
use Illuminate\Support\Facades\Crypt;

class checkToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token=$request->header('token');
        if (!$token)
        {
            return Base::jsonReturn(3000,  'token缺失');
        }else{
            $token=Token::getTokenField(['token'=>$token],['token','expire_time','store_id']);
            if (empty($token) || ! Store::getStoreInfo(['store_id'=>$token->store_id]))
            {
                return Base::jsonReturn(3001,  'token伪造');
            }else{
                $time=time();
                if($time>$token->expire_time)
                {
                    return Base::jsonReturn(3002, 'token已过期');
                }
            }
        }
        return $next($request);
    }
}
