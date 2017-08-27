<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-7-29
 * Time: 上午11:21
 */

namespace WBT\Controller\WxUser;


use MP\Model\Mp\BillPayMethod;
use MP\Model\Mp\Order;
use MP\Model\Mp\OrderStatus;
use MP\Model\Mp\WxUser;
use WBT\Business\ConfigBusiness;
use WBT\Business\MailBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use WBT\Business\Weixin\WxpayBusiness;
use MP\Model\Mp\WxPayRecord;
use Common\Helper\BaseController;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Bill;

include_once LIB.'/WxPayPub/WxPayPubHelper.php';
class PayController extends BaseController
{

    public function indexAction()
    {
        $orderID = $this->_request->get("order_id"); //商品描述
        $order = new Order([Order::ORDER_ID => $orderID]);
        if($order->getPayFinished())
        {
            $this->_view->set('is_finished',$order->getPayFinished());

        }
        $totalFee= $this->_request->get("total_fee"); //总金额
        $mpUserID= $this->_request->get("mp_user_id");
        $communityID= $this->_request->get("community_id");
        $storeID= $this->_request->get("store_id");
        $this->_view->set('total_fee',number_format($totalFee,2,'.',''));
        $wxUserID= $this->_request->get("wx_user_id");
        $payMethod= $this->_request->get("pay_method");
        $totalFee =  intval($totalFee * 100);
        $orderID = (string)$orderID;
        log_debug("===============".$orderID);
        log_debug("===============".$totalFee);

        if($payMethod != "cash_pay")
        {
            $bizPackage = WxpayBusiness::getBizPackage($orderID,$totalFee,$mpUserID,$communityID,$storeID);
            $this->_view->set('biz_package',$bizPackage);
        }

        $this->_view->set('order_id',$orderID);
        $this->_view->set('community_id',$communityID);
        $this->_view->set('store_id',$storeID);
        $this->_view->set('mp_user_id',$mpUserID);
        $this->_view->set('wx_user_id',$wxUserID);
        log_debug("===============".$payMethod);
        if($payMethod == "cash_pay")
        {
            $this->changeView('WBT/WxUser/Pay.cash.html');
        }

    }

