<?php

namespace WBT\Controller\MpAdmin;

/**
 * Created by PhpStorm.
 * User: kingcores
 * Date: 14-3-25
 * Time: 下午3:11
 * 房屋录入
 */

use Bluefin\Controller;
use Bluefin\Data\Database;
use Bluefin\HTML\Link;
use Bluefin\HTML\Table;
use MP\Model\Mp\Community;
use MP\Model\Mp\DirectoryType;
use MP\Model\Mp\HouseMember;
use MP\Model\Mp\HouseMemberType;
use MP\Model\Mp\MpArticle;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\BillDay;
use MP\Model\Mp\BillDetail;
use MP\Model\Mp\Bill;
use WBT\Business\UserBusiness;
use WBT\Business\Weixin\BillBusiness;
use WBT\Business\Weixin\HouseMemberBusiness;
use WBT\Controller\CommunityControllerBase;
use MP\Model\Mp\BillPayMethod;


class BillController extends CommunityControllerBase
{
    protected function _init()
    {
        $this->_moduleName = "bill";
        parent::_init();
    }
    //显示
    public function listAction()
    {

        $mpUserID = $this->_request->get(MpUser::MP_USER_ID);
        $this->_view->set(MpUser::MP_USER_ID, $mpUserID);
        $mpUser        = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $communityId   = $this->_request->get('community_id');
        $community     = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set("community_name", $communityName);
        $this->_view->set('mp_name', $mpUser->getMpName());
        $this->_view->set('user_id', $userId = UserBusiness::getLoginUser()->getUserID());
        $this->_view->set('community_id', $communityId);

        $paging = []; // 先初始化为空
        $outputColumns = BillDay::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);

        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;

        $ranking   = [BillDay::BILL_DAY => true];
        $condition = [BillDay::MP_USER_ID => $mpUserID, BillDay::COMMUNITY_ID => $communityId];

        $data = BillBusiness::getBillDayList($condition, $paging, $ranking, $outputColumns );

        $shownColumns = [
            BillDay::BILL_DAY => [
                Table::COLUMN_TITLE => '账单发送日期',
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        $billDay =  $row[BillDay::BILL_DAY];
                        $communityID = $row[BillDay::COMMUNITY_ID];
                        $mpUserID=$row[BillDay::MP_USER_ID];
                        $year = substr($billDay,0,4);
                        $month = substr($billDay,4,2);
                        $day = substr($billDay,6,2);
                        $url = "<a href=\"/mp_admin/bill/list_detail?mp_user_id={$mpUserID}&community_id={$communityID}&bill_day={$billDay}\">$year-$month-$day</a>";
                        return $url;
                    }
            ]
            ];
        $power = $this->checkChangePower("bill_rw","bill_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('bill_rw', $checkReadPower);
        if($power["delete"])
        {
            $shownColumns[Table::COLUMN_OPERATIONS] = [
            Table::COLUMN_CELL_STYLE => 'width:12%',
            Table::COLUMN_OPERATIONS => [
                new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，将无法恢复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/bill/remove_bill_day?bill_day={{ this.bill_day }}&community_id={{this.community_id}}', null, function(){bluefinBH.showInfo('删除成功',function(){location.reload();});});});"),
            ],];
        }


        $table               = Table::fromDbData($data, $outputColumns, BillDay::BILL_DAY_ID, $paging, $shownColumns,
            ['class' => 'table-bordered table-striped table-hover ']);
        $table->showRecordNo = true;
        $this->_view->set('table', $table);
    }
