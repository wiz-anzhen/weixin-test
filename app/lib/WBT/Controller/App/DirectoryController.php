<?php

namespace WBT\Controller\App;

use Common\Helper\BaseController;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\Directory;
use MP\Model\Mp\DirectorySmallFlowType;
use MP\Model\Mp\DirectoryType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\TopDirectory;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\DirectoryBusiness;
use MP\Model\Mp\AppUser;

class DirectoryController extends BaseController
{
    public function listAction()
    {
        $mpUserID = $this->_request->get('mp_user_id');
        $topDirectoryId = $this->_request->get( Directory::TOP_DIRECTORY_ID );
        $phone = $this->_request->get('phone');
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if(!$appUser->isEmpty())
        {
            $appUser->setLastAccess(date('Y-m-d H:i:s',time()))->update();
        }
       /*
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $houseMember = new HouseMember([HouseMember::WX_USER_ID => $this->_wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $houseMemberType = $houseMember->getMemberType();
        $this->_view->set('member_type', $houseMemberType);
*/
        $topDirectory = new TopDirectory([TopDirectory::TOP_DIRECTORY_ID => $topDirectoryId]);
        $viewType = $topDirectory->getUrlType();
            $this->_view->set('top_name', $topDirectory->getTitle());
        $this->_view->set('directory_background_img', $topDirectory->getDirectoryBackgroundImg());
        $this->_view->set('directory_top_img', $topDirectory->getDirectoryTopImg());
        $this->_view->set('directory_top_img_second', $topDirectory->getDirectoryTopImgSecond());
        $this->_view->set('directory_top_img_third', $topDirectory->getDirectoryTopImgThird());

        $paging         = [ ];
        $ranking        = [ Directory::SORT_NO ];
        $data = DirectoryBusiness::getList( [ Directory::TOP_DIRECTORY_ID => $topDirectoryId ],
            $paging, $ranking );

        /*
         * 计算业主未读缴费通知单

        $houseAddressArray =  HouseMember::fetchColumn(
            [HouseMember::HOUSE_ADDRESS],
            [
                HouseMember::WX_USER_ID => $this->_wxUserID,
                HouseMember::COMMUNITY_ID => $this->_communityID,
                HouseMember::MEMBER_TYPE => HouseMemberType::OWNER,
            ]);
        foreach($houseAddressArray as $key => $value)
        {
            if(empty($value))
            {
                unset($houseAddressArray[$key]);
            }
        }
        if(!empty($houseAddressArray))
        {
            //计算阅读数量
            $expr = "read_time is  null";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition = [$con, Bill::HOUSE_ADDRESS => $houseAddressArray];
            $count = Bill::fetchCount($condition);
            if(!empty($count))
            {
                $this->_view->set('bill_count', $count);
            }
        }

        $houseNo = HouseMember::fetchColumn(HouseMember::HOUSE_NO,[WxUser::WX_USER_ID => $this->_wxUserID,HouseMember::COMMUNITY_ID => $data[0]['community_id']]);
 * */
        /*
        if (!$wxUser->isEmpty())
        {*/
            //判断公众账号属性
            $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
            $mpUserType = $mpUser->getMpUserType();
            foreach($data as $key => $value)
            {
                if($value[Directory::COMMON_TYPE] != "text")
                {
                    $content = DirectoryBusiness::getContent($value[Directory::COMMON_TYPE],$value[Directory::COMMON_CONTENT]);
                    if(preg_match("/\?/i", $value[Directory::COMMON_CONTENT]))
                    {
                        if(strpos($content,'weibotui'))
                        {
                            $value[Directory::COMMON_CONTENT] =  $content. '&mp_user_id='.$mpUserID.'&phone='.$phone;
                        }
                        else
                        {
                            $value[Directory::COMMON_CONTENT] =  $content;
                        }
                    }
                    else
                    {
                        if(strpos($content,'weibotui'))
                        {
                            $value[Directory::COMMON_CONTENT] =  $content. '?mp_user_id='.$mpUserID.'&phone='.$phone;
                        }
                        else
                        {
                            $value[Directory::COMMON_CONTENT] =  $content;
                        }
                    }
                }

                $smallContent = DirectoryBusiness::getContent($value[Directory::SMALL_FLOW_TYPE],$value[Directory::SMALL_FLOW_CONTENT]);
                if(preg_match("/\?/i", $value[Directory::SMALL_FLOW_CONTENT]))
                {
                    if($mpUserType == 1)
                    {
                        $value[Directory::SMALL_FLOW_CONTENT]= $smallContent .'&mp_user_id='.$mpUserID.'&phone='.$phone;
                    }
                    else
                    {
                        $value[Directory::SMALL_FLOW_CONTENT]= $smallContent .'&mp_user_id='.$mpUserID.'&phone='.$phone;
                    }

                }
                else
                {
                    if($mpUserType == 1)
                {
                    $value[Directory::SMALL_FLOW_CONTENT] = $smallContent. '?mp_user_id='. $mpUserID.'&phone='.$phone;
                }
                else
                {
                    $value[Directory::SMALL_FLOW_CONTENT] = $smallContent. '?mp_user_id='. $mpUserID.'&phone='.$phone;
                }

                }

                if($value[Directory::SHOW_SMALL_FLOW])
                {
                    //小流量数据限制房间编号
                    $isSmallFlow = false;//判断用户房间编号是否在可用范围内
                    $smallFlowNo = explode("\n",$value[Directory::SMALL_FLOW_NO]);
                    foreach($smallFlowNo as $no)
                    {
                        if(!empty($no))
                        {
                            $smallFlowNo = explode(",",$no);
                            $smallFlowNoStart = $smallFlowNo[0];
                            $smallFlowNoEnd = $smallFlowNo[1];
                            //判断用户房间编号是否在可用范围
                            $houseNo = [];
                            foreach($houseNo as $hNo)
                            {
                                //核对位数补0操作
                                $ret = DirectoryBusiness::checkLength($smallFlowNoStart,$smallFlowNoEnd,$hNo);
                                $smallFlowNoStart = $ret[0];
                                $smallFlowNoEnd = $ret[1];
                                $hNo = $ret[2];
                                //如果是小流量使用小流量类型及内容覆盖普通目录类型及内容
                                if( $smallFlowNoStart <= $hNo and $hNo <= $smallFlowNoEnd )
                                {
                                    $isSmallFlow = true;
                                    $value[Directory::COMMON_TYPE] = $value[Directory::SMALL_FLOW_TYPE];
                                    $value[Directory::COMMON_CONTENT] = $value[Directory::SMALL_FLOW_CONTENT] ;
                                    break;

                                }
                            }
                            if($isSmallFlow)
                            {
                                break;
                            }
                        }
                    }
                }
                $data[$key] = $value;
            }
        /*
        }*/
        $wxUserID = '';
        $cs = new CustomerSpecialist([CustomerSpecialist::WX_USER_ID => $wxUserID,CustomerSpecialist::VALID => "1"]);
        if(!$cs->isEmpty())
        {
            $this->_view->set( 'cs', true);
        }
        
        foreach($data as $key => $value)
        {
            if(strpos($value[Directory::COMMON_CONTENT],'weibotui'))
            {
                $data[$key][Directory::COMMON_CONTENT] = str_replace("wx_user","app",$value[Directory::COMMON_CONTENT]);
            }

        }
        log_debug("1111111111111111111111111111111",$data);
        $this->_view->set( 'directories', $data);
        //判断访问页面
        if($viewType != "none" and !empty($viewType))
        {
            $url = "WBT/App/Directory.".$viewType.".html";
            $this->changeView($url);
        }


    }

}
