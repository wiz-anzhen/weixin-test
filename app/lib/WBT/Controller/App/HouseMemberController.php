<?php

namespace WBT\Controller\App;

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
class HouseMemberController extends BaseController
{
    // 输入信息，绑定会员
    public function certifyAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $communityID = $wxUser->getCurrentCommunityID();
        $this->_view->set( 'wx_user_id', $wxUserID);
        $memberType = HouseMemberType::getDictionary();
        $type = [];
        foreach($memberType as $key => $value)
        {
            $type [] = [$value,$key];
        }
        $address = AddressLevelInfo::fetchRows(['*'],[AddressLevelInfo::COMMUNITY_ID => $communityID]);
        $level = [];
        foreach($address as $value)
        {
            $level[] = $value[AddressLevelInfo::LEVEL];
        }
        $maxLevel = max($level);
        $this->_view->set( 'max_level', $maxLevel);
        //对现有数据进行处理，可以取出level=1的数据
        $addressProcess = [];
        foreach($address as $value)
        {
            for($i=1;$i<=$maxLevel;$i++)
            {
                if($value[AddressLevelInfo::LEVEL] == $i)
                {
                    $addressProcess[$i][] = $value;
                }
            }

        }

        $this->_view->set( 'address_level_1', $addressProcess[1]);
        $addressProcess = array_slice($addressProcess,1);
        log_debug("============",$addressProcess);
        //按level等级循环，不包含level==1
        $level = array_unique($level);
        $level = array_slice($level,1);
        $this->_view->set( 'level', $level);

        $this->_view->set( 'member_type', $type);

    }

    // 查看已认证用户
    public function checkAction()
    {
        $wxUser =  new WxUser();
        $wxUser = $this->_wxUser;
        $wxUserID = $wxUser->getWxUserID();
        $outputColumns = HouseMember::s_metadata()->getFilterOptions();
        $paging = []; // 先初始化为空
        $ranking = [HouseMember::VERIFY_TIME];
        $condition = $this->_request->getQueryParams();
        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        $cs = new CustomerSpecialist([CustomerSpecialist::WX_USER_ID => $wxUserID]);
        $csID = $cs->getCustomerSpecialistID();
        $checkInfo = HouseMember::fetchRowsWithCount( ['*'], [HouseMember::CURRENT_CS_ID => $csID], null, $ranking, $paging, $outputColumns );
        $shownColumns =
            [

                HouseMember::HOUSE_ADDRESS,
                HouseMember::NAME ,
                'cs_name' => [
                    Table::COLUMN_TITLE => '客服专员',
                    Table::COLUMN_FUNCTION =>function($row){
                            if(empty($row[HouseMember::CS_NAME]))
                            {
                                $row[HouseMember::CS_NAME] = '';
                            }
                            return $row[HouseMember::CS_NAME];
                        }
                ],
             ];

        $table   = Table::fromDbData( $checkInfo, $outputColumns, HouseMember::HOUSE_MEMBER_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = true;
        $this->_view->set( 'table', $table );

    }

}
