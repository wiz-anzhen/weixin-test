<?php

namespace WBT\Controller\MpAdmin;
use Bluefin\Data\Database;
use Bluefin\Controller;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxPayRecord;
use WBT\Business\ConfigBusiness;
use WBT\Business\UserBusiness;

use MP\Model\Mp\Community;
use WBT\Business\Weixin\WxApiBusiness;
use WBT\Business\Weixin\WxPayRecordBusiness;
use WBT\Controller\CommunityControllerBase;

class WxPayRecordController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "order";
        parent::_init();
    }
    //显示
    public function listAction()
    {
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        if (!isset($paging[Database::KW_SQL_ROWS_PER_PAGE]))
        {
            $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;
        }
        $mpUserId = $this->_request->get( 'mp_user_id' );
        $this->_view->set( MpUser::MP_USER_ID, $mpUserId );
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserId ]);
        $mpUserName = $mpUser->getMpName();
        $this->_view->set( 'mp_name', $mpUser->getMpName() );
        $this->_view->set( 'user_id', $userID = UserBusiness::getLoginUser()->getUserID() );

        $communityId = $this->_request->get( 'community_id');
        $community = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set( "community_name", $communityName );
        $this->_view->set( "community_id", $communityId );
        //查询条件
        $orderId = $name =  $payMethod = '';
        $name     = $this->_request->get( 'name' );
        $this -> _view -> set("name",$name);
        $orderId     = $this->_request->get( 'order_id' );
        $this -> _view -> set("order_id",$orderId);
        $payMethod     = $this->_request->get( 'pay_method' );
        $this -> _view -> set("pay_method",$payMethod);
        $pageArr    = $this->_request->get( '*PAGING*' );
        $page = $pageArr['page'];
        if (!empty($page))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = intval($page);
        }
        else
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $this->_view->set('page', $paging[Database::KW_SQL_PAGE_INDEX]);
        if($mpUserName == $communityName)
        {
            $condition     = [ WxPayRecord::MP_USER_ID => $mpUserId];
        }
        else
        {
            $condition     = [ WxPayRecord::MP_USER_ID => $mpUserId,WxPayRecord::COMMUNITY_ID => $communityId];
        }

        if(!empty($name))
        {
            $expr = " username like '%$name%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($orderId))
        {
            $condition[WxPayRecord::ORDER_ID] = $orderId;
        }
        if(!empty($payMethod))
        {
            $condition[WxPayRecord::PAY_METHOD] = $payMethod;
        }
        $payStartDateStart = $this->_request->get("pay_start_date_start");
        $payStartDateEnd = $this->_request->get("pay_start_date_end");
        if(!empty($payStartDateStart) && !empty($payStartDateEnd))
        {
            $expr = sprintf("`pay_start_date` >= '%s' and `pay_start_date` <= '%s'",$payStartDateStart,$payStartDateEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $this->_view->set('pay_start_date_start', $payStartDateStart);
        $this->_view->set('pay_start_date_end', $payStartDateEnd);
        $payEndDateStart = $this->_request->get("pay_end_date_start");
        $payEndDateEnd = $this->_request->get("pay_end_date_end");
        if(!empty($payEndDateStart) && !empty($payEndDateEnd))
        {
            $expr = sprintf("`pay_end_date` >= '%s' and `pay_end_date` <= '%s'",$payEndDateStart,$payEndDateEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $this->_view->set('pay_end_date_start', $payEndDateStart);
        $this->_view->set('pay_end_date_end', $payEndDateEnd);

        $ranking    = $this->_request->get( 'rank' );
        $this->_view->set( 'rank', $ranking);
        if(empty($ranking))
        {
            $ranking = [WxPayRecord::_CREATED_AT => true];
        }
        else
        {
            if($ranking == 'pay_time_reduce')
            {
                $ranking = [WxPayRecord::_CREATED_AT ];
            }
            else
            {
                $ranking       = [ WxPayRecord::_CREATED_AT => true ];
            }
        }

        $outputColumns = WxPayRecord::s_metadata()->getFilterOptions();


        //$ranking       = [ WxPayRecord::WX_PAY_RECORD_ID ];
        $data          = WxPayRecordBusiness::getList( $condition, $paging, $ranking, $outputColumns );

        $shownColumns = [
            WxPayRecord::ORDER_ID  => [  Table::COLUMN_TITLE       => '订单号',
                Table::COLUMN_FUNCTION   => function(array $row)
                    {
                        $orderID=$row[WxPayRecord::ORDER_ID];
                        $communityID = $row[WxPayRecord::COMMUNITY_ID];
                        $mpUserID=$row[WxPayRecord::MP_USER_ID];
                        return "<a target=\"_blank\"   href=\"/mp_admin/order/detail?mp_user_id={$mpUserID}&order_id={$orderID}&community_id={$communityID}\"  >{$orderID}</a>";
                    }
            ],
            WxPayRecord::USERNAME,
            WxPayRecord::OUTTRADENO,
            WxPayRecord::TRANSACTIONID,
            WxPayRecord::PAY_METHOD,
            WxPayRecord::PAY_START_DATE => [Table::COLUMN_TITLE => "下订单时间"],
            WxPayRecord::PAY_END_DATE,
            WxPayRecord::PAY_VALUE,
            WxPayRecord::PAY_FINISHED => [Table::COLUMN_FUNCTION => function (array $row){
                    if($row[WxPayRecord::PAY_FINISHED] == 0)
                    {
                        return "未支付";
                    }
                    else
                    {
                        return "已支付";
                    }

                }],

           ];

        $table               = Table::fromDbData( $data, $outputColumns, WxPayRecord::WX_PAY_RECORD_ID, $paging, $shownColumns,
            [ 'class' => 'table-bordered table-striped table-hover' ] );
        $table->showRecordNo = false;
        $this->_view->set( 'table', $table );
    }


}