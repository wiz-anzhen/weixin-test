<?php
/*
 *  Copyright (c) 2013 The CCP project authors. All Rights Reserved.
 *
 *  Use of this source code is governed by a Beijing Speedtong Information Technology Co.,Ltd license
 *  that can be found in the LICENSE file in the root of the web site.
 *
 *   http://www.cloopen.com
 *
 *  An additional intellectual property rights grant can be found
 *  in the file PATENTS.  All contributing project authors may
 *  be found in the AUTHORS file in the root of the source tree.
 */

/**
 * 发起HTTPS请求
 */
function curl_post($url,$data,$header,$post=1)
{
    //初始化curl
    $ch = curl_init();
    //参数设置
    $res= curl_setopt ($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt ($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, $post);
    if( $post)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
    $result = curl_exec ($ch);
    //连接失败
    if($result == FALSE){
        print curl_error($ch);
    }
    curl_close($ch);
    return $result;
}


class REST {
    private $main_account;
    private $main_token;
    private $app_id;
    private $batch;
    function __construct($main_account,$main_token,$app_id)
    {
        $this->main_account = $main_account;
        $this->main_token = $main_token;
        $this->app_id = $app_id;
        $this->batch= time();
        $this->address = "app.cloopen.com:8883";
        $this->soft_version = "2013-03-22";
    }

    /**
     * 创建子账户
     * @param friendlyName 子账户名称
     */
    function CreateSubAccount($friendlyName)
    {
        // 拼接请求包体
        $body="
			<SubAccount>
            <appId>$this->app_id</appId>
			<friendlyName>$friendlyName</friendlyName>
			<accountSid>$this->main_account</accountSid>
			</SubAccount>";
        // 大写的sig参数
        $sig =  strtoupper(md5($this->main_account . $this->main_token . $this->batch));
        // 生成请求URL
        $url="https://$this->address/$this->soft_version/Accounts/$this->main_account/SubAccounts?sig=$sig";
        // 生成授权：主账户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->main_account . ":" . $this->batch);
        // 生成包头
        $header = array("Accept:application/xml","Content-Type:application/xml;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = curl_post($url,$body,$header);

        return $result;
    }

    /**
     * 双向回呼
     * @param from 主叫电话号码。号码前需加0086
     * @param to 被叫电话号码。号码前需加0086
     * @param voip_account VoIP号码
     * @param sub_account 子账户Id
     * @param sub_token 子账户的授权令牌
     */
    function CallBack($from,$to,$voip_account,$sub_account,$sub_token)
    {
        // 拼接请求包体
        $body= "<CallBack>
			<subAccountSid>$sub_account</subAccountSid>
            <voipAccount>$voip_account</voipAccount>
			<from>$from</from>
			<to>$to</to>
			</CallBack>";
        // 大写的sig参数
        $sig =  strtoupper(md5($sub_account . $sub_token . $this->batch));
        // 生成请求URL
        $url="https://$this->address/$this->soft_version/SubAccounts/$sub_account/Calls/Callback?sig=$sig";
        // 生成授权：子账户Id + 英文冒号 + 时间戳。
        $authen=base64_encode($sub_account . ":" . $this->batch);
        // 生成包头
        $header = array("Accept:application/xml","Content-Type:application/xml;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = curl_post($url,$body,$header);

        return $result;
    }

    /**
     * 发送短信
     * @param to 短信接收端手机号码集合,用逗号分开
     * @param body 短信正文
     * @param msgType 消息类型。取值0（普通短信）、1（长短信），默认值 0
     * @param sub_account 子账户Id
     */
    function SendSMS($to,$body,$msgType,$sub_account)
    {
        // 拼接请求包体
        $body="<SMSMessage>
			<to>$to</to>
			<body>$body</body>
			<msgType>$msgType</msgType>
			<appId>$this->app_id</appId>
            <subAccountSid>$sub_account</subAccountSid>
			</SMSMessage>";
        // 大写的sig参数
        $sig =  strtoupper(md5($this->main_account . $this->main_token . $this->batch));
        // 生成请求URL
        $url="https://$this->address/$this->soft_version/Accounts/$this->main_account/SMS/Messages?sig=$sig";
        // 生成授权：主账户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->main_account . ":" . $this->batch);
        // 生成包头
        $header = array("Accept:application/xml","Content-Type:application/xml;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = curl_post($url,$body,$header);

        return $result;
    }

    /**
     * 营销外呼
     * @param to 被叫号码
     * @param mediaName 语音文件名称
     */
    function LandingCall($to,$mediaName,$playTimes)
    {
        // 拼接请求包体
        $body="<LandingCall>
               <mediaTxt>$mediaName</mediaTxt>
               <to>$to</to>
               <appId>$this->app_id</appId>
               <playTimes>$playTimes</playTimes>
               </LandingCall>";
        // 大写的sig参数
        $sig =  strtoupper(md5($this->main_account . $this->main_token . $this->batch));
        // 生成请求URL
        $url="https://$this->address/$this->soft_version/Accounts/$this->main_account/Calls/LandingCalls?sig=$sig";
        // 生成授权：主账户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->main_account . ":" . $this->batch);
        // 生成包头
        $header = array("Accept:application/xml","Content-Type:application/xml;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = curl_post($url,$body,$header);

        return $result;
    }

    /**
     * 语音验证码
     * @param verifyCode 验证码内容，为数字和英文字母，不区分大小写，长度4-20位
     * @param playTimes 播放次数，1－3次
     * @param to 接收号码
     * @param respUrl 用户接听呼叫后会发起请求通知应用用户已经接听（选填）
     */
    function VoiceVerifyCode($verifyCode,$playTimes,$to,$respUrl)
    {
        // 拼接请求包体
        $body="<VoiceVerify>
               <appId>$this->app_id</appId>
               <verifyCode>$verifyCode</verifyCode>
               <playTimes>$playTimes</playTimes>
               <to>$to</to>
               <respUrl>$respUrl</respUrl>
               </VoiceVerify>";
        // 大写的sig参数
        $sig =  strtoupper(md5($this->main_account . $this->main_token . $this->batch));
        // 生成请求URL
        $url="https://$this->address/$this->soft_version/Accounts/$this->main_account/Calls/VoiceVerify?sig=$sig";
        // 生成授权：主账户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->main_account . ":" . $this->batch);
        // 生成包头
        $header = array("Accept:application/xml","Content-Type:application/xml;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = curl_post($url,$body,$header);

        return $result;
    }

    /**
     * 账户查询
     */
    function QueryAccountInfo()
    {
        // 大写的sig参数
        $sig =  strtoupper(md5($this->main_account . $this->main_token . $this->batch));
        // 生成请求URL
        $url="https://$this->address/$this->soft_version/Accounts/$this->main_account/AccountInfo?sig=$sig";
        // 生成授权：主账户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->main_account . ":" . $this->batch);
        // 生成包头
        $header = array("Accept:application/xml","Content-Type:application/xml;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = curl_post($url,"",$header,0);

        return $result;
    }

}
?>
