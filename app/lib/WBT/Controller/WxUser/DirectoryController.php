<?php

namespace WBT\Controller\WxUser;

use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\Directory;
use MP\Model\Mp\Community;
use MP\Model\Mp\DirectorySmallFlowType;
use MP\Model\Mp\DirectoryType;
use MP\Model\Mp\IndustryType;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\TopDirectory;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\ApiBusiness;
use WBT\Business\Weixin\DirectoryBusiness;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\Bill;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\DirectoryPowerType;
use WBT\Business\Weixin\WxUserBusiness;

class DirectoryController extends WxUserControllerBase
{
    public function listAction()
    {
        $mpUserID = $this->_request->get('mp_user_id');
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $industry = $mpUser->getIndustry();
        $topDirectoryId = $this->_request->get( Directory::TOP_DIRECTORY_ID );
        $this->_view->set('top_directory_id', $topDirectoryId);

        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        $community = new Community([Community::COMMUNITY_ID => $communityID]);;
        $communityType = $community->getCommunityType();
        $houseMember = new HouseMember([HouseMember::WX_USER_ID => $this->_wxUserID,HouseMember::COMMUNITY_ID => $wxUser->getCurrentCommunityID()]);
        $houseMemberType = $houseMember->getMemberType();
        $this->_view->set('member_type', $houseMemberType);

        $topDirectory = new TopDirectory([TopDirectory::TOP_DIRECTORY_ID => $topDirectoryId]);

        if($topDirectory->getPowerType() == DirectoryPowerType::REGISTER )
        {
            if($wxUser->getPhone() == "")
            {
                $url = sprintf('/wx_user/user_info/register?mp_user_id=%s', $mpUserID);
                $this->_gateway->redirect($url);
            }
        }

        if($topDirectory->getPowerType() == DirectoryPowerType::IDENTIFY )
        {
            if(!WxUserBusiness::isMember($wxUser))
            {
                $url = sprintf('/wx_user/user_info/index?mp_user_id=%s', $mpUserID);
                $this->_gateway->redirect($url);

            }
        }
        if($topDirectory->getPowerType() == DirectoryPowerType::OTHER )
        {
            if($wxUser->getPhone() == "")
            {
                $url = sprintf('/wx_user/user_info/other?mp_user_id=%s', $mpUserID);
                $this->_gateway->redirect($url);

            }
        }



        $viewType = $topDirectory->getUrlType();
        $this->_view->set('top_name', $topDirectory->getTitle());
        $this->_view->set('directory_background_img', $topDirectory->getDirectoryBackgroundImg());
        $this->_view->set('directory_top_img', $topDirectory->getDirectoryTopImg());
        $this->_view->set('directory_top_img_second', $topDirectory->getDirectoryTopImgSecond());
        $this->_view->set('directory_top_img_third', $topDirectory->getDirectoryTopImgThird());

        if($industry == IndustryType::PROCUREMENT )
        {
            $this->_view->set('community_type', $communityType);
            $url = "WBT/WxUser/Directory.procurement.html";
            $this->changeView($url);
            return;
        }

        $paging         = [ ];
        $ranking        = [ Directory::SORT_NO ];
        $data = DirectoryBusiness::getList( [ Directory::TOP_DIRECTORY_ID => $topDirectoryId ],
            $paging, $ranking );
        log_debug('data================',$data);
        if(count($data) == 1)
        {
            $content = DirectoryBusiness::getContent($data[0][Directory::COMMON_TYPE],$data[0][Directory::COMMON_CONTENT]);
            $data[0][Directory::COMMON_CONTENT] =  $content. '&mp_user_id='.$mpUserID. '&power_type='.$data[0][Directory::POWER_TYPE];
            $this->_gateway->redirect($data[0][Directory::COMMON_CONTENT]);
        }
        else
        {
            /*
             * 计算业主未读缴费通知单
             * */
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

            if (!$wxUser->isEmpty())
            {
                //判断公众账号属性
                $mpUserType = $mpUser->getMpUserType();
                foreach($data as $key => $value)
                {
                    if($value[Directory::COMMON_TYPE] != "text")
                    {
                        $content = DirectoryBusiness::getContent($value[Directory::COMMON_TYPE],$value[Directory::COMMON_CONTENT]);
                        log_debug('content===============',$content);
                        if(preg_match("/\?/i", $value[Directory::COMMON_CONTENT]))
                        {
                           if($mpUserType == 1)
                           {
                               if($industry == IndustryType::PROCUREMENT)
                               {
                                   $value[Directory::COMMON_CONTENT] =  $content. '&mp_user_id='.$mpUserID. '&power_type='.$value[Directory::POWER_TYPE].'&top_directory_id='.$topDirectoryId;
                               }
                               else
                               {
                                   $value[Directory::COMMON_CONTENT] =  $content. '&mp_user_id='.$mpUserID. '&power_type='.$value[Directory::POWER_TYPE];
                               }

                           }
                            else
                            {
                                $value[Directory::COMMON_CONTENT] =  $content. '&mp_user_id='.$mpUserID.'&wx_user_id='.$wxUserID. '&power_type='.$value[Directory::POWER_TYPE];
                            }

                        }
                        else
                        {
                            if($mpUserType == 1)
                            {
                                if($industry == IndustryType::PROCUREMENT)
                                {
                                    $value[Directory::COMMON_CONTENT] = $content . '?mp_user_id=' . $mpUserID. '&power_type='.$value[Directory::POWER_TYPE].'&top_directory_id='.$topDirectoryId;
                                }
                                else
                                {
                                    $value[Directory::COMMON_CONTENT] = $content . '?mp_user_id=' . $mpUserID. '&power_type='.$value[Directory::POWER_TYPE];
                                }

                            }
                            else
                            {
                                $value[Directory::COMMON_CONTENT] = $content . '?mp_user_id=' . $mpUserID.'&wx_user_id='.$wxUserID. '&power_type='.$value[Directory::POWER_TYPE];
                            }

                        }
                    }

                    $smallContent = DirectoryBusiness::getContent($value[Directory::SMALL_FLOW_TYPE],$value[Directory::SMALL_FLOW_CONTENT]);
                    if(preg_match("/\?/i", $value[Directory::SMALL_FLOW_CONTENT]))
                    {
                        if($mpUserType == 1)
                        {
                            if($industry == IndustryType::PROCUREMENT)
                            {
                                $value[Directory::SMALL_FLOW_CONTENT]= $smallContent .'&mp_user_id='.$mpUserID. '&power_type='.$value[Directory::POWER_TYPE].'&top_directory_id='.$topDirectoryId;
                            }
                            else
                            {
                                $value[Directory::SMALL_FLOW_CONTENT]= $smallContent .'&mp_user_id='.$mpUserID. '&power_type='.$value[Directory::POWER_TYPE];
                            }

                        }
                        else
                        {
                            $value[Directory::SMALL_FLOW_CONTENT]= $smallContent .'&mp_user_id='.$mpUserID.'&wx_user_id='.$wxUserID. '&power_type='.$value[Directory::POWER_TYPE];
                        }

                    }
                    else
                    {
                        if($mpUserType == 1)
                        {
                            if($industry == IndustryType::PROCUREMENT)
                            {
                                $value[Directory::SMALL_FLOW_CONTENT] = $smallContent. '?mp_user_id='. $mpUserID. '&power_type='.$value[Directory::POWER_TYPE].'&top_directory_id='.$topDirectoryId;
                            }
                            else
                            {
                                $value[Directory::SMALL_FLOW_CONTENT] = $smallContent. '?mp_user_id='. $mpUserID. '&power_type='.$value[Directory::POWER_TYPE];
                            }

                        }
                        else
                        {
                            $value[Directory::SMALL_FLOW_CONTENT] = $smallContent. '?mp_user_id='. $mpUserID.'&wx_user_id='.$wxUserID. '&power_type='.$value[Directory::POWER_TYPE];
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
            }
        }
        $cs = new CustomerSpecialist([CustomerSpecialist::WX_USER_ID => $wxUserID,CustomerSpecialist::VALID => "1"]);
        if(!$cs->isEmpty())
        {
            $this->_view->set( 'cs', true);
        }
        $this->_view->set( 'directories', $data);
        //判断访问页面
        if($viewType != "none" and !empty($viewType))
        {
            $url = "WBT/WxUser/Directory.".$viewType.".html";
            $this->changeView($url);
        }


    }

}