//账单细节显示
    public function listDetailAction()
    {

        $mpUserID = $this->_request->get(MpUser::MP_USER_ID);
        $this->_view->set(MpUser::MP_USER_ID, $mpUserID);
        $mpUser        = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $communityId   = $this->_request->get('community_id');
        $community     = new Community([Community::COMMUNITY_ID => $communityId]);
        $communityName = $community->getName();
        $this->_view->set("community_name", $communityName);
        $this->_view->set('mp_name', $mpUser->getMpName());
        $this->_view->set('user_id', $userId = UserBusiness::getLoginUser()->getUserID());
        $this->_view->set('community_id', $communityId);

        $billDay = $this->_request->get(Bill::BILL_DAY);
        $this->_view->set('bill_day', $billDay);

        //账单所有日期
        $allDay = BillDay::fetchColumn([Bill::BILL_DAY]);
        $this->_view->set('all_day', $allDay);

        $paging = []; // 先初始化为空
        $outputColumns = Bill::s_metadata()->getFilterOptions();
        $condition = $this->_request->getQueryParams();

        // 从url中提取condition和panging
        Database::extractQueryCondition($condition, $outputColumns, $paging, $ranking);
        $power = $this->checkChangePower("bill_rw","bill_d");
        $checkReadPower = false;
        if($power["update"] or $power["delete"])
        {
            $checkReadPower = true;
        }
        $this->_view->set('bill_rw', $checkReadPower);
        if (!isset($paging[Database::KW_SQL_PAGE_INDEX]))
        {
            $paging[Database::KW_SQL_PAGE_INDEX] = 1;
        }
        $paging[Database::KW_SQL_ROWS_PER_PAGE] = 50;

        $ranking   = [Bill::BILL_ID];
        //搜索条件
        $condition = [];
        $name = $houseAddress = $select_day = "";
        $name = $this->_request->get('name');
        $houseAddress = $this->_request->get('house_address');
        $select_day = $this->_request->get('select_day');
        $this->_view->set('name', $name);
        $this->_view->set('house_address', $houseAddress);
        $this->_view->set('select_day', $select_day);
        if(!empty($name))
        {
            $expr = " name like '%$name%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($houseAddress))
        {
            $expr = " house_address like '%$houseAddress%'";
            $con =  new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($select_day))
        {
            $condition[Bill::BILL_DAY ] = $select_day;
            if($select_day == "all")
            {
                $condition[Bill::BILL_DAY ] = $allDay;
            }
        }
        else
        {
            $condition[Bill::BILL_DAY ] = $billDay;
        }
        $condition[Bill::MP_USER_ID ] = $mpUserID;
        $condition[Bill::COMMUNITY_ID ] = $communityId;
        //计算总额
        $sum = Bill::fetchColumn([Bill::TOTAL_PAYMENT],$condition);
        $billCount = count($sum);
        $sum = array_sum($sum);
        $this->_view->set('sum', $sum);
        //计算阅读数量
        $readCondition = $condition;
        $expr = "read_time is not null";
        $con =  new \Bluefin\Data\DbCondition($expr);
        $readCondition[] = $con;
        $read_over = Bill::fetchCount($readCondition);
        $this->_view->set('read_over', $read_over);
        $readNo = $billCount-$read_over;
        $this->_view->set('read_no', $readNo);
        $data = BillBusiness::getBillList($condition, $paging, $ranking, $outputColumns );
        $shownColumns = [
            Bill::HOUSE_NO,
            Bill::NAME,
            Bill::BILL_DAY,
            Bill::PHONE,
            Bill::HOUSE_ADDRESS,
            Bill::HOUSE_AREA,
            Bill::TOTAL_PAYMENT,
            Bill::BILL_PAY_METHOD => [
                Table::COLUMN_TITLE => "支付方式",
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        if($row[Bill::PAY_FINISHED])
                        {
                            $ret = BillPayMethod::getDisplayName($row[Bill::BILL_PAY_METHOD]);

                        }
                        else
                        {
                            $ret = "";
                        }
                        return $ret;
                    }],
            Bill::PAY_FINISHED => [
                Table::COLUMN_TITLE => "是否完成支付",
                Table::COLUMN_FUNCTION => function (array $row)
                    {
                        if($row[Bill::PAY_FINISHED])
                        {
                            $ret = "已支付";

                        }
                        else
                        {
                            $ret = "未支付";
                        }
                        return $ret;
                    }],
            Bill::READ_TIME,
            "detail" =>
                [
                   Table::COLUMN_TITLE => "详情",
                   Table::COLUMN_CELL_STYLE => "width:38%;",
                   Table::COLUMN_FUNCTION => function (array $row)use($power,$checkReadPower)
                       {
                           $communityId =  $row[Bill::COMMUNITY_ID];
                           $billDay = $row[Bill::BILL_DAY];
                           $billDetail = BillDetail::fetchRows(['*'],[BillDetail::BILL_ID => $row[Bill::BILL_ID]]);
                           if($checkReadPower)
                           {
                               $ret = '<table class="table-bordered table-striped table-hover" style="background-color:white;border:1px solid #ddd;text-align: center">';
                               $ret .=' <tr>
                                         <td style=\"width:100px\">收费项目</td>
                                         <td style=\"width:150px\">计费周期</td>
                                         <td style=\"width:50px\">金额</td>
                                         <td style="width:45px">备注</td>
                                         <td style="width:45px">操作</td>
                                    </tr>';
                               foreach($billDetail as $value)
                               {
                                   $ret .= '<tr>';
                                   if($power["delete"])
                                   {
                                       $ret .= sprintf(
                                           "<td style=\"width:100px\">%s&nbsp;&nbsp;</td>
                                            <td style=\"width:150px\">%s&nbsp;&nbsp;</td>
                                            <td style=\"width:50px\">%s&nbsp;&nbsp;</td>
                                            <td style=\"width:50px\">%s&nbsp;&nbsp;</td>
                                            <td style=\"width:50px\">%s&nbsp;&nbsp;<br>%s</td>
                                            ",                                                                                                          $value[BillDetail::BILL_DETAIL_NAME],
                                           $value[BillDetail::BILLING_CYCLE],
                                           $value[BillDetail::DETAIL_PAYMENT],
                                           $value[BillDetail::DETAIL_REMARKS ],
                                           new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/bill_dialog/update_bill_detail?bill_detail_id={$value[Billdetail::BILL_DETAIL_ID]}&community_id={$communityId}&bill_day={$billDay}');"),
                                           new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，将无法恢复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/bill/remove_bill_detail?bill_detail_id={$value[Billdetail::BILL_DETAIL_ID]}&community_id={$communityId}', null, function(){bluefinBH.showInfo('删除成功',function(){location.reload();});});});")
                                       );
                                       $ret .= '</tr>';
                                   }
                                   else
                                   {
                                       $ret .= sprintf(
                                           "<td style=\"width:100px\">%s&nbsp;&nbsp;</td>
                                            <td style=\"width:150px\">%s&nbsp;&nbsp;</td>
                                            <td style=\"width:50px\">%s&nbsp;&nbsp;</td>
                                            <td style=\"width:50px\">%s&nbsp;&nbsp;</td>
                                            <td style=\"width:50px\">%s&nbsp;&nbsp;</td>
                                            ",                                                                                                          $value[BillDetail::BILL_DETAIL_NAME],
                                           $value[BillDetail::BILLING_CYCLE],
                                           $value[BillDetail::DETAIL_PAYMENT],
                                           $value[BillDetail::DETAIL_REMARKS ],
                                           new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/bill_dialog/update_bill_detail?bill_detail_id={$value[Billdetail::BILL_DETAIL_ID]}&community_id={$communityId}&bill_day={$billDay}');")
                                       );
                                       $ret .= '</tr>';
                                   }

                               }
                               $ret .= "</table>";
                           }
                           else
                           {
                               $ret = '<table class="table-bordered table-striped table-hover" style="background-color:white;border:1px solid #ddd;text-align: center">';
                               $ret .=' <tr>
                                         <td style=\"width:100px\">收费项目</td>
                                         <td style=\"width:150px\">计费周期</td>
                                         <td style=\"width:50px\">金额</td>
                                    </tr>';
                               foreach($billDetail as $value) {
                                   $ret .= '<tr>';
                                   $ret .= sprintf(
                                       "<td style=\"width:100px\">%s&nbsp;&nbsp;</td>
                                        <td style=\"width:150px\">%s&nbsp;&nbsp;</td>
                                        <td style=\"width:50px\">%s&nbsp;&nbsp;</td>
                                        ",                                                                                                              $value[BillDetail::BILL_DETAIL_NAME],
                                       $value[BillDetail::BILLING_CYCLE],
                                       $value[BillDetail::DETAIL_PAYMENT]
                                   );
                                   $ret .= '</tr>'; }
                               $ret .= "</table>";
                           }

                             return $ret;
                       }
                ]
        ];

        if($checkReadPower)
        {
            $shownColumns[Table::COLUMN_OPERATIONS] = [
                Table::COLUMN_TITLE => "缴费通知单明细操作",
                Table::COLUMN_CELL_STYLE => "width:10%",
                Table::COLUMN_FUNCTION => function (array $row)use($power)
                    {
                        $billID = $row[Bill::BILL_ID];
                        $communityId =  $row[Bill::COMMUNITY_ID];
                        $billDay = $row[Bill::BILL_DAY];
                        $mpUserID= $row[Bill::MP_USER_ID];
                        $update =  new Link('修改', "javascript:bluefinBH.ajaxDialog('/mp_admin/bill_dialog/update_bill?bill_id={$billID}&community_id={$communityId}');");
                        $delete = new Link('删除', "javascript:bluefinBH.confirm('警告：删除后，将无法恢复。<br/><br/>确定更要删除吗？', function(){javascript:wbtAPI.call('../fcrm/bill/remove_bill?bill_id={$billID}&community_id={$communityId}', null, function(){bluefinBH.showInfo('删除成功',function(){location.reload();});});});");
                        $addDetail = new Link('添加详情', "javascript:bluefinBH.ajaxDialog('/mp_admin/bill_dialog/add_bill_detail?mp_user_id={$mpUserID}&community_id={$communityId}&bill_id={$billID}&bill_day={$billDay}');");
                        $ret = $update."<br>".$addDetail;
                        if($power["delete"])
                        {
                            $ret .= "<br>".$delete;
                        }
                        return $ret;
                    }
                ,];
        }


        $table               = Table::fromDbData($data, $outputColumns, Bill::BILL_ID, $paging, $shownColumns,
            ['class' => 'table-bordered table-striped table-hover ']);
        $table->showRecordNo = false;
        $this->_view->set('table', $table);
    }
}