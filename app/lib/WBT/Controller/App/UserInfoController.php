<?php

namespace WBT\Controller\App;

use Common\Helper\BaseController;
use MP\Model\Mp\AppUser;
use MP\Model\Mp\Bill;
use MP\Model\Mp\BillDetail;
use MP\Model\Mp\BillPayMethod;
use MP\Model\Mp\ChannelArticle;
use MP\Model\Mp\Community;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\BillBusiness;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\HouseMemberType;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\MpUserConfigType;
class UserInfoController extends BaseController
{
    // 输入信息，绑定会员
    public function indexAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $currentCommunity = $wxUser->getCurrentCommunityID();
        $this->_view->set('current_community', $currentCommunity);

        $condition = [Community::MP_USER_ID => $this->_mpUserID,Community::IS_VIRTUAL => '0'];
        $community = Community::fetchRows( [ '*' ], $condition );
        $this->_view->set('community', $community);
    }

    public function verifyCodeAction()
    {
        $phone    = $this->_request->get( 'phone' );
        $this->_view->set( 'phone', $phone );
    }

    public function successAction()
    {

    }

    public function datePlannerAction()
    {

    }


    public function feeListAction()
    {
        $article = new ChannelArticle([ChannelArticle::CHANNEL_ID => '8']);
        $this->_view->set('article',$article);
    }


    public function billAction()
    {
         $billID = $this->_request->get( 'bill_id' );
         $bill = new Bill([Bill::BILL_ID => $billID]);
         $this->_view->set('house_name', $bill->getName());
         $this->_view->set('house_address', $bill->getHouseAddress());
         $this->_view->set('house_area', $bill->getHouseArea());
         $this->_view->set('total_payment', $bill->getTotalPayment());
         $this->_view->set('pay_finished', $bill->getPayFinished());
         $this->_view->set('bill_id', $billID);
         $payType = ConfigBusiness::mpUserConfig($bill->getMpUserID());
         $payType = $payType[MpUserConfigType::WX_PAY];
         if(!empty($payType))
         {
            $this->_view->set("pay_type",$payType);
         }
         $billDetail = BillDetail::fetchRows(['*'],[BillDetail::BILL_ID => $billID]);
         $this->_view->set('bill_detail', $billDetail);
         $communityID = $bill->getCommunityID();
         $community = new Community([Community::COMMUNITY_ID => $communityID]);
         $billComment = $community->getBillComment();
         $this->_view->set('bill_comment', $billComment);
         $this->_view->set('bill_name', $community->getBillName());

         $billYmd = $bill->getBillDay();
         $billYear = substr($billYmd,0,4);
         $billMonth = substr($billYmd,4,2);
         $billDay = substr($billYmd,6,2);
         $this->_view->set('bill_day', sprintf("%s-%s-%s",$billYear, $billMonth, $billDay));
         BillBusiness::checkRead($billID);

    }

    public function billListAction()
    {
        $this->_view->set('mmdd_today', date('Ymd'));
        $this->_view->set('mmdd_yesterday', date('Ymd', strtotime("-1 day")));
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        if(!empty($this->_wxUserID))
        {
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
                $ranking = [Bill::BILL_DAY => true];
                $grouping = null;
                $bill = Bill::fetchRows( [ '*' ], [Bill::HOUSE_ADDRESS => $houseAddressArray], $grouping, $ranking);
                foreach($bill as $key => $value)
                {
                    $bill[$key]["month"] = substr($value['bill_day'],4,2);
                    $bill[$key]["day"]   = substr($value['bill_day'],6,2);


                }
                $this->_view->set('bill_list', $bill);

            }

        }

    }

    public function hotLineAction()
    {

    }

    //登陆
    public function loginAction()
    {
        $phone    = $this->_request->get( 'phone' );
        $mpUserID    = $this->_request->get( 'mp_user_id' );
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $currentCommunity = $appUser->getCurrentCommunityID();
        $this->_view->set('current_community', $currentCommunity);
        $this->_view->set('phone', $phone);
        $condition = [Community::MP_USER_ID => $this->_mpUserID,Community::IS_VIRTUAL => '0'];
        $community = Community::fetchRows( [ '*' ], $condition );
        $this->_view->set('community', $community);
        $this->_view->set('head_pic', $appUser->getHeadPic());
        $this->_view->set('nick', $appUser->getNick());
        $host = ConfigBusiness::getHost();
        if ($appUser->getIsQuit() == 0)
        {
            $this->_gateway->redirect( $host."/app/user_info/login_success?mp_user_id=".$mpUserID."&phone=".$phone);
        }
    }

    public function loginSuccessAction()
    {
        $phone = $this->_request->get( 'phone' );
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $this->_view->set('head_pic', $appUser->getHeadPic());
        $this->_view->set('nick', $appUser->getNick());
        $text    = $this->_request->get( 'text' );
        $this->_view->set( 'text', $text );
    }
}
