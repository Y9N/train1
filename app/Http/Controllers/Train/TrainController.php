<?php

namespace App\Http\Controllers\Train;

use App\Model\UserModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class TrainController extends Controller
{
    /**
     * 登录调用借口
     */
    public function login()
    {
        #申请app_id  app_key
        //echo 111;die;
        $app_id=md5(1);
        $app_key=md5('123456');


        $login_url="http://train1.com/train/content";
        #组装参数
        $param=[
            'account_name'=>'yuanchao',
            'psd'=>'123456',
            'app_id'=>$app_id
        ];


        #数据加密
        $encrypt_data=openssl_encrypt(json_encode($param),'AES-256-CBC','yuanchao',false,'q1q1q1q1q1q1q1q1');

        $api_param=[];

        $api_param['data']=$encrypt_data;
        //var_dump($encrypt_data);die;
        #参数排序
        ksort($param);

        #把参数转换为a=1&b=2
        $str=http_build_query($param);


        $sign_str=$str.'&app_key='.$app_key;

        #生成签名
        $sign=md5($sign_str);

        #在请求的数组中加上签名
        $api_param['sign']=$sign;
        //var_dump($api_param);die;
        $ch=curl_init();

        #设置请求的url
        curl_setopt($ch,CURLOPT_URL,$login_url);

        #不输出，返回字符串
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        #设置post提交
        curl_setopt($ch,CURLOPT_POST,1);

        #设置post提交字段
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($api_param));

        #发送请求
        $data=curl_exec($ch);
        //var_dump($data);die;
        if(curl_errno($ch)){
            var_dump(curl_errno($ch));
            var_dump(curl_error());
            die;
        }
        $data=json_decode($data,true);
        $decrypt = openssl_decrypt($data['data'] , 'AES-256-CBC','yuanchao',false,'q1q1q1q1q1q1q1q1');
        $decrypt=json_decode($decrypt,true);

        ksort($decrypt);
        $str = http_build_query($decrypt);
        $parm_str = $str.'&app_key='.$app_key;
        $sign = md5($parm_str);
        if($sign == $data['sign']){
            echo 'ok';
        }else{
            echo 'nnoonon';
        }
    }

    public function content(Request $request)
    {
        //echo 111;
        //var_dump($request->all());
        return ['name'=>'yuachao'];
    }

    /**
     * 接收type登录
     */
    public function typelogin()
    {
        $type=$_POST['type'];
        $info=UserModel::where('name',$_POST['name'])->first();
        if($info['pwd']!=$_POST['pwd']){
            die('账号或密码错误');
        }
        $id=$info['id'];
        if($type=='pc'){
            echo 'pc';
            $this->pclogin($info);
        }elseif($type=='android'){
            echo 'android';
            $this->androidlogin($info);
        }elseif($type=='ios'){
            echo 'ios';
            $this->ioslogin($info);
        }
    }

    /**
     * @param $data
     * 处理电脑登录
     */
    public function pclogin($data)
    {
        if($data['type']==2){
            //判断安卓是否在线，改成安卓电脑同时在线
            $type=4;
        }elseif($data['type']==3){
            //判断ios是否在线，改成ios电脑同时在线
            $type=5;
        }elseif(empty($data['type'])){
            //判断有无状态，改成电脑在线
            $type=1;
        }else{
            die('已经登录过了');
        }
        $rs=UserModel::where('id',$data['id'])->update(['type'=>$type]);
        if($rs===false){
            die('登录失败');
        }
        $id=$data['id'];
        $pc_token=rand(1000,9999);
        Redis::hset("login:id:$id","pc:token",$pc_token);
        echo '登陆成功';
    }

    /**
     * @param $data
     * 安卓登录处理
     */
    public function androidlogin($data)
    {
        if($data['type']==1){
            //判断电脑是否在线，改成安卓电脑同时在线
            $type=4;
        }elseif($data['type']==2||$data['type']==4){
            //判断安卓是否在线，勿重复登陆
            die('已经登录过了');
        }elseif($data['type']==5){
            $type=4;
        }else{
            //ios登录 或者未登录 将状态改成android在线
            $type=2;
        }
        $rs=UserModel::where('id',$data['id'])->update(['type'=>$type]);
        if($rs===false){
            die('登录失败');
        }
        $id=$data['id'];
        $android_token=rand(1000,9999);
        Redis::hdel("login:id:$id","ios:token");
        Redis::hset("login:id:$id","android:token",$android_token);
        echo '登陆成功';
        var_dump($rs);
    }

    /**
     * @param $data
     * ios登录处理
     */
    public function ioslogin($data)
    {
        if($data['type']==1){
            //判断电脑是否在线，改成ios电脑同时在线
            $type=5;
        }elseif($data['type']==3||$data['type']==5){
            die('已经登录过啦');
        }elseif($data['type']==4){
            $type=5;
        }else{
            //安卓登录 或者未登录 改成iOS登录
            $type=3;
        }
        $rs=UserModel::where('id',$data['id'])->update(['type'=>$type]);
        if($rs===false){
            die('登录失败');
        }
        $id=$data['id'];
        $ios_token=rand(1000,9999);
        Redis::hdel("login:id:$id","android:token");
        Redis::hset("login:id:$id","ios:token",$ios_token);
        echo '登陆成功';
        var_dump($rs);
    }
}
