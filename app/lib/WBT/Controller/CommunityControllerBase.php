<?php

namespace WBT\Controller;

use Bluefin\Controller;
use Common\Data\Event;
use Common\Helper\BaseController;
use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\Community;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\SuperAdmin;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\MpUserBusiness;
use WBT\Model\Weibotui\UserStatus;
use Bluefin\Auth\AuthInterface;
use MP\Model\Mp\CommunityAdminPowerType;
use MP\Model\Mp\IndustryType;

class CommunityControllerBase extends BaseController
{
    protected $_userID;
    protected $_username;
    protected $_mpUserID;
    protected $_communityID;
    protected $_isCommunityAdmin;
    protected $_moduleName;
    protected $_powerArray;
    protected $_isMpAdmin;
    protected $_isCompanyAdmin;

    protected function _init()
    {
        $this->_isMpAdmin = false;
        $this->_userID   = UserBusiness::getLoginUser()->getUserID();
        $this->_username = UserBusiness::getLoginUser()->getUsername();
        $this->_view->set('username', $this->getSimpleUsername($this->_username));
        $this->_view->set('username_all', $this->_username);
        $this->_mpUserID = $this->_request->get( 'mp_user_id' );
        $this->_communityID = $this->_request->get("community_id");
        $this->_view->set('community_id', $this->_communityID);
        $this->_view->set('mp_user_id', $this->_mpUserID);
        $mpUser = new MpUser([MpUser::MP_USER_ID => $this->_mpUserID]);
        $community = new Community([Community::COMMUNITY_ID=>$this->_communityID]);
        $industry = $mpUser->getIndustry();
        if($industry == IndustryType::RESTAURANT)
        {
            $this->_view->set('restaurant', true);
        }
        $this->_view->set('industry', $industry);
        $this->_view->set("community_type_all", $community->getCommunityType());
        $auth = $this->_requireAuth( 'weibotui' );
        $this->_checkAccountStatus( $auth );

        if (empty( $this->_mpUserID ))
        {
            log_warn("[mpUserId:{$this->_mpUserID}] 缺少公共账号 ID 参数");
            $this->_redirectToErrorPage('缺少公共账号 ID 参数' );
        }
        if(!$mpUser->getValid())
        {
            log_warn("[mpUserId:{$this->_mpUserID}] 该公共账号 已经失效");
            $this->_redirectToErrorPage('该公共账号 已经失效' );
        }
        if(!$community->getValid() && !$community->isEmpty())
        {
            log_warn("[mpUserId:{$this->_mpUserID},CommunityId:{$this->_communityID}] 该公共帐号中的社区已经失效");
            $this->_redirectToErrorPage('该公共账号中的社区 已经失效' );
        }
        $this->_urlSignature = 'hou8e';
         //判断用户权限
        $this->checkPower($this->_username,$this->_communityID,$this->_mpUserID,$this->_moduleName);

        // my accounts
        $outputColumns = MpUser::s_metadata()->getFilterOptions();
        $ranking       = [ MpUser::MP_USER_ID ];
        $paging        = [];
        $data          = MpUserBusiness::getMpUserList($this->_username, $ranking, $paging, $outputColumns );
        $this->_view->set('my_accounts', $data);

        parent::_init();
    }
// 核实用户权限
    public function checkPower($userName,$communityID,$mpUserID,$moduleName)
    {
        //管理权限
        $powerArr = UserBusiness::checkPower($userName,$communityID,$mpUserID);
        $processPowerArr = [];
        foreach($powerArr as $power)
        {
            if(substr($power,-2) == '_r')
            {
                $processPowerArr[] = substr($power,0,-2);
            }
            elseif(substr($power,-3) == '_rw')
            {
                $processPowerArr[] = substr($power,0,-3);
            }
            elseif(substr($power,-2) == '_d')
            {
                $processPowerArr[] = substr($power,0,-2);
            }
            else
            {
                $processPowerArr[] = $power;
            }
        }
        foreach($processPowerArr as $power)
        {
            $this->_view->set("$power", $power);
        }
        if(!strict_in_array($moduleName,$processPowerArr) and $moduleName!="")
        {
            log_warn("[userID:{$this->_userID}] 没有权限访问该页面");
            $this->_redirectToErrorPage('您没有权限访问该页面');
        }
        $this->_powerArray = $powerArr;
        if( $powerArr['user'] == "super_admin" or  $powerArr['user'] == "mp_admin")
        {
            $this->_view->set( 'is_super_admin', TRUE );
            $this->_view->set( 'is_mp_admin', TRUE );
            $this->_isMpAdmin = true;
        }
        elseif($powerArr['user'] == "community_admin")
        {
            $this->_isCommunityAdmin = true;
            $this->_view->set('is_community_admin', TRUE);
        }
        elseif($powerArr['user'] == "company_admin")
        {
            $this->_isCompanyAdmin = true;
            $this->_view->set('is_company_admin', TRUE);
            $this->_view->set('company_admin', '1');
        }
        elseif($powerArr['user'] == "no_admin")
        {
            log_warn("[userID:{$this->_userID}] 没有权限访问该页面");
            $this->_redirectToErrorPage('试图访问未授权的公共账号信息');
        }
    }

// 核实用户是否具有修改权限
    public function checkChangePower($updateName,$deleteName,$directorySmallFlow = null)
    {
       $power["update"] = strict_in_array($updateName,$this->_powerArray);//修改权限判断
       $power["delete"] = strict_in_array($deleteName,$this->_powerArray);//删除权限判断
       $power["directory_small_flow"] = strict_in_array($directorySmallFlow,$this->_powerArray);//小流量权限判断
       return $power;
    }

    protected function _checkAccountStatus( AuthInterface $auth )
    {

    }

    protected function _setUserProfileAndRolesInView() {
        $userProfiles = UserBusiness::getUserProfileFromSession();

        $this->_view->set( 'loginProfile', $userProfiles );
        $this->_view->set( 'userRoles', $this->_app->role( 'weibotui' )->get() );
    }

    // 可以在构造函数中调用
    protected function _redirectToErrorPage( $message) {
        log_debug("$message");
        $uri = sprintf('/error/index/?message=%s', utf8_encode($message));
        $this->_gateway->redirect($uri);
    }
}
