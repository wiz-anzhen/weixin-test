<?php


use MP\Model\Mp\GroupSend;
use MP\Model\Mp\GroupSendItem;
use WBT\Business\Weixin\GroupSendBusiness;
use WBT\Business\Weixin\WxApiBusiness;
use MP\Model\Mp\WxUser;
use WBT\Business\UserBusiness;
use MP\Model\Mp\GroupSendRangeType;
use MP\Model\Mp\MpUser;

require_once 'MpUserServiceBase.php';
class GroupSendService extends MpUserServiceBase
{
    // 群发列表
    public function update()
    {
        $id   = $this->_app->request()->getQueryParam( GroupSend::GROUP_SEND_ID );
        $data = $this->_app->request()->getArray( [
            GroupSend::TITLE,
            GroupSend::CONTENT_TYPE,
            GroupSend::CONTENT_VALUE,
            GroupSend::GROUP_SEND_RANGE,
            GroupSend::GROUP_SEND_NO,
        ] );
        if($data[GroupSend::GROUP_SEND_RANGE] == GroupSendRangeType::SEND_BY_HOUSE_NO and $data[GroupSend::GROUP_SEND_NO] == "" )
        {
            return [ 'errno' => 1, 'error' => '请您填写指定房间编号' ];
        }
        $data[GroupSend::GROUP_SEND_NO]    = str_replace( '，', ',', $data[GroupSend::GROUP_SEND_NO]);
        return GroupSendBusiness::update( $id, $data );
    }

    public function insert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [
            GroupSend::TITLE,
            GroupSend::CONTENT_TYPE,
            GroupSend::CONTENT_VALUE,
            GroupSend::MP_USER_ID,
            GroupSend::COMMUNITY_ID,
            GroupSend::GROUP_SEND_RANGE,
            GroupSend::GROUP_SEND_NO,
            GroupSend::SEND_TYPE,
        ] );
        if($data[GroupSend::GROUP_SEND_RANGE] == GroupSendRangeType::SEND_BY_HOUSE_NO and $data[GroupSend::GROUP_SEND_NO] == "" )
        {
            return [ 'errno' => 1, 'error' => '请您填写请您填写指定房间编号' ];
        }
        $data[GroupSend::GROUP_SEND_NO]    = str_replace( '，', ',', $data[GroupSend::GROUP_SEND_NO]);
        return GroupSendBusiness::insert( $data );
    }

    public function delete()
    {
        $id = $this->_app->request()->get( GroupSend::GROUP_SEND_ID );
        return GroupSendBusiness::delete( $id );

    }

// 群发内容
    public function insertContent()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [
            GroupSendItem::TITLE,
            GroupSendItem::CONTENT,
            GroupSendItem::AUTHOR,
            GroupSendItem::PIC_URL,
            GroupSendItem::DESCRIPTION,
            GroupSendItem::CONTENT_SOURCE_URL,
            GroupSendItem::SORT_NO,
            GroupSendItem::MP_USER_ID,
            GroupSendItem::COMMUNITY_ID,
            GroupSendItem::GROUP_SEND_ID,
            GroupSendItem::SHOW_COVER_PIC
        ] );
        $file = explode(".com/",$data[GroupSendItem::PIC_URL]);
        $size =  filesize($file[1]);
        if($size > 1048576)
        {
            return [ 'errno' => 1, 'error' => "您上传的图片超过1M，请重新上传图片" ];
        }
        return GroupSendBusiness::insertContent( $data );
    }

    public function updateContent()
    {
        $id   = $this->_app->request()->getQueryParam( GroupSendItem::GROUP_SEND_ITEM_ID );
        $data = $this->_app->request()->getArray( [
            GroupSendItem::TITLE,
            GroupSendItem::CONTENT,
            GroupSendItem::AUTHOR,
            GroupSendItem::PIC_URL,
            GroupSendItem::DESCRIPTION,
            GroupSendItem::CONTENT_SOURCE_URL,
            GroupSendItem::SORT_NO,
            GroupSendItem::SHOW_COVER_PIC
        ] );
        $file = explode(".com/",$data[GroupSendItem::PIC_URL]);
        $size =  filesize($file[1]);
        if($size > 1048576)
        {
            return [ 'errno' => 1, 'error' => "您上传的图片超过1M，请重新上传图片" ];
        }
        return GroupSendBusiness::updateContent( $id, $data );
    }

    public function deleteContent()
    {
        $id = $this->_app->request()->get( GroupSendItem::GROUP_SEND_ITEM_ID );
        return GroupSendBusiness::deleteContent( $id );

    }

//复制消息
    public function copy()
    {
        $id = $this->_app->request()->get( GroupSend::GROUP_SEND_ID );
        log_debug("======================".$id);
        $from= $this->_app->request()->get( "from" );
        return GroupSendBusiness::copy( $id,$from );

    }

