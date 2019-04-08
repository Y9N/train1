<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Redis;

class Train1Middleware
{
    private $_data_info= [];
    private $_blank_list= 'blank_list';
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //var_dump(1234567);
        $data = $request->input('data');

        #对数据进行解密
        $data=$this->_AesDecrypt($request);

        #接口防刷
        $data = $this->_apiAccess($request);
        //var_dump($data);die;
        if($data['error'] != 0){
            return response( $data );
        }
        //验签
        $data = $this->_checkSign($request);

        if($data['error'] == 0){
            $request -> request -> replace($this -> _data_info);
            $response= $next($request);
            //后置
            $data=$response->original;
            $decrypt=$this->_Aesencrypt($data);
            $sign=$this->_Sign($data);
            $api_param['data']=$decrypt;
            $api_param['sign']=$sign;
            echo json_encode($api_param);

            exit;
            return $response;
        }else{
            return response( $data );
        }
    }
    /*
     * 对称加密解密
     */
    private function _AesDecrypt($request){
        $data = $request->input('data');

        if(!empty($data)){
            $decrypt = openssl_decrypt($data , 'AES-256-CBC','yuanchao',false,'q1q1q1q1q1q1q1q1');
            //var_dump($decrypt);die;
            $this->_data_info = json_decode($decrypt,true);
        }else{
            return [
                'error' => 3,
                'mag'   => '解密失败'
            ];
        }


    }

    /*
     * 对称加密数据
    */
    private function _Aesencrypt($data){
        if(!empty($data)){
            $decrypt = openssl_encrypt(json_encode($data) , 'AES-256-CBC','yuanchao',false,'q1q1q1q1q1q1q1q1');
            //var_dump($decrypt);die;
            return $decrypt;
        }else{
            return [
                'error' => 3,
                'mag'   => '加密失败'
            ];
        }


    }

    /*
     * 验签
     */
    private function _checkSign($request){
        $date = $request->input('sign');
        //var_dump($date);die;
        if(!empty($date)){
            ksort($this->_data_info);
            $key = $this->_getAppIdKey()['key'];
            $str = http_build_query($this->_data_info);
            $parm_str = $str.'&app_key='.$key;
            $sign = md5($parm_str);
            if($sign == $date){
                return [
                    'error' => 0,
                    'mag'   => '验签成功'
                ];
            }else{
                return [
                    'error' => 1,
                    'mag'   => '验签失败'
                ];
            }
        }else{
            return [
                'error' => 1,
                'mag'   => '数据传输错误'
            ];
        }

    }

    /*
     * 签名
     */
    private function _Sign($data){
            ksort($data);
            $key = $this->_getAppIdKey()['key'];
            $str = http_build_query($data);
            $parm_str = $str.'&app_key='.$key;
            $sign = md5($parm_str);
            return $sign;
    }

    private function _getAppIdKey(){
        return [
            'appid' => md5(1),
            'key'   => md5(123456)
        ];
    }

    /**
     * @param $request
     *获取appid
     */
    private function _getAppId(){
        return $this -> _data_info['app_id'];
    }

    /**
     * @param $request
     * 接口防刷
     */
    private function _apiAccess($request){
        $appid = $this -> _getAppId();
        //var_dump($appid);die;
        $set_time = Redis::zScore($this->_blank_list , $appid);
        if(!empty($set_time)){
            if(time() - $set_time > 1800){
                Redis::zRem($this->_blank_list , $appid);
                $data = $this -> _apiAccessCount();
                return $data;
            }else{
                return [
                    'error' => '2000',
                    'msg'   => '接口已上线，请稍后再试'
                ];
            }
        }else{
            $data = $this -> _apiAccessCount();
            return $data;
        }
    }

    /**
     * @return array
     * 记录次数
     */
    private function _apiAccessCount(){
        $appid = $this -> _getAppId();
        $num = Redis::incr($appid);
        if($num == 1){
            Redis::expire($appid ,60);
        }

        if($num >= 100){
            Redis::zAdd($this->_blank_list , time() , $appid);
            Redis::del($appid);
            return [
                'error' => '2000',
                'msg'   => '接口已上线，请稍后再试'
            ];
        }else{
            return [
                'error' => '0',
                'msg'   => '成功'
            ];
        }
    }
}
