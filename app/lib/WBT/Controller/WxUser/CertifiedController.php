<?php

namespace WBT\Controller\WxUser;

use MP\Model\Mp\Bill;
use MP\Model\Mp\BillDetail;
use MP\Model\Mp\ChannelArticle;
use MP\Model\Mp\Community;
use MP\Model\Mp\CustomerSpecialist;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\BillBusiness;
use WBT\Business\Weixin\DirectoryBusiness;
use WBT\Controller\WxUserControllerBase;
use MP\Model\Mp\HouseMemberType;
use Common\Helper\BaseController;
use MP\Model\Mp\AddressLevelInfo;
use Bluefin\Data\Database;
use Bluefin\HTML\Table;
class CertifiedController extends WxUserControllerBase
{
    // 输入信息，绑定会员
    public function userRequiredAction()
    {

    }

}