//群发消息过程

    public function send()
    {
        $id = $this->_app->request()->get( GroupSend::GROUP_SEND_ID );
        $mpUserID = $this->_app->request()->get( GroupSend::MP_USER_ID );
        $communityID = $this->_app->request()->get( GroupSend::COMMUNITY_ID );
        $groupSend = new GroupSend([GroupSend::GROUP_SEND_ID => $id]);
        //取出微信id
        $from= $this->_app->request()->get( "from" );
        if($from == "mp")
        {
            $wxUserID = WxUser::fetchColumn([WxUser::WX_USER_ID],[WxUser::MP_USER_ID => $mpUserID]);
        }
        else
        {
            $wxUserID =  GroupSendBusiness::getWxUserId($groupSend->getGroupSendRange(),$communityID,$groupSend->getGroupSendNo(),$mpUserID);
        }
        // 判断公众账号类型
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $mpUserType = $mpUser->getMpUserType();
        $accessToken = WxApiBusiness::getAccessToken($mpUserID);
        $contentType = $groupSend->getContentType();
        //服务号发送
        if($mpUserType)
        {
            $wxUserIDs = [];
            foreach($wxUserID as $value)
            {
                $wxUserIDs[] = '"'.$value.'"';
            }
            $wxUserIDs = implode(",",$wxUserIDs);
            if($contentType == "custom_text")
            {
                $contentValue = $groupSend->getContentValue();
                if(empty($contentValue))
                {
                    return [ 'errno' => 1, 'error' => '请先编辑消息内容，再发布' ];
                }
                else
                {
                    $article = "
                          {
                            \"touser\":[
                                $wxUserIDs
                                        ],
                             \"msgtype\":\"text\",
                             \"text\":{\"content\":\"$contentValue\"}
                          }
                         ";
                    $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$accessToken";
                    $res = _curl_post($url, $article);
                    $res = (array)json_decode($res);
                    log_debug("000000000000000999999999",$res);
                    $groupSend->setMsgID($res["msg_id"])->setStatus($res["errmsg"])->update();
                    if($res["errcode"] != 0)
                    {
                        return [ 'errno' => 1, 'error' => '发布失败' ];
                    }
                }

            }
            else
            {
                $groupSendItem = GroupSendItem::fetchRows(['*'],[GroupSendItem::GROUP_SEND_ID => $id],$grouping = null, $ranking = [GroupSendItem::SORT_NO], $pagination = null, $outputColumns = null, $withDeleted = false);
                if(empty($groupSendItem))
                {
                    return [ 'errno' => 1, 'error' => '请先编辑多图文消息，再发布' ];
                }
                else
                {
                    $type = "image";
                    $mediaID = [];
                    foreach($groupSendItem as $item)
                    {
                        $filePath = $item[GroupSendItem::PIC_URL];
                        $filePath = explode(".com/",$filePath);
                        $fileData = array("media" => "@".$filePath[1]);
                        log_debug("000000000000000",$filePath);
                        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$accessToken&type=$type";
                        $res = _curl_post($url, $fileData);
                        $res = (array)json_decode($res);
                        log_debug("000000000000000999999999",$res);
                        $mediaID[$item[GroupSendItem::SORT_NO]] = $res["media_id"];
                    }
                    $articles = [];
                    foreach($groupSendItem as $item)
                    {
                        $mId = $mediaID[$item[GroupSendItem::SORT_NO]];
                        $author = $item[GroupSendItem::AUTHOR];
                        $title=$item[GroupSendItem::TITLE];
                        $content=$item[GroupSendItem::CONTENT];
                        $content = preg_replace("/\"/","\\\"",$content);
                        $contentUrl=$item[GroupSendItem::CONTENT_SOURCE_URL];

                        $digest=$item[GroupSendItem::DESCRIPTION];
                        $showCoverPic=$item[GroupSendItem::SHOW_COVER_PIC];
                        $articles[] = "{
                                      \"thumb_media_id\":\"$mId\",
                                      \"author\":\"$author\",
			                          \"title\":\"$title\",
			                          \"content_source_url\":\"$contentUrl\",
			                          \"content\":\"$content\",
			                          \"digest\":\"$digest\",
			                          \"show_cover_pic\":\"$showCoverPic\"
		                             }";
                    }

                    $articles = implode(",",$articles);
                    log_debug("===============================",$articles);
                    $article = "
                            {
                                \"articles\":
                                  [
		                             $articles
                                   ]
                            }";
                    log_debug("=========================================",$article);
                    $url = "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=$accessToken";
                    $res = _curl_post($url, $article);
                    $res = (array)json_decode($res);
                    log_debug("article===========================",$res);
                    $mId = $res["media_id"];
                    $article = "
                           {
                               \"touser\":[
                                           $wxUserIDs
                                           ],
                               \"mpnews\":{
                                          \"media_id\":\"$mId\"
                                          },
                               \"msgtype\":\"mpnews\"
                            }
                             ";
                    $url = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$accessToken";
                    $res = _curl_post($url, $article);
                    $res = (array)json_decode($res);
                    log_debug("000000000000000999999999",$res);
                    log_debug("=========================================",$article);
                    $groupSend->setMsgID($res["msg_id"])->setStatus($res["errmsg"])->update();
                    if($res["errcode"] != 0)
                    {
                        return [ 'errno' => 1, 'error' => '发布失败' ];
                    }
                }

            }
        }
        //订阅号群发
        if(!$mpUserType)
        {
            if($contentType == "custom_text")
            {
                $contentValue = $groupSend->getContentValue();
                if(empty($contentValue))
                {
                    return [ 'errno' => 1, 'error' => '请先编辑消息内容，再发布' ];
                }
                else
                {
                    $article = array("filter" => array("is_to_all" => true),"text" => array("content" => $contentValue),"msgtype" => "text");
                    $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=$accessToken";
                    $article = urldecode(json_encode($article, JSON_UNESCAPED_UNICODE));
                    $res = _curl_post($url, $article);
                    $res = (array)json_decode($res);
                    log_debug("000000000000000999999999",$res);
                    $groupSend->setMsgID($res["msg_id"])->setStatus($res["errmsg"])->update();
                    if($res["errcode"] != 0)
                    {
                        return [ 'errno' => 1, 'error' => '发布失败' ];
                    }
                }

            }
            else
            {
                $groupSendItem = GroupSendItem::fetchRows(['*'],[GroupSendItem::GROUP_SEND_ID => $id],$grouping = null, $ranking = [GroupSendItem::SORT_NO], $pagination = null, $outputColumns = null, $withDeleted = false);
                if(empty($groupSendItem))
                {
                    return [ 'errno' => 1, 'error' => '请先编辑多图文消息，再发布' ];
                }
                else
                {
                    $type = "image";
                    $mediaID = [];
                    foreach($groupSendItem as $item)
                    {
                        $filePath = $item[GroupSendItem::PIC_URL];
                        $filePath = explode(".com/",$filePath);
                        $fileData = array("media" => "@".$filePath[1]);
                        log_debug("000000000000000",$filePath);
                        $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$accessToken&type=$type";
                        $res = _curl_post($url, $fileData);
                        $res = (array)json_decode($res);
                        log_debug("000000000000000999999999",$res);
                        $mediaID[$item[GroupSendItem::SORT_NO]] = $res["media_id"];
                    }
                    $articles = [];
                    foreach($groupSendItem as $item)
                    {
                        $mId = $mediaID[$item[GroupSendItem::SORT_NO]];
                        $author = $item[GroupSendItem::AUTHOR];
                        $title=$item[GroupSendItem::TITLE];
                        $content=$item[GroupSendItem::CONTENT];
                        $content = preg_replace("/\"/","\\\"",$content);
                        $contentUrl=$item[GroupSendItem::CONTENT_SOURCE_URL];

                        $digest=$item[GroupSendItem::DESCRIPTION];
                        $showCoverPic=$item[GroupSendItem::SHOW_COVER_PIC];
                        $articles[] = "{
                                      \"thumb_media_id\":\"$mId\",
                                      \"author\":\"$author\",
			                          \"title\":\"$title\",
			                          \"content_source_url\":\"$contentUrl\",
			                          \"content\":\"$content\",
			                          \"digest\":\"$digest\",
			                          \"show_cover_pic\":\"$showCoverPic\"
		                             }";
                    }

                    $articles = implode(",",$articles);
                    log_debug("===============================",$articles);
                    $article = "
                            {
                                \"articles\":
                                  [
		                             $articles
                                   ]
                            }";
                    log_debug("=========================================",$article);
                    $url = "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=$accessToken";
                    $res = _curl_post($url, $article);
                    $res = (array)json_decode($res);
                    log_debug("article===========================",$res);
                    $mId = $res["media_id"];

                    $article = array("filter" => array("is_to_all" => true),"mpnews" => array("media_id" => $mId),"msgtype" => "text");
                    $url = "https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=$accessToken";
                    $article = urldecode(json_encode($article, JSON_UNESCAPED_UNICODE));

                    $res = _curl_post($url, $article);
                    $res = (array)json_decode($res);
                    log_debug("000000000000000999999999",$res);
                    log_debug("=========================================",$article);
                    $groupSend->setMsgID($res["msg_id"])->setStatus($res["errmsg"])->update();
                    if($res["errcode"] != 0)
                    {
                        return [ 'errno' => 1, 'error' => '发布失败' ];
                    }
                }

            }
        }

        $data[GroupSend::GROUP_SEND_TIME] = time();
        $data[ GroupSend::GROUP_SEND_AUTHOR] = UserBusiness::getLoginUsername();
        GroupSendBusiness::update( $id, $data );
    }

    // 预览消息
    public function preview()
    {
        $id   = $this->_app->request()->getQueryParam( GroupSend::GROUP_SEND_ID );
        //取出会员号微信id
        $vipNo   = $this->_app->request()->get("vip_no" );
        $wxUser = new WxUser([WxUser::VIP_NO => $vipNo]);
        $wxUserID = $wxUser->getWxUserID();
        if($wxUser->isEmpty())
        {
            return [ 'errno' => 1, 'error' => '请确认您填写的会员号' ];
        }

        $groupSend = new GroupSend([GroupSend::GROUP_SEND_ID => $id]);
        $mpUserID = $groupSend->getMpUserID();
        $communityID = $groupSend->getCommunityID();

        $accessToken = WxApiBusiness::getAccessToken($mpUserID);
        $contentType = $groupSend->getContentType();

        if($contentType == "custom_text")
        {
            $contentValue = $groupSend->getContentValue();
            if(empty($contentValue))
            {
                return [ 'errno' => 1, 'error' => '请先编辑消息内容，再发布' ];
            }
            else
            {
                $article = "
                          {
                            \"touser\":\"$wxUserID\",
                             \"msgtype\":\"text\",
                             \"text\":{\"content\":\"$contentValue\"}
                          }
                         ";
                $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=$accessToken";
                $res = _curl_post($url, $article);
                $res = (array)json_decode($res);
                log_debug("000000000000000999999999",$res);
                if($res["errcode"] != 0)
                {
                    return [ 'errno' => 1, 'error' => '发布失败' ];
                }
            }

        }
        else
        {
            $groupSendItem = GroupSendItem::fetchRows(['*'],[GroupSendItem::GROUP_SEND_ID => $id],$grouping = null, $ranking = [GroupSendItem::SORT_NO], $pagination = null, $outputColumns = null, $withDeleted = false);
            if(empty($groupSendItem))
            {
                return [ 'errno' => 1, 'error' => '请先编辑多图文消息，再发布' ];
            }
            else
            {
                $type = "image";
                $mediaID = [];
                foreach($groupSendItem as $item)
                {
                    $filePath = $item[GroupSendItem::PIC_URL];
                    $filePath = explode(".com/",$filePath);
                    $fileData = array("media" => "@".$filePath[1]);
                    log_debug("000000000000000",$filePath);
                    $url = "http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token=$accessToken&type=$type";
                    $res = _curl_post($url, $fileData);
                    $res = (array)json_decode($res);
                    log_debug("000000000000000999999999",$res);
                    $mediaID[$item[GroupSendItem::SORT_NO]] = $res["media_id"];
                }
                $articles = [];
                foreach($groupSendItem as $item)
                {
                    $mId = $mediaID[$item[GroupSendItem::SORT_NO]];
                    $author = $item[GroupSendItem::AUTHOR];
                    $title=$item[GroupSendItem::TITLE];
                    $content=$item[GroupSendItem::CONTENT];
                    $content = preg_replace("/\"/","\\\"",$content);
                    $contentUrl=$item[GroupSendItem::CONTENT_SOURCE_URL];

                    $digest=$item[GroupSendItem::DESCRIPTION];
                    $showCoverPic=$item[GroupSendItem::SHOW_COVER_PIC];
                    $articles[] = "{
                                      \"thumb_media_id\":\"$mId\",
                                      \"author\":\"$author\",
			                          \"title\":\"$title\",
			                          \"content_source_url\":\"$contentUrl\",
			                          \"content\":\"$content\",
			                          \"digest\":\"$digest\",
			                          \"show_cover_pic\":\"$showCoverPic\"
		                             }";
                }

                $articles = implode(",",$articles);
                log_debug("===============================",$articles);
                $article = "
                            {
                                \"articles\":
                                  [
		                             $articles
                                   ]
                            }";
                log_debug("=========================================",$article);
                $url = "https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=$accessToken";
                $res = _curl_post($url, $article);
                $res = (array)json_decode($res);
                log_debug("article===========================",$res);
                $mId = $res["media_id"];
                $article = "
                           {
                               \"touser\":\"$wxUserID\",
                               \"mpnews\":{
                                          \"media_id\":\"$mId\"
                                          },
                               \"msgtype\":\"mpnews\"
                            }
                             ";
                $url = "https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=$accessToken";
                $res = _curl_post($url, $article);
                $res = (array)json_decode($res);
                log_debug("000000000000000999999999",$res);
                log_debug("=========================================",$article);
                if($res["errcode"] != 0)
                {
                    return [ 'errno' => 1, 'error' => '发布失败' ];
                }
            }

        }

        return [ 'errno' => 0];
    }
}