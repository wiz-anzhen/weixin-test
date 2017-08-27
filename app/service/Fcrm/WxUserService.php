<?php

set_include_path( LIB . '/PHPExcel' . PATH_SEPARATOR . get_include_path() );
require_once LIB . '/PHPExcel/PHPExcel.php';
require_once 'MpUserServiceBase.php';


use MP\Model\Mp\WxUser;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\WxUserBusiness;
use MP\Model\Mp\WxPayRecord;
use WBT\Business\Weixin\WxPayRecordBusiness;
class WxUserService extends MpUserServiceBase
{
    public function update()
    {
        $res      = array( 'errno' => 0 );
        $request  = $this->_app->request();
        $wxUserId = $request->get( WxUser::WX_USER_ID );
        $mpUserId = $request->get( WxUser::MP_USER_ID);
        $wxUser   = new WxUser([WxUser::WX_USER_ID => $wxUserId, WxUser::MP_USER_ID => $mpUserId]);
        $fields   = [ WxUser::USER_LEVEL ];
        $data     = $request->getArray( $fields );

        $wxUser->apply($data)->update();

        return $res;
    }

    public function download()
    {
        $mpUserID = $this->_app->request()->getQueryParam( 'mp_user_id' );
        $communityID = $this->_app->request()->getQueryParam( 'community_id' );
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $community = new Community([Community::COMMUNITY_ID => $communityID]);

        if($mpUser->getMpName() == $community->getName())
        {
            $condition = [WxUser::MP_USER_ID => $mpUserID];
        }
        else
        {
            $condition = [WxUser::MP_USER_ID => $mpUserID,WxUser::CURRENT_COMMUNITY_ID => $communityID];
        }
        log_debug("====================",$condition);
        $timeVerifyStart = $this->_app->request()->getQueryParam("time_verify_start");
        $timeVerifyEnd = $this->_app->request()->getQueryParam("time_verify_end");
        $timeRegisterStart = $this->_app->request()->getQueryParam("time_register_start");
        $timeRegisterEnd = $this->_app->request()->getQueryParam("time_register_end");

        if(!empty($timeRegisterStart) && !empty($timeRegisterEnd))
        {
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
            $expr = sprintf("`register_time` >= '%s' and `register_time` <= '%s'",$timeRegisterStart,$timeRegisterEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        if(!empty($timeVerifyStart) && !empty($timeVerifyEnd))
        {
            $expr = "wx_user_id is not null";
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
            $expr = sprintf("`create_time` >= '%s' and `create_time` <= '%s'",$timeVerifyStart,$timeVerifyEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }

        log_debug("====================",$condition);
        $data = WxUserBusiness::getWxUserList( $condition, $paging=NULL, NULL, NULL );
        $newData = $data;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('会员信息统计');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);
//标题栏：订单编号 、订单状态、收货人姓名、收货人电话、 客服组、客服专员、商品名称、商品销售价、商品提成、商品数量

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '会员号' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '电话' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '姓名' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '地址' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, '邮箱' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, '生日' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, '关注时间' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, '注册时间' );

        foreach ($newData as $data)
        {
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, $data[WxUser::VIP_NO] );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, $data[WxUser::PHONE]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, $data[WxUser::NICK]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $data[WxUser::ADDRESS]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $data[WxUser::EMAIL]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $data[WxUser::BIRTH]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, $data[WxUser::CREATE_TIME]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, $data[WxUser::REGISTER_TIME]  );
        }

        $filename = '会员信息统计';

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache - Control:must - revalidate, post - check = 0, pre - check = 0" );
        header( "Content-Type:application/force-download" );
        header( "Content-Type:application/vnd.ms-execl" );
        header( "Content-Type:application/octet-stream" );
        header( "Content-Type:application/download" );;
        header( 'Content-Disposition:attachment;filename="' . $filename . '.xlsx"' );
        header( "Content-Transfer-Encoding:binary" );
        $objWriter->save( 'php://output' );
    }

    public function downloadPay()
    {
        $mpUserID = $this->_app->request()->getQueryParam( 'mp_user_id' );
        $communityID = $this->_app->request()->getQueryParam( 'community_id' );
        //$mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        $community = new Community([Community::MP_USER_ID=>$mpUserID,Community::COMMUNITY_ID => $communityID]);
        $condition = [WxPayRecord::MP_USER_ID => $mpUserID,WxPayRecord::COMMUNITY_ID => $communityID];
        $payStartDateStart = $this->_app->request()->getQueryParam("pay_start_date_start");
        $payStartDateEnd = $this->_app->request()->getQueryParam("pay_start_date_end");
        if(!empty($payStartDateStart) && !empty($payStartDateEnd))
        {
            $expr = sprintf("`pay_start_date` >= '%s' and `pay_start_date` <= '%s'",$payStartDateStart,$payStartDateEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $payEndDateStart = $this->_app->request()->getQueryParam("pay_end_date_start");
        $payEndDateEnd = $this->_app->request()->getQueryParam("pay_end_date_end");
        if(!empty($payEndDateStart) && !empty($payEndDateEnd))
        {
            $expr = sprintf("`pay_end_date` >= '%s' and `pay_end_date` <= '%s'",$payEndDateStart,$payEndDateEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $data = WxPayRecordBusiness::getList( $condition, $paging=NULL, NULL, NULL );
        $dataArr = $data;
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('支付记录表');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '商铺名称' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '订单号' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '微信商户订单号' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '微信支付单号' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, '付款用户姓名' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5) . $row, '付款方式' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, '下订单时间' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, '订单完成时间' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8 ) . $row, '支付金额' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row, '是否完成支付' );
        log_debug('=========================',$dataArr);
        foreach($dataArr as $data)
        {
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, $community->getName() );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, $data[WxPayRecord::ORDER_ID]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, $data[WxPayRecord::OUTTRADENO]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $data[WxPayRecord::TRANSACTIONID]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $data[WxPayRecord::USERNAME]  );
            if($data[WxPayRecord::PAY_METHOD]=='wx_pay')
            {
                $payMethod = '微信支付';
            }
            elseif($data[WxPayRecord::PAY_METHOD]=='cash_pay')
            {
                $payMethod = '货到付款';
            }
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $payMethod  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, $data[WxPayRecord::PAY_START_DATE]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, $data[WxPayRecord::PAY_END_DATE]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8 ) . $row, $data[WxPayRecord::PAY_VALUE]  );
            if($data[WxPayRecord::PAY_FINISHED] == 1)
            {
                $payStatus = '已支付';
            }
            elseif($data[WxPayRecord::PAY_FINISHED] == 0)
            {
                $payStatus = '未支付';
            }
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, $payStatus  );
        }


        $filename = $community->getName().'支付记录表';

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
        header( "Pragma: public" );
        header( "Expires: 0" );
        header( "Cache - Control:must - revalidate, post - check = 0, pre - check = 0" );
        header( "Content-Type:application/force-download" );
        header( "Content-Type:application/vnd.ms-execl" );
        header( "Content-Type:application/octet-stream" );
        header( "Content-Type:application/download" );;
        header( 'Content-Disposition:attachment;filename="' . $filename . '.xlsx"' );
        header( "Content-Transfer-Encoding:binary" );
        $objWriter->save( 'php://output' );

    }

}