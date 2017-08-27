<?php
require_once 'Channel.class.php';
class AndroidIOSPush{
	public static $res ="";
    public static function error_output($str)
    {
        $res = '';
        $res .= "\033[1;40;31m" . $str . "\033[0m" . "\n";
//         echo "\033[1;40;31m" . $str . "\033[0m" . "\n";
    }

    public static function right_output($str)
    {
        $res = '';
        $res .= "\033[1;40;32m" . $str . "\033[0m" . "\n";
//         echo "\033[1;40;32m" . $str . "\033[0m" . "\n";
    }

    public static function push_broadcast($message, $set = null,$userid = '',$channleid='',$message_type)
    {
        $api_key = 'Tn7inzGbo1WbdTLr9yCyFWnF';
        $secret_key = '4NF28sdA15e5gygcXMuynC7jjk03m6P4';
    	$androidRes = self::push_android_broadcast($api_key, $secret_key, $message, $set,$userid,$channleid,$message_type);
        //$androidRes = self::test_pushMessage_android($userid);
        return $androidRes;
        //$iosRes = self::push_ios_broadcast($api_key, $secret_key, $message, $set);

    }
    
    public static function push_ios($message, $set = null,$userid = '',$channleid,$message_type)
    {
    	$api_key = 'Tn7inzGbo1WbdTLr9yCyFWnF';
    	$secret_key = '4NF28sdA15e5gygcXMuynC7jjk03m6P4';
    	$name = '开发证书';
    	$description = '开发证书';
    	$dev_cert = 'ios/push.pem';
    	$fd = fopen($dev_cert, 'r');
    	$dev_cert = fread($fd, filesize($dev_cert));
		fclose($fd);
    	$iosRes = self::push_ios_broadcast($api_key, $secret_key, $message, $set,$userid,$channleid,$message_type);
    	return $iosRes;
    
    }

    // 推送android设备消息
    public static function push_android_broadcast($api_key, $secret_key, $message, $set = null,$userid='',$channleid,$message_type)
    {
    	if ($set) {
            $push_content_id = $set['push_conetent_id'];
        }


        $secretKey = $secret_key;
        $apiKey = $api_key;
        
        $channel = new Channel($apiKey, $secretKey);
        // 推送广播消息 推送类型 取值范围 1-3, 1:单人，2：一群人tag， 3：所有人
        $push_type = 1; 
        $optional[Channel::USER_ID] = $userid;
        //$optional[Channel::CHANNLE_ID] = $channleid;
        $optional[Channel::DEVICE_TYPE] = 3;
        //0:消息 1:通知
        $optional[Channel::MESSAGE_TYPE] = $message_type;
        // 消息类型的内容必须按指定内容发送，示例如下：
        $message_key = "msg_key";
        //$ret = '';
        $ret = $channel->pushMessage($push_type, $message, $message_key, $optional);
        if (false === $ret) {
            self::error_output('WRONG, ' . __FUNCTION__ . ' ERROR!!!!!');
            self::error_output('ERROR NUMBER: ' . $channel->errno());
            self::error_output('ERROR MESSAGE: ' . $channel->errmsg());
            self::error_output('REQUEST ID: ' . $channel->getRequestId());
        } else {
            self::right_output('SUCC, ' . __FUNCTION__ . ' OK!!!!!');
            self::right_output('result: ' . print_r($ret, true));
        }
         return $ret;
    }


    //推送ios设备消息
    public static function push_ios_broadcast($api_key, $secret_key, $message, $set = null,$userid='',$channleid,$message_type)
    {
        if ($set) {
            $push_content_id = $set['push_conetent_id'];
        }

        $secretKey = $secret_key;
        $apiKey = $api_key;

        $channel = new Channel ($apiKey, $secretKey);
        
        $push_type = 1; // 推送广播消息
        $optional[Channel::USER_ID] = $userid;
        //指定发到ios设备
        $optional[Channel::DEVICE_TYPE] = 4;
        //指定消息类型为通知
        $optional[Channel::MESSAGE_TYPE] = 1;
        //如果ios应用当前部署状态为开发状态，指定DEPLOY_STATUS为1，默认是生产状态，值为2.
        //旧版本曾采用不同的域名区分部署状态，仍然支持。
       $optional[Channel::DEPLOY_STATUS] = 1;
        //通知类型的内容必须按指定内容发送，示例如下：
        $messageIOS = '{
		"aps":{
			"alert":"'.$message.'",
			"sound":"",
			"badge":0
		    }
 	    }';
        //print_r($optional);exit;
        $message_key = "msg_key";
        $ret = $channel->pushMessage($push_type, $messageIOS, $message_key, $optional);
        