    public function notifyAction()
    {
        $bankBillNo = $this->_request->get("bank_billno"); //银行订单号
        $bankType = $this->_request->get("bank_type"); //银行类型
        $discount = $this->_request->get("discount"); //折扣价格
        $feeType = $this->_request->get("fee_type"); //现金支付币种  1 人民币
        $inputCharset = $this->_request->get("input_charset"); //字符编码
        $notifyId = $this->_request->get("notify_id"); //支付结果通知ID
        $outTradeNo = $this->_request->get("out_trade_no"); //商户系统订单号 与请求一致
        $attach = $this->_request->get("attach"); //商户号
        $partner = $this->_request->get("partner"); //商户号
        $productFee = $this->_request->get("product_fee"); //物品费用
        $sign = $this->_request->get("sign");  //签名
        $signType = $this->_request->get("sign_type"); //签名方式MD5 RSA 默认MD5
        $timeEnd = $this->_request->get("time_end"); //支付完成时间
        $totalFee = $this->_request->get("total_fee"); //支付金额
        $tradeMode = $this->_request->get("trade_mode"); //交易模式 1 即时到帐
        $tradeState = $this->_request->get("trade_state");  //交易状态 支付结果 0 表示成功
        $transactionId = $this->_request->get("transaction_id"); //交易号 28位数值 前10位=商户号+8位订单产生日期+10位流水号
        $transportFee = $this->_request->get("transport_fee"); //物流费用
        $orderID = $this->_request->get("order_id"); //物流费用

        $postStr =  $this->getRequest()->getRawBody();
        /*
         * wxapibusiness  增加方法分析postStr从微信服务器获取的xml 获取用户wx_user_id
         */
        $billID = $this->_request->get("bill_id"); //物流费用
        if(empty($billID))
        {
            $this->_view->set('attach',$attach);
            $order =  new Order([Order::ORDER_ID => $orderID]);
            if($tradeState == 0)
            {
                $this->_view->set('notify_wx',"支付成功");
                $payFinished = 1;
                $order->setPayFinished(1)->setStatus(OrderStatus::VERIFIED_TO_SHIP)->update();
            }
            else
            {
                $this->_view->set('notify_wx',"支付失败");
                $payFinished = 0;
            }


            $wxUserID = $order->getWxUserID();
            $wxUserName = $order->getCustomerName();
            $mpUserID = $order->getMpUserID();
            $communityID = $order->getCommunityID();
            $wxPayRecord = new WxPayRecord([WxPayRecord::TRANSACTIONID => $transactionId]);

            //调用发货通知
            $deliverTimestamp = trim(time().'');//发货时间戳
            $deliverStatus = '1';
            $deliverMsg = 'ok';
            if($wxPayRecord->isEmpty())
            {
                $wxPayRecord = new WxPayRecord();
                $wxPayRecord->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setUsername($wxUserName)->setPayFinished($payFinished)->setPayValue($totalFee)->setPayMethod("wx_pay")->setPayIterm("商城支付")->setOuttradeno($outTradeNo)->setTransactionid($transactionId)->setOrderID($orderID)->setPayStartDate($order->getCreateTime())->setPayEndDate(time())->insert();

            }
            if($tradeState != '0')
            {
                //调用发货通知
                $deliverStatus = '0';
                $deliverMsg = '失败';
            }
            $postData = WxpayBusiness::deliverNotifyPackage($mpUserID,$wxUserID,$transactionId,$outTradeNo,$deliverTimestamp,$deliverStatus,$deliverMsg);
            WxApiBusiness::sentDeliverNotify($mpUserID,$postData);
        }
        else
        {
            $wxUserID = $this->_request->get("wx_user_id");
            $this->_view->set('attach',$attach);
            $bill = new Bill([Bill::BILL_ID => $billID]);
            if($tradeState == 0)
            {
                $this->_view->set('notify_wx',"支付成功");
                $payFinished = 1;
                $bill->setPayFinished(1)->setBillPayMethod(BillPayMethod::WX_PAY)->update();
            }
            else
            {
                $this->_view->set('notify_wx',"支付失败");
                $payFinished = 0;
            }

            $mpUserID = $bill->getMpUserID();
            $wxUserName = $bill->getName();
            $communityID = $bill->getCommunityID();
            $wxPayRecord = new WxPayRecord([WxPayRecord::TRANSACTIONID => $transactionId]);

            //调用发货通知
            $deliverTimestamp = trim(time().'');//发货时间戳
            $deliverStatus = '1';
            $deliverMsg = 'ok';

            if($wxPayRecord->isEmpty())
            {
                $wxPayRecord = new WxPayRecord();
                $wxPayRecord->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setUsername($wxUserName)->setPayFinished($payFinished)->setPayValue($totalFee)->setPayMethod("wx_pay")->setPayIterm("物业支付")->setOuttradeno($outTradeNo)->setTransactionid($transactionId)->insert();

            }
            if($tradeState != '0')
            {
                //调用发货通知
                $deliverStatus = '0';
                $deliverMsg = '失败';
            }
            $postData = WxpayBusiness::deliverNotifyPackage($mpUserID,$wxUserID,$transactionId,$outTradeNo,$deliverTimestamp,$deliverStatus,$deliverMsg);
            WxApiBusiness::sentDeliverNotify($mpUserID,$postData);
        }





    }
    /*
     * 告警通知
     */
    public function warningAction()
    {
        $postStr =  $this->getRequest()->getRawBody();
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

        $errorType = $postObj->ErrorType;//错误类型
        $errorDescription = $postObj->Description;//错识描述
        $errorDetail = $postObj->AlarmContent; //错误详情
        log_debug("postStr======".$errorType);
        log_debug("postStr======".$errorDescription);
        log_debug("postStr======".$errorDetail);
        $htmlContent = "错误类型".$errorType."错识描述".$errorDescription."错误详情".$errorDetail;
        $toEmail = ConfigBusiness::getEmailTechnology();
        $toEmail = explode(",",$toEmail);
        MailBusiness::sendMailAsyn($toEmail,$cc=null, $subject="微信支付告警通知", $htmlContent);
        echo "success";
    }

    /*
     * 用户维权通知URL
     */
    public function feedBackAction()
    {
        $postStr =  $this->getRequest()->getRawBody();
        log_debug("postStr:".$postStr);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $wxUserId = $postObj->getOpenId;
        $feedBackId = $postObj->getFeedBackId;
        $appID = $postObj->getAppId;

        $mpUser = new MpUser([MpUser::API_ID => $appID]);
        if($mpUser->isEmpty())
        {
            log_error("error,could not find mpUser.[appID:$appID]");
            echo "error";
            return;
        }

        $mpUserID = $mpUser->getMpUserID();
        WxApiBusiness::sentFeedBack($mpUserID,$wxUserId,$feedBackId);
        echo "success";
    }

    //付款成功
    public function successAction()
    {
        $wxUserID = $this->_wxUserID;
        $mpUserID = $this->_request->get( 'mp_user_id' );
        $communityID = $this->_request->get( 'community_id' );
        $storeID = $this->_request->get( 'store_id' );
        $this->_view->set("mp_user_id",$mpUserID);
        $this->_view->set("wx_user_id",$wxUserID);
        $this->_view->set("community_id",$communityID);
        $this->_view->set("store_id",$storeID);
    }

//缴费通知单支付
    public function payBillAction()
    {
        $billID = $this->_request->get("bill_id"); //缴费通知单id
        $bill = new Bill([Bill::BILL_ID => $billID]);
        $this->_view->set('house_name', $bill->getName());
        $this->_view->set('house_address', $bill->getHouseAddress());
        $billDay = $bill->getBillDay();
        $billDay = substr($billDay,0,4)."-".substr($billDay,4,2)."-".substr($billDay,6,2);
        $this->_view->set('bill_day', $billDay);

        $totalFee= $this->_request->get("total_fee"); //总金额
        $mpUserID= $this->_request->get("mp_user_id");
        $communityID= $this->_request->get("community_id");

        $this->_view->set('total_fee',number_format($totalFee,2,'.',''));
        $wxUserID= $this->_request->get("wx_user_id");

        $totalFee =  intval($totalFee * 100);
        $billID = (string)$billID;
        log_debug("===============".$billID);
        log_debug("===============".$totalFee);

        $bizPackage = WxpayBusiness::getBillBizPackage($billID,$totalFee,$mpUserID,$wxUserID);
        $this->_view->set('biz_package',$bizPackage);

        $this->_view->set('bill_id',$billID);
        $this->_view->set('community_id',$communityID);

        $this->_view->set('mp_user_id',$mpUserID);
        $this->_view->set('wx_user_id',$wxUserID);

    }
//缴费通知单支付通知
    public function notifyBillAction()
    {
        $bankBillNo = $this->_request->get("bank_billno"); //银行订单号
        $bankType = $this->_request->get("bank_type"); //银行类型
        $discount = $this->_request->get("discount"); //折扣价格
        $feeType = $this->_request->get("fee_type"); //现金支付币种  1 人民币
        $inputCharset = $this->_request->get("input_charset"); //字符编码
        $notifyId = $this->_request->get("notify_id"); //支付结果通知ID
        $outTradeNo = $this->_request->get("out_trade_no"); //商户系统订单号 与请求一致
        $attach = $this->_request->get("attach"); //商户号
        $partner = $this->_request->get("partner"); //商户号
        $productFee = $this->_request->get("product_fee"); //物品费用
        $sign = $this->_request->get("sign");  //签名
        $signType = $this->_request->get("sign_type"); //签名方式MD5 RSA 默认MD5
        $timeEnd = $this->_request->get("time_end"); //支付完成时间
        $totalFee = $this->_request->get("total_fee"); //支付金额
        $tradeMode = $this->_request->get("trade_mode"); //交易模式 1 即时到帐
        $tradeState = $this->_request->get("trade_state");  //交易状态 支付结果 0 表示成功
        $transactionId = $this->_request->get("transaction_id"); //交易号 28位数值 前10位=商户号+8位订单产生日期+10位流水号
        $transportFee = $this->_request->get("transport_fee"); //物流费用
        $billID = $this->_request->get("bill_id"); //物流费用
        $wxUserID = $this->_request->get("wx_user_id");
        $postStr =  $this->getRequest()->getRawBody();
        /*
         * wxapibusiness  增加方法分析postStr从微信服务器获取的xml 获取用户wx_user_id
         */

        $this->_view->set('attach',$attach);
        $bill = new Bill([Bill::BILL_ID => $billID]);
        if($tradeState == 0)
        {
            $this->_view->set('notify_wx',"支付成功");
            $payFinished = 1;
            $bill->setPayFinished(1)->setBillPayMethod(BillPayMethod::WX_PAY)->update();
        }
        else
        {
            $this->_view->set('notify_wx',"支付失败");
            $payFinished = 0;
        }

        $mpUserID = $bill->getMpUserID();
        $wxUserName = $bill->getName();
        $communityID = $bill->getCommunityID();
        $wxPayRecord = new WxPayRecord([WxPayRecord::TRANSACTIONID => $transactionId]);

        //调用发货通知
        $deliverTimestamp = trim(time().'');//发货时间戳
        $deliverStatus = '1';
        $deliverMsg = 'ok';

        if($wxPayRecord->isEmpty())
        {
            $wxPayRecord = new WxPayRecord();
            $wxPayRecord->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setUsername($wxUserName)->setPayFinished($payFinished)->setPayValue($totalFee)->setPayMethod("wx_pay")->setPayIterm("物业支付")->setOuttradeno($outTradeNo)->setTransactionid($transactionId)->insert();

        }
        if($tradeState != '0')
        {
            //调用发货通知
            $deliverStatus = '0';
            $deliverMsg = '失败';
        }
        $postData = WxpayBusiness::deliverNotifyPackage($mpUserID,$wxUserID,$transactionId,$outTradeNo,$deliverTimestamp,$deliverStatus,$deliverMsg);
        WxApiBusiness::sentDeliverNotify($mpUserID,$postData);

    }

    //新商城支付用于餐饮
    public function storeAction()
    {
        $orderID = $this->_request->get("order_id"); //商品描述
        $totalFee= $this->_request->get("total_fee"); //总金额
        $mpUserID= $this->_request->get("mp_user_id");
        $communityID= $this->_request->get("community_id");
        $storeID= $this->_request->get("store_id");
        $this->_view->set('total_fee',number_format($totalFee,2,'.',''));
        $wxUserID= $this->_request->get("wx_user_id");
        $payMethod= $this->_request->get("pay_method");
        $totalFee =  intval($totalFee * 100);
        $order = new Order([Order::ORDER_ID => $orderID]);
        if($order->getPayFinished())
        {
            $this->_view->set('is_finished',$order->getPayFinished());

        }

        $orderID = (string)$orderID;
        log_debug("===============".$orderID);
        log_debug("===============".$totalFee);

        if($payMethod != "cash_pay")
        {
            $bizPackage = WxpayBusiness::getBizPackageNew($orderID,$totalFee,$mpUserID,$communityID,$storeID,$wxUserID);
            $this->_view->set('biz_package',$bizPackage);
        }

        $this->_view->set('order_id',$orderID);
        $this->_view->set('community_id',$communityID);
        $this->_view->set('store_id',$storeID);
        $this->_view->set('mp_user_id',$mpUserID);
        $this->_view->set('wx_user_id',$wxUserID);
        log_debug("===============".$payMethod);
        if($payMethod == "cash_pay")
        {
            $this->changeView('WBT/WxUser/Pay.storeCash.html');
        }

    }

    public function notifyNewAction()
    {
        log_debug("============================对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，");
        //使用通用通知接口
        $notify_new = new \Notify_pub_new();

        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify_new->saveData($xml);

        $outTradeNo = $notify_new->data["out_trade_no"]; //商户系统订单号 与请求一致
        $attach = $notify_new->data["attach"]; //商户号
        log_debug("qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq".$attach);
        $totalFee = $notify_new->data["total_fee"]; //支付金额
        $totalFee = $totalFee/100;
        $transactionId = $notify_new->data["transaction_id"]; //交易号 28位数值 前10位=商户号+8位订单产生日期+10位流水号
        log_debug("qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqq".$transactionId);
        $this->_view->set('attach',$attach);
        $order =  new Order([Order::ORDER_ID => $attach]);
        $mpUserID = $order->getMpUserID();
        $mpUser = new MpUser([MpUser::MP_USER_ID=>$mpUserID]);
        $notify = new \Notify_pub($mpUser->getPayKey(),$mpUser->getSslcertPath(),$mpUser->getSslkeyPath(),$notify_new->data);
        log_debug("============================对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，".$attach);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE)
        {
            $notify->setReturnParameter("return_code","FAIL");//返回状态码
            $notify->setReturnParameter("return_msg","签名失败");//返回信息
        }
        else
        {
            $notify->setReturnParameter("return_code","SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        $this->_view->set('returnXml',$returnXml);

        if($notify->checkSign() == TRUE)
        {
            if ($notify->data["return_code"] == "FAIL")
            {
                //此处应该更新一下订单状态，通信出错
                $this->_view->set('notify_wx',"支付失败");
                $payFinished = 0;
                log_debug("1111111111111111111111111111111111111111111111111");
            }
            elseif($notify->data["result_code"] == "FAIL")
            {
                //此处应该更新一下订单状态，业务出错
                $this->_view->set('notify_wx',"支付失败");
                log_debug("2222222222222222222222222222222222222222222222222222");
                $payFinished = 0;
            }
            else
            {
                //此处应该更新一下订单状态，支付成功

                $this->_view->set('notify_wx',"支付成功");
                $payFinished = 1;
                log_debug("3333333333333333333333333333333333333333333333333");
                if($order->isEmpty())
                {log_debug("空空空空空空空空空空空==================================");
                    $order =  new Order([Order::ORDER_ID => (string)$attach]);
                }
                else
                {
                    log_debug("yesyesyesyesyesyesyesyesyesyesyesyes==================================");
                    $order->setPayFinished(1)->setStatus(OrderStatus::VERIFIED_TO_SHIP)->update();
                }

            }

            //商户自行增加处理流程,
            //例如：更新订单状态
            //例如：数据库操作
            //例如：推送支付完成信息
            log_debug("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa");
            $wxUserID = $order->getWxUserID();
            $wxUserName = $order->getCustomerName();

            $communityID = $order->getCommunityID();
            log_debug("bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb");
            log_debug("=======================".$mpUserID);
            log_debug("=======================".$communityID);
            log_debug("=======================".$transactionId);
            $wxPayRecord = new WxPayRecord([WxPayRecord::MP_USER_ID => $mpUserID,WxPayRecord::COMMUNITY_ID => $communityID,WxPayRecord::TRANSACTIONID => $transactionId]);
            log_debug("=======================".$wxPayRecord->getWxPayRecordID());
            log_debug("=======================",$wxPayRecord->data());
            log_debug("44444444444444444444444444444444444444444444444444444");
            if(empty($attach))
            {
                $attach = "0";
            }
            if($wxPayRecord->isEmpty())
            {log_debug("55555555555555555555555555555555555");
                $wxPayRecord = new WxPayRecord();
                $wxPayRecord->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setUsername($wxUserName)->setPayFinished($payFinished)->setPayValue($totalFee)->setPayMethod("wx_pay")->setPayIterm("商城支付")->setOuttradeno($outTradeNo)->setTransactionid($transactionId)->setOrderID($attach)->setPayStartDate($order->getCreateTime())->setPayEndDate(time())->insert();

            }
            else
            {
                log_debug("kkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk".$wxPayRecord->getMpUserID());
                $wxPayRecord->setMpUserID($mpUserID)->setCommunityID($communityID)->setWxUserID($wxUserID)->setUsername($wxUserName)->setPayFinished($payFinished)->setPayValue($totalFee)->setPayMethod("wx_pay")->setPayIterm("商城支付")->setOuttradeno($outTradeNo)->setTransactionid($transactionId)->setOrderID($attach)->setPayStartDate($order->getCreateTime())->setPayEndDate(time())->update();
            }

        }

    }
} 