<?php

namespace App\Http\Controllers\Train;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PcAddIosController extends Controller
{
   public function pc()
   {
       $data=[
           'name'=>'yuanchao',
           'pwd'=>'yuanchao',
           'type'=>'pc'
       ];

       $login_url="http://train1.com/train/typelogin";

       $ch=curl_init();

       #设置请求的url
       curl_setopt($ch,CURLOPT_URL,$login_url);

       #不输出，返回字符串
       curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

       #设置post提交
       curl_setopt($ch,CURLOPT_POST,1);

       #设置post提交字段
       curl_setopt($ch,CURLOPT_POSTFIELDS,$data);

       #发送请求
       $data=curl_exec($ch);
       var_dump($data);
   }


    public function android()
    {
        $data=[
            'name'=>'yuanchao',
            'pwd'=>'yuanchao',
            'type'=>'android'
        ];

        $login_url="http://train1.com/train/typelogin";

        $ch=curl_init();

        #设置请求的url
        curl_setopt($ch,CURLOPT_URL,$login_url);

        #不输出，返回字符串
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        #设置post提交
        curl_setopt($ch,CURLOPT_POST,1);

        #设置post提交字段
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data));

        #发送请求
        $data=curl_exec($ch);
        var_dump($data);
    }


    public function ios()
    {
        $data=[
            'name'=>'yuanchao',
            'pwd'=>'yuanchao',
            'type'=>'ios'
        ];

        $login_url="http://train1.com/train/typelogin";

        $ch=curl_init();

        #设置请求的url
        curl_setopt($ch,CURLOPT_URL,$login_url);

        #不输出，返回字符串
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

        #设置post提交
        curl_setopt($ch,CURLOPT_POST,1);

        #设置post提交字段
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($data));

        #发送请求
        $data=curl_exec($ch);
        var_dump($data);
    }
}