        /* if (false === $ret) {
            self::error_output('WRONG, ' . __FUNCTION__ . ' ERROR!!!!!');
            self::error_output('ERROR NUMBER: ' . $channel->errno());
            self::error_output('ERROR MESSAGE: ' . $channel->errmsg());
            self::error_output('REQUEST ID: ' . $channel->getRequestId());
        } else {
            self::right_output('SUCC, ' . __FUNCTION__ . ' OK!!!!!');
            self::right_output('result: ' . print_r($ret, true));
        } */
          return $ret;
    }
    
    //推送android设备消息
    public static function  test_pushMessage_android ($user_id)
    {
    	global $apiKey;
    	global $secretKey;
    	$apikey = 'Nk36sVnHFGGfhR6gmNT4vO3x';
    	$secretkey = 'NitfgqWeXGlm7fm6oOdG9NHs7IRvcWMw';
    	$channel = new Channel ( $apikey, $secretkey ) ;
    	//推送消息到某个user，设置push_type = 1;
    	//推送消息到一个tag中的全部user，设置push_type = 2;
    	//推送消息到该app中的全部user，设置push_type = 3;
    	$push_type = 1; //推送单播消息
    	$optional[Channel::USER_ID] = $user_id; //如果推送单播消息，需要指定user
    	//optional[Channel::TAG_NAME] = "xxxx";  //如果推送tag消息，需要指定tag_name
    
    	//指定发到android设备
    	$optional[Channel::DEVICE_TYPE] = 3;
    	//指定消息类型为通知
    	$optional[Channel::MESSAGE_TYPE] = 0;
    	//通知类型的内容必须按指定内容发送，示例如下：
    	print_r($optional);
    	$message = '{
			"title": "test_push",
			"description": "open url",
			"notification_basic_style":7,
			"open_type":1,
			"url":"http://www.baidu.com"
 		}';
    
    	$message_key = "msg_key";
    	$ret = $channel->pushMessage ( $push_type, $message, $message_key, $optional ) ;
    	print_r($ret);
    	if ( false === $ret )
    	{
    		self::error_output ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!!' ) ;
    		self::error_output ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
    		self::error_output ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
    		self::error_output ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
    	}
    	else
    	{
    		self::right_output ( 'SUCC, ' . __FUNCTION__ . ' OK!!!!!' ) ;
    		self::right_output ( 'result: ' . print_r ( $ret, true ) ) ;
    	}
    	return $ret;
    }
    /*
     * 初始化证书
     */
    
    public static function test_initAppIoscert($apiKey,$secretKey, $name, $description, $release_cert, $dev_cert )
    {
    	$channel = new Channel ($apiKey, $secretKey) ;
    	//如果ios应用当前部署状态为开发状态，指定DEPLOY_STATUS为1，默认是生产状态，值为2.
    	//旧版本曾采用不同的域名区分部署状态，仍然支持。
    	//$optional[Channel::DEPLOY_STATUS] = 1;
    	//$devcert = 
    	$ret = $channel->initAppIoscert($name, $description, $release_cert, $dev_cert) ;
    	/* if ( false === $ret )
    	{
    		error_output ( 'WRONG, ' . __FUNCTION__ . ' ERROR!!!!' ) ;
    		error_output ( 'ERROR NUMBER: ' . $channel->errno ( ) ) ;
    		error_output ( 'ERROR MESSAGE: ' . $channel->errmsg ( ) ) ;
    		error_output ( 'REQUEST ID: ' . $channel->getRequestId ( ) );
    	}
    	else
    	{
    		right_output ( 'SUCC, ' . __FUNCTION__ . ' OK!!!!!' ) ;
    		right_output ( 'result: ' . print_r ( $ret, true ) ) ;
    	} */
    	
    	return $ret;
    }
}