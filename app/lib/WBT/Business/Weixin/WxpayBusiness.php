<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-7-29
 * Time: 下午3:03
 */

namespace WBT\Business\Weixin;
use WBT\Business\ConfigBusiness;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\Weixin\MpUserBusiness;
require_once LIB.'/Wxpay/WxPayHelper.php';
include_once LIB.'/WxPayPub/WxPayPubHelper.php';

class WxpayBusiness
{
    public static function getBizPackage($orderID,$totalFee,$mpUserID)
    {
        $mpUser = MpUserBusiness::getMpUser($mpUserID);
        $partnerKey = $mpUser->getPartnerKey();//ConfigBusiness::getPartnerKey();
        $appKey = $mpUser->getPaySignKey();//ConfigBusiness::getPaySignKey();
        $appId = $mpUser->getAppID();//ConfigBusiness::getAppId();
        $signType = 'SHA1';
        $spbillCreateIp = WxUserBusiness::getWxUserIP();
        $commonUtil = new \CommonUtil();
        $wxPayHelper = new \WxPayHelper($partnerKey,$appKey,$appId,$signType);
        $wxPayHelper->setParameter("bank_type", "WX");
        $wxPayHelper->setParameter("body", "$orderID");
        $wxPayHelper->setParameter("partner", $mpUser->getPartnerID());//ConfigBusiness::getPartnerId()
        $wxPayHelper->setParameter("out_trade_no", $commonUtil->create_noncestr());
        $wxPayHelper->setParameter("attach", $mpUserID);
        $wxPayHelper->setParameter("total_fee", $totalFee);
        $wxPayHelper->setParameter("fee_type", "1");
        $host = ConfigBusiness::getHost();
        $notifyUrl = $host."/wx_user/pay/notify?order_id=".$orderID;
        $wxPayHelper->setParameter("notify_url", $notifyUrl);
        $wxPayHelper->setParameter("spbill_create_ip", $spbillCreateIp);
        $wxPayHelper->setParameter("input_charset", "UTF-8");
        $bizPackage = $wxPayHelper->create_biz_package();
        return $bizPackage;
    }

    public static function deliverNotifyPackage($mpUserID,$wxUserID,$transactionId,$outTradeNo,$deliverTimestamp,$deliverStatus,$deliverMsg)
    {
        $mpUser = MpUserBusiness::getMpUser($mpUserID);
        $partnerKey = $mpUser->getPartnerKey();//ConfigBusiness::getPartnerKey();
        $appKey = $mpUser->getPaySignKey();//ConfigBusiness::getPaySignKey();
        $appId = $mpUser->getAppID();//ConfigBusiness::getAppId();
        $signType = 'SHA1';
        $wxPayHelper = new \WxPayHelper($partnerKey,$appKey,$appId,$signType);
        $signMethod = "SHA1";
        $nativeObj["appid"] = $appId;
        $nativeObj["openid"] = $wxUserID;
        $nativeObj["transid"] = $transactionId;
        $nativeObj["out_trade_no"] = $outTradeNo;
        $nativeObj["deliver_timestamp"] = $deliverTimestamp;
        $nativeObj["deliver_status"] = $deliverStatus;
        $nativeObj["deliver_msg"] = $deliverMsg;
        $nativeObj["app_signature"] = $wxPayHelper->get_biz_sign($nativeObj);//sha1(\CommonUtil::formatBizQueryParaMap($nativeObj,false));
        $nativeObj["sign_method"] = $signMethod;
        return  ($nativeObj);
    }
//缴费通知单回调信息
    public static function getBillBizPackage($billID,$totalFee,$mpUserID,$wxUserID)
    {
        $mpUser = MpUserBusiness::getMpUser($mpUserID);
        $partnerKey = $mpUser->getPartnerKey();//ConfigBusiness::getPartnerKey();
        $appKey = $mpUser->getPaySignKey();//ConfigBusiness::getPaySignKey();
        $appId = $mpUser->getAppID();//ConfigBusiness::getAppId();
        $signType = 'SHA1';
        $spbillCreateIp = WxUserBusiness::getWxUserIP();
        $commonUtil = new \CommonUtil();
        $wxPayHelper = new \WxPayHelper($partnerKey,$appKey,$appId,$signType);
        $wxPayHelper->setParameter("bank_type", "WX");
        $wxPayHelper->setParameter("body", "物业费用");
        $wxPayHelper->setParameter("partner", $mpUser->getPartnerID());//ConfigBusiness::getPartnerId()
        $wxPayHelper->setParameter("out_trade_no", $commonUtil->create_noncestr());
        $wxPayHelper->setParameter("attach", $mpUserID);
        $wxPayHelper->setParameter("total_fee", $totalFee);
        $wxPayHelper->setParameter("fee_type", "1");
        $host = ConfigBusiness::getHost();
        $notifyUrl = $host."/wx_user/pay/notify?bill_id=".$billID."&wx_user_id=".$wxUserID;
        $wxPayHelper->setParameter("notify_url", $notifyUrl);
        $wxPayHelper->setParameter("spbill_create_ip", $spbillCreateIp);
        $wxPayHelper->setParameter("input_charset", "UTF-8");
        $bizPackage = $wxPayHelper->create_biz_package();
        return $bizPackage;
    }

     //新微信支付不包含城市展望
    public static function getBizPackageNew($orderID,$totalFee,$mpUserID,$communityID,$storeID,$wxUserID)
    {
        //使用jsapi接口
        $mpUser = MpUserBusiness::getMpUser($mpUserID);
        $app_id = $mpUser->getAppID();
        $app_secret = $mpUser->getAppSecret();
        $mchid = $mpUser->getMchid();
        $pay_key = $mpUser->getPayKey();
        $js_api_call_url = $mpUser->getJsApiCallUrl();
        $sslcert_path = $mpUser->getSslcertPath();
        $sslkey_path = $mpUser->getSslkeyPath();
        $notify_url = $mpUser->getNotifyUrl();
        $curl_timeout = $mpUser->getCurlTimeout();
        $jsApi = new \JsApi_pub($app_id,$app_secret,$pay_key,$sslcert_path,$sslkey_path,$curl_timeout);
        /*
        //=========步骤1：网页授权获取用户openid============
        //通过code获得openid
        if (!isset($_GET['code']))
        {
            //触发微信返回code码
            $url = $jsApi->createOauthUrlForCode(\WxPayConf_pub::JS_API_CALL_URL);
            Header("Location: $url");
        }else
        {
            //获取code码，以获取openid
            $code = $_GET['code'];
            $jsApi->setCode($code);
            $openid = $jsApi->getOpenId();
        }
       */
        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub($pay_key,$sslkey_path,$sslkey_path,$mchid,$app_id,$curl_timeout);

        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $openid = $wxUserID;
        $unifiedOrder->setParameter("openid","$openid");//商品描述
        $unifiedOrder->setParameter("body","$orderID");//商品描述
        $unifiedOrder->setParameter("attach","$orderID");//商品描述
        //自定义订单号，此处仅作举例
        $timeStamp = time();
        $out_trade_no = $app_id."$timeStamp";
        $unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
        $unifiedOrder->setParameter("total_fee",$totalFee);//总金额
        $unifiedOrder->setParameter("notify_url",$notify_url);//通知地址
        $unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID

        $prepay_id = $unifiedOrder->getPrepayId();
        //=========步骤3：使用jsapi调起支付============
        $jsApi->setPrepayId($prepay_id);

        return $jsApiParameters = $jsApi->getParameters();
    }
} 