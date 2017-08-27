<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\ArticleType;
use MP\Model\Mp\ChannelArticle;
use MP\Model\Mp\Directory;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\UrgentNoticeReadRecord;
use MP\Model\Mp\WxMenuContentType;
use MP\Model\Mp\WxMenuType;
use MP\Model\Mp\WxSubMenu;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\MpRuleNewsItem;
use MP\Model\Mp\WeixinMessageType;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\MpUserConfigType;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\GroupSend;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\Bill;

class ApiBusiness
{
    public static function matchRule(MpUser &$mpUser, $postStr)
    {
        $content = null;
        $responseStr = null;
        if (empty($postStr))
        {
            return $responseStr;
        }

        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $wxUserID = $postObj->FromUserName;
        $mpUserOpenID = $postObj->ToUserName;
        $receiveMsgType = $postObj->MsgType;
        $msgID = $postObj->MsgID;
        $status = $postObj->Status;
        $eventKey = $postObj->EventKey;

        $responseFromUser = $mpUserOpenID;
        $responseToUser = $wxUserID;
        $mpUserType = $mpUser->getMpUserType();
        $wxUser = WxUserBusiness::initWxUserForSession($mpUser, $wxUserID);

        //存储群发回复信息
        if(!empty($msgID) and !empty($status))
        {
            $groupSend = new GroupSend([GroupSend::MSG_ID => $msgID]);
            if(!($groupSend->isEmpty()))
            {
                $groupSend->setStatus($status)->update();
                return null;
            }

        }

        // 保证粉丝数的正确性
        if ($postObj->Event == 'unsubscribe')
        {
            self::processUnSubscribeEvent($mpUser, $wxUser);
            return null;
        }

        if ($postObj->Event == 'subscribe')
        {
            $subscribeConfig = ConfigBusiness::mpUserConfig($mpUser->getMpUserID());
            if ($subscribeConfig[MpUserConfigType::SUBSCRIBE_BOOL_NEWS] == 1)
            {
                if(!$wxUser->getIsFans())
                {
                    $wxUser->setIsFans(1);
                    $wxUser->save();
                }
                ReportBusiness::processSubscribeEvent($mpUser, $wxUser);
                $subscribeUrl = $subscribeConfig[MpUserConfigType::SUBSCRIBE_URL];
                log_debug("555555555555555555555555555=================".$subscribeUrl);
                if(!empty($subscribeUrl))
                {
                    $subscribeUrl = str_replace("#HOST#", get_host(), $subscribeUrl);
                    $subscribeUrl = str_replace("#MP_USER_ID#", $mpUser->getMpUserID(), $subscribeUrl);
                    $subscribeUrl = str_replace("#VIP_NO#", $wxUser->getVipNo(), $subscribeUrl);
                }

                log_debug("====================================".$subscribeUrl);
                $content = [];
                $content['content_type'] = WeixinMessageType::NEWS;
                $content['content'][]= [MpRuleNewsItem::TITLE  =>$subscribeConfig[MpUserConfigType::SUBSCRIBE_TITLE],
                    MpRuleNewsItem::PIC_URL => $subscribeConfig[MpUserConfigType::SUBSCRIBE_PIC],
                    MpRuleNewsItem::URL     => $subscribeUrl,
                    MpRuleNewsItem::DESCRIPTION     => $subscribeConfig[MpUserConfigType::SUBSCRIBE_DESCRIPTION],];
            }
            else
            {
                $content = self::processSubscribeEvent($mpUser, $wxUser);
            }
            return self::getResponseContent($responseFromUser, $responseToUser, $content);
        }

        if ($postObj->Event == 'LOCATION')
        {
            $latitudeUser = $postObj->Latitude;
            $longitudeUser = $postObj->Longitude;
            $wxUser->setLatitudeuser($latitudeUser)->setLongitudeuser($longitudeUser)->update();

        }
        //  处理内部测试命令
        if ($receiveMsgType == WeixinMessageType::TEXT)
        {
            $userMessage = trim($postObj->Content);
            $content = self::processTextRequestForTest($mpUser, $wxUser, $userMessage);

            if(!empty($content))
            {
                return self::getResponseContent($responseFromUser, $responseToUser, $content);
            }
        }

         // 核对底部菜单访问权限
        $wxSubMenu = new WxSubMenu([WxSubMenu::WX_MENU_KEY => $eventKey ]);
        $authority = $wxSubMenu->getAccessAuthority();


        if($authority == "register")
        {
            // 必须是注册才可以访问，没有注册引导用户注册
            if ($wxUser->getPhone() == "")
            {
                $content =  self::getFollowedContent($mpUser, $wxUser);
                $content = str_replace("index", "register", $content);
                return self::getResponseContent($responseFromUser, $responseToUser, $content);
            }
        }


        if($authority == "identify")
        {
            // 必须是会员才可以访问，非会员引导用户认证
            if (!WxUserBusiness::isMember($wxUser))
            {
                $content =  self::getFollowedContent($mpUser, $wxUser);
                return self::getResponseContent($responseFromUser, $responseToUser, $content);
            }

            // 用户从微信中退出后，让其登陆
            if ($wxUser->getIsQuit() == 1)
            {
                $content =  self::getFollowedContent($mpUser, $wxUser);
                $content = str_replace("index", "login", $content);
                return self::getResponseContent($responseFromUser, $responseToUser, $content);
            }
        }
        // 注册+认证
        if($authority == "other")
        {
            if ($wxUser->getPhone() == "")
            {
                $content =  self::getFollowedContent($mpUser, $wxUser);
                $content = str_replace("index", "other", $content);
                return self::getResponseContent($responseFromUser, $responseToUser, $content);
            }
        }



        if ($receiveMsgType == WeixinMessageType::EVENT && $postObj->Event == WxMenuType::CLICK)
        {
            if(!$mpUser->getValid())
            {
                $content = '无效的菜单，建议先取消关注再关注。';
                return self::getResponseContent($responseFromUser, $responseToUser, $content);

            }
            $content = self::processClickEvent($mpUser, $wxUser, $eventKey);
        }
        // 处理长微博信息
        $cwbMpUserID = ConfigBusiness::getCwbMpUserID();

        if($mpUserType == 1)
        {
            // 文本消息
            if ($receiveMsgType == WeixinMessageType::TEXT)
            {
                // 字符串预处理，去掉两头空格，全角转半角
                $userMessage = trim($postObj->Content);
                $userMessage = str_to_semiangle($userMessage);
                if($mpUser->getMpUserID() != $cwbMpUserID and !WxUserBusiness::isMember($wxUser))
                {
                    $content =  self::getFollowedContent($mpUser, $wxUser);
                    return self::getResponseContent($responseFromUser, $responseToUser, $content);
                }
                $content = self::processTextRequest($mpUser, $wxUser, $userMessage,$receiveMsgType);
            }
            elseif ($receiveMsgType == WeixinMessageType::VOICE)
            {
                $mediaID = $postObj->MediaId;
                //获取语音消息地址
                $userMessage = WxApiBusiness::getVoiceImgMessage($mpUser->getMpUserID(), $mediaID);
                if($mpUser->getMpUserID() != $cwbMpUserID and !WxUserBusiness::isMember($wxUser))
                {
                    $content =  self::getFollowedContent($mpUser, $wxUser);
                    return self::getResponseContent($responseFromUser, $responseToUser, $content);
                }
                $content = self::processTextRequest($mpUser, $wxUser, $userMessage,$receiveMsgType);
            }
            elseif ($receiveMsgType == WeixinMessageType::IMAGE)
            {
                $userMessage = $postObj->PicUrl;
                if($mpUser->getMpUserID() != $cwbMpUserID and !WxUserBusiness::isMember($wxUser))
                {
                    $content =  self::getFollowedContent($mpUser, $wxUser);
                    return self::getResponseContent($responseFromUser, $responseToUser, $content);
                }
                $content = self::processTextRequest($mpUser, $wxUser, $userMessage,$receiveMsgType);
            }
        }


        if (empty($content))
        {
            return null;
        }

        return self::getResponseContent($responseFromUser, $responseToUser, $content);
    }

    public static function processRequest(MpUser &$mpUser,$urlParams, $postStr)
    {
        $mpUserID = $mpUser->getMpUserID();
        log_info("WXAPI_INPUT [mpUserID:$mpUserID]" . $postStr);
        $result = null;

        if(!self::checkSignature($mpUser,$urlParams))
        {
            log_warning('check_signature_error. urlParams:'. var_export($urlParams,true));
            //return $result;
        }

        // 用户在公众平台填写后台url时会收到echostr的信息
        if(isset($urlParams['echostr']))
        {
            $result = $urlParams['echostr'];
            log_info("PRINT_WX_MP_ECHOSTR[mpUserID:$mpUserID]");
            return $result;
        }

        // 正常处理内容
        $result = self::matchRule($mpUser,$postStr);
        log_info("WXAPI_OUTPUT [mpUserID:$mpUserID]" . $result);

        return $result;
    }


    public static function checkSignature(MpUser &$mpUser, $urlParams)
    {
        $signature = $urlParams['signature'];
        $timestamp = $urlParams['timestamp'];
        $nonce = $urlParams['nonce'];


        $token = WxApiBusiness::getAccessToken($mpUser->getMpUserID());
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    public static function processSubscribeEvent(MpUser &$mpUser, WxUser &$wxUser)
    {
        if(!$wxUser->getIsFans())
        {
            $wxUser->setIsFans(1);
            $wxUser->save();
        }

        ReportBusiness::processSubscribeEvent($mpUser, $wxUser);

        if (WxUserBusiness::isMember($wxUser))
        {
            $content = sprintf("欢迎您再次关注%s。", $mpUser->getMpName());
        }
        else
        {
            $content = self::getFollowedContent($mpUser, $wxUser);

        }
        return $content;
    }

    public static function processUnSubscribeEvent(MpUser &$mpUser, WxUser &$wxUser)
    {
        if($wxUser->getIsFans())
        {
            $wxUser->setIsFans(0)->save();
        }
        ReportBusiness::processUnsubscribeEvent($mpUser, $wxUser);
    }

    public static function processClickEvent(MpUser &$mpUser, WxUser &$wxUser, $eventKey)
    {
        $mpUserID = $mpUser->getMpUserID();
        $mpUserType = $mpUser->getMpUserType();
        $wxUserID = $wxUser->getWxUserID();
        /*
        * 如果是当天第一条消息，则
        * 1. 如果当天是业主生日，则返回“Happy Birthday”
        * 2. 如果开启天气提醒，则返回天气预报（未实现）
        * 3. 如果有未读置顶通知，显示置顶通知
        */

        // 正常的菜单处理
        $menu = new WxSubMenu([WxSubMenu::MP_USER_ID => $mpUser->getMpUserID(),
                               WxSubMenu::WX_MENU_KEY => $eventKey,
                               WxSubMenu::WX_MENU_TYPE => WxMenuType::CLICK]);

        if($menu->isEmpty())
        {
            log_error("invalid weixin menu");
            return '无效的菜单，建议先取消关注再关注。';
        }

        $contentType  = $menu->getContentType();
        $contentValue = $menu->getContentValue();
        if($contentType == WxMenuContentType::CUSTOM_TEXT)
        {
            return  $contentValue;
        }
        elseif($contentType == WxMenuContentType::CUSTOM_NEWS)
        {
            $content['content_type'] = WeixinMessageType::NEWS;
            $contentValueArray = explode( ",", $contentValue );
            $paging            = null;
            $ranking           = [ MpRuleNewsItem::SORT_NO ];
            $condition         = [ MpRuleNewsItem::MP_RULE_NEWS_ITEM_ID => $contentValueArray];
            $news   = MpRuleNewsItemBusiness::getMpRuleNewsItemList( $condition, $paging, $ranking);


            foreach($news as $item)
            {
                $topDirNo = $item[MpRuleNewsItem::TOP_DIR_NO];
                if(!empty($topDirNo))
                {
                    $communityID = $wxUser->getCurrentCommunityID();
                    if(empty($communityID))
                    {
                        log_error("empty communityID.错误:无效的小区ID");
                        return "错误:无效的小区ID";
                    }
                    $topDir = new \MP\Model\Mp\TopDirectory([\MP\Model\Mp\TopDirectory::TOP_DIR_NO => $topDirNo,
                                                             \MP\Model\Mp\TopDirectory::COMMUNITY_ID => $communityID]);

                    if($topDir->isEmpty())
                    {
                        log_error("could not find TopDirectory.[topDirNo:$topDirNo][communityID:$communityID]");
                        return "错误:无效的菜单";
                    }
                    $data = DirectoryBusiness::getList( [ Directory::TOP_DIRECTORY_ID => $topDir->getTopDirectoryID() ],
                        $paging, $ranking );

                    if(count($data) == 1)
                    {
                        $contentUrl = DirectoryBusiness::getContent($data[0][Directory::COMMON_TYPE],$data[0][Directory::COMMON_CONTENT]);
                        if(preg_match("/\?/i", $data[0][Directory::COMMON_CONTENT]))
                        {
                           if($mpUserType == 1)
                           {
                               $contentUrl =  $contentUrl. '&mp_user_id='.$mpUserID;
                           }
                           else
                           {
                                $contentUrl =  $contentUrl. '&mp_user_id='.$mpUserID.'&wx_user_id='.$wxUserID;
                           }

                        }
                        else
                        {
                            if($mpUserType == 1)
                            {
                                $contentUrl = $contentUrl . '?mp_user_id=' . $mpUserID;
                            }
                            else
                            {
                                $contentUrl = $contentUrl . '?mp_user_id=' . $mpUserID.'&wx_user_id='.$wxUserID;
                            }
                        }

                        $item[MpRuleNewsItem::URL] = $contentUrl;
                    }
                    else
                    {
                        if($mpUserType == 1)
                        {
                            $item[MpRuleNewsItem::URL] = sprintf("%s/wx_user/directory/list?top_directory_id=%d&mp_user_id=%s", get_host(),$topDir->getTopDirectoryID(), $mpUserID);
                        }
                        else
                        {
                            $item[MpRuleNewsItem::URL] = sprintf("%s/wx_user/directory/list?top_directory_id=%d&mp_user_id=%s&wx_user_id=%s",get_host(),$topDir->getTopDirectoryID(), $mpUserID,$wxUserID);
                        }

                    }

                }
                elseif (strlen( $item[MpRuleNewsItem::URL] ) > 0)
                {
                    $pos = strpos($item[MpRuleNewsItem::URL], '?');
                    if ($pos === false)
                    {
                        if($mpUserType == 1)
                        {
                            $item[MpRuleNewsItem::URL] = $item[MpRuleNewsItem::URL] . '?mp_user_id=' . $mpUserID;
                        }
                        else
                        {
                            $item[MpRuleNewsItem::URL] = $item[MpRuleNewsItem::URL] . '?mp_user_id=' . $mpUserID.'&wx_user_id='.$wxUserID;
                        }

                    }
                    else
                    {
                        if($mpUserType == 1)
                        {
                            $item[MpRuleNewsItem::URL] = $item[MpRuleNewsItem::URL] . '&mp_user_id=' . $mpUserID;
                        }
                        else
                        {
                            $item[MpRuleNewsItem::URL] = $item[MpRuleNewsItem::URL] . '&mp_user_id=' . $mpUserID.'&wx_user_id='.$wxUserID;
                        }

                    }

                }
                $content['content'][] = $item;
            }
            //追加通知信息
            if($mpUserType == 1)
            {
                $notify = self::getNotifyInfo($mpUser,$wxUser);
                foreach($notify as $item)
                {
                    if(count($content['content']) >= 10)
                    {
                        break;
                    }
                    $content['content'][] = $item;
                }
            }

            return $content;
        }
        return null;
    }

    public static function processTextRequestForTest(MpUser &$mpUser, WxUser &$wxUser, $userMessage)
    {
        if($userMessage == '会员号')
        {
            return sprintf("会员号：%s", $wxUser->getVipNo());
        }

        if (strtolower($userMessage) == 'delete')
        {
            $wxUserID = $wxUser->getWxUserID();
           //$wxUser->delete();
            $wxUserDelete = new WxUser();
            $wxUserDelete->delete([WxUser::WX_USER_ID => $wxUserID]);
            $hs = new HouseMember();
            $hs->delete([HouseMember::WX_USER_ID => $wxUserID]);


            $ur = new  UrgentNoticeReadRecord();
            $ur->delete([UrgentNoticeReadRecord::WX_USER_ID => $wxUserID ]);

            $order = new \MP\Model\Mp\Order();
            $order->delete([\MP\Model\Mp\Order::WX_USER_ID => $wxUserID]);

            return "已删除当前帐号信息";
        }

            //测试用，重置认证结果，上线时去掉这个逻辑
        if (strtolower($userMessage) == 'reset')
        {
            $wxUser->setCurrentCommunityID(0)->setPhone(null)->setRegisterTime(null)->setIdentifyTime(null)->update();

            // 已认证用户标记为未认证用户
            $hs = new HouseMember();

            $hs->setWxUserID(null)->setCurrentCsID(null)->setCurrentCsGroupID(null)->setCsName(null)->setCsGroupName(null)->update([HouseMember::WX_USER_ID => $wxUser->getWxUserID()]);


            // 删除已读紧急通知记录
            $ur = new  UrgentNoticeReadRecord();
            $ur->delete([UrgentNoticeReadRecord::WX_USER_ID => $wxUser->getWxUserID() ]);


            // 删除用户信息
            //$wxUser->delete();

            return "reset ok";
        }

        return null;
    }



    public static function processTextRequest(MpUser &$mpUser, WxUser &$wxUser, $userMessage,$type)
    {
        // 查询公共帐号配置属性
        $mpUserID = $mpUser->getMpUserID();
        $cwbMpUserID = ConfigBusiness::getCwbMpUserID();
        //长微博公众账号客服沟通
        if($mpUserID == $cwbMpUserID or $mpUserID == '45829')
        {
            CsChatBusiness::processCsChatCwb($mpUser, $wxUser, $userMessage,$type);
        }
        elseif(ConfigBusiness::csAnswerEnabled($mpUserID))
        {
            CsChatBusiness::processCsChat($mpUser, $wxUser, $userMessage,$type);
        }
        return null;
    }


    public static function getFollowedContent(MpUser &$mpUser, WxUser &$wxUser)
    {
        $content = $mpUser->getFollowedContent();
        $content = str_replace("#HOST#", get_host(), $content);
        $content = str_replace("#MP_USER_ID#", $mpUser->getMpUserID(), $content);
        $content = str_replace("#VIP_NO#", $wxUser->getVipNo(), $content);
        if($mpUser->getMpUserType() == 0)
        {
            $content = str_replace("#WX_USER_ID#", $wxUser->getWxUserID(), $content);
        }

        return $content;
    }

    public static function generateVerifyPhoneCode()
    {
        $str         = '123456789';
        $maxStrIndex = strlen( $str ) - 1;
        $code       = '';
        $codeLen    = 3;

        for ($i = 0; $i < $codeLen; ++$i)
        {
            $r = rand( 0, $maxStrIndex );
            $code .= $str[$r];
        }

        return $code;
    }

    public static function getResponseContent($responseFromUser, $responseToUser, $msgContent)
    {
        if(is_array($msgContent))
        {
            $msgType = $msgContent['content_type'];
            $funcFlag = 0;
            if(isset($msgContent['func_flag']))
            {
                $funcFlag = $msgContent['func_flag'];
            }

            $content = $msgContent['content'];

            if($msgType == WeixinMessageType::TEXT)
            {
                return self::getResponseTextContent($responseFromUser, $responseToUser, $content, $funcFlag);
            }
            elseif($msgType == WeixinMessageType::NEWS)
            {
                return self::getResponseNewsContent($responseFromUser, $responseToUser, $content, $funcFlag);
            }
            else
            {
                log_error("wrong msg type.[msgType:$msgType]");
                return '';
            }
        }
        else
        {
            return self::getResponseTextContent($responseFromUser,$responseToUser,$msgContent);
        }
    }

    public static function getResponseTextContent($responseFromUser, $responseToUser, $msgContent, $funcFlag = 0)
    {

        $time = time();

        $text = $msgContent;
        $msgType = 'text';
        log_debug("response text:" . $text);
        $textTpl = "<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime><MsgType><![CDATA[%s]]></MsgType><Content><![CDATA[%s]]></Content><FuncFlag>%d</FuncFlag></xml>";
        $responseContent = sprintf($textTpl, $responseToUser, $responseFromUser, $time, $msgType, $text, $funcFlag);

        return $responseContent;
    }

    public static function getResponseNewsContent($responseFromUser, $responseToUser, $msgContent, $funcFlag = 0)
    {
        $time = time();
        $articleCount = count($msgContent);

        $responseContent = sprintf('<xml><ToUserName><![CDATA[%s]]></ToUserName><FromUserName><![CDATA[%s]]></FromUserName><CreateTime>%s</CreateTime><MsgType><![CDATA[news]]></MsgType><ArticleCount>%d</ArticleCount><Articles>',
            $responseToUser,$responseFromUser,$time, $articleCount);

        foreach($msgContent as $article)
        {
            $responseContent .= sprintf(" <item><Title><![CDATA[%s]]></Title> <Description><![CDATA[%s]]></Description><PicUrl><![CDATA[%s]]></PicUrl><Url><![CDATA[%s]]></Url></item>",
                $article[MpRuleNewsItem::TITLE], $article[MpRuleNewsItem::DESCRIPTION],
                $article[MpRuleNewsItem::PIC_URL], $article[MpRuleNewsItem::URL]);
        }

        $responseContent .=  "</Articles><FuncFlag>$funcFlag</FuncFlag></xml>";

        return $responseContent;
    }

    // 生日提醒
    public static function notifyBirthday(MpUser &$mpUser, WxUser &$wxUser)
    {
        $mpUserID = $mpUser->getMpUserID();
        $todayYmd = get_current_ymd();
        $content = [];
        $birthdayConfig = ConfigBusiness::mpUserConfig($mpUserID);
        if ( isset($birthdayConfig[MpUserConfigType::BIRTHDAY_TITLE]) and  $wxUser->getMessageDate() != $todayYmd)
        {
            $wxUser->setMessageDate($todayYmd)->update();
            if (substr($wxUser->getBirth(), -4) == date('md'))
            {
                 $content =
                    [
                        MpRuleNewsItem::TITLE  => $birthdayConfig[MpUserConfigType::BIRTHDAY_TITLE],
                        MpRuleNewsItem::PIC_URL     => $birthdayConfig[MpUserConfigType::BIRTHDAY_PIC_URL],
                        MpRuleNewsItem::URL         => $birthdayConfig[MpUserConfigType::BIRTHDAY_ARTICLE_URL],
                    ];
            }
        }
        return $content;
    }

    //紧急通知
    public static function notifyUrgent(MpUser &$mpUser, WxUser &$wxUser)
    {
        // 紧急通知
        $wxUserID = $wxUser->getWxUserID();
        $mpUserID = $mpUser->getMpUserID();
        $communityId = $wxUser ->getCurrentCommunityID();
        $content = null;

        $condition = [ChannelArticle::MP_USER_ID =>$mpUserID,ChannelArticle::KEEP_TOP =>'1',ChannelArticle::COMMUNITY_ID => $communityId ];
        $data = ChannelArticle::fetchRowsWithCount([ '*' ],$condition);

        if(empty($data))
        {
            return $content;
        }

        foreach($data as $k => $v)
        {
            $channelArticleID = $v[ChannelArticle::CHANNEL_ARTICLE_ID];
            $channelID = $v[ChannelArticle::CHANNEL_ID];
            $url=ChannelBusiness::getArticleUrl("wx_user/channel/ours",$mpUserID, $channelID, $communityId, $channelArticleID);

            $urgentNoticeReadRecord = new UrgentNoticeReadRecord(
                [
                    UrgentNoticeReadRecord::CHANNEL_ARTICLE_ID => $channelArticleID,
                    UrgentNoticeReadRecord::WX_USER_ID => $wxUserID
                ]);

            if ($urgentNoticeReadRecord->isEmpty())
            {
                $content[$k] = [MpRuleNewsItem::TITLE       => $v[ChannelArticle::ARTICLE_TITLE],
                                MpRuleNewsItem::PIC_URL     => $v[ChannelArticle::SHARE_URL],
                                MpRuleNewsItem::URL         => $url,];
            }

        }
        return $content;
    }

    //缴费通知单通知
    public static function notifyBill(MpUser &$mpUser, WxUser &$wxUser)
    {
        $wxUserID = $wxUser->getWxUserID();
        $mpUserID = $mpUser->getMpUserID();
        $content = [];
        $houseAddressArray =  HouseMember::fetchColumn(
            [HouseMember::HOUSE_ADDRESS],
            [
                HouseMember::WX_USER_ID => $wxUserID,
                HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID(),
                HouseMember::MEMBER_TYPE => HouseMemberType::OWNER,
            ]);
        foreach($houseAddressArray as $key => $value)
        {
            if(empty($value))
            {
                unset($houseAddressArray[$key]);
            }
        }
        //如果地址为空，返回空值
        if(empty($houseAddressArray))
        {
            return $content;
        }
        //获取未读缴费通知单
        $expr = "read_time is  null";
        $con =  new \Bluefin\Data\DbCondition($expr);
        $condition = [$con, Bill::HOUSE_ADDRESS => $houseAddressArray];
        $ranking = [Bill::BILL_DAY => true];
        $grouping = null;
        $bill = Bill::fetchRows( [ '*' ],$condition, $grouping, $ranking);
        //如果缴费通知单为空，返回空值
        if(empty($bill))
        {
            return $content;
        }
        foreach($bill as $key => $value)
        {
            $bill[$key]["year"] = substr($value['bill_day'],0,4);
            $bill[$key]["month"] = substr($value['bill_day'],4,2);
            $bill[$key]["day"]   = substr($value['bill_day'],6,2);
        }
        $host =  ConfigBusiness::getHost();

        foreach($bill as $value)
        {
            $billID = $value[Bill::BILL_ID];
            $picUrl = self::checkMonth($value["month"]);
            $url = $host."/wx_user/user_info/bill?mp_user_id=$mpUserID&bill_id=$billID";
            $content[] = [MpRuleNewsItem::TITLE  => $value["year"]."年".$value["month"]."月".$value["day"]."日" ."缴费通知单",
                          MpRuleNewsItem::PIC_URL => $picUrl,
                          MpRuleNewsItem::URL     => $url,];
        }
        return $content;

    }
    //核实月份选择图片
    public static function checkMonth($month)
    {
        $host =  ConfigBusiness::getHost();
        $picUrl = $host."/img/month/";
        switch ($month)
        {
            case "01":
                return $picUrl."01.png";
                break;
            case "02":
                return $picUrl."02.png";
                break;
            case "03":
                return $picUrl."03.png";
                break;
            case "04":
                return $picUrl."04.png";
                break;
            case "05":
                return $picUrl."05.png";
                break;
            case "06":
                return $picUrl."06.png";
                break;
            case "07":
                return $picUrl."07.png";
                break;
            case "08":
                return $picUrl."08.png";
                break;
            case "09":
                return $picUrl."09.png";
                break;
            case "10":
                return $picUrl."10.png";
                break;
            case "11":
                return $picUrl."11.png";
                break;
            case "12":
                return $picUrl."12.png";
                break;
            default:
                return "";
        }
    }

    public static function getNotifyInfo(MpUser &$mpUser, WxUser &$wxUser)
    {
        $content = [];

        $birthday = self::notifyBirthday($mpUser,$wxUser);
        if(!empty($birthday))
        {
            array_push($content,$birthday);
        }
        $urgent = self::notifyUrgent($mpUser,$wxUser);
        if(!empty($urgent))
        {
            $content = array_merge($content,$urgent);
        }

        $bill =  self::notifyBill($mpUser,$wxUser);
        if(!empty($bill))
        {
            $content = array_merge($content,$bill);
        }
        log_debug("=============================",$content);
        return $content;

    }

}
