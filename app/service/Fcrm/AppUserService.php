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
use WBT\Business\Weixin\AppUserBusiness;
use MP\Model\Mp\AppUser;
class AppUserService extends MpUserServiceBase
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

        $timeVerifyStart = $this->_app->request()->getQueryParam("time_verify_start");
        $timeVerifyEnd = $this->_app->request()->getQueryParam("time_verify_end");

        if(!empty($timeVerifyStart) && !empty($timeVerifyEnd))
        {
            $expr = sprintf("`create_time` >= '%s' and `create_time` <= '%s'",$timeVerifyStart,$timeVerifyEnd);
            $con = new \Bluefin\Data\DbCondition($expr);
            $condition[] = $con;
        }
        $data = AppUserBusiness::getAppUserList( $condition, $paging=NULL, NULL, NULL );
        $newData = $data;

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->setTitle('APP会员信息统计');
        $objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(12);

        $row = 1;
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, '会员号' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, '电话' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '姓名' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '城市' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, '小区' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, '邮箱' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, '生日' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, '注册时间' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8 ) . $row, '经度' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row, '纬度' );

        foreach ($newData as $data)
        {
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, $data[AppUser::VIP_NO] );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, $data[AppUser::PHONE]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, $data[AppUser::NICK]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $data[AppUser::CITY]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $data[AppUser::COMMUNITY_NAME]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $data[AppUser::EMAIL]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, $data[AppUser::BIRTH]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, $data[AppUser::CREATE_TIME]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 8 ) . $row, $data[AppUser::LONGITUDEUSER]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 9 ) . $row, $data[AppUser::LATITUDEUSER]  );
        }

        $filename = 'APP会员信息统计';

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
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, '付款用户姓名' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, '付款方式' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, '支付开始时间' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, '支付结束时间' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, '支付金额' );
        $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 7 ) . $row, '是否完成支付' );
        log_debug('=========================',$dataArr);
        foreach($dataArr as $data)
        {
            $row++;
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 0 ) . $row, $community->getName() );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 1 ) . $row, $data[WxPayRecord::ORDER_ID]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 2 ) . $row, $data[WxPayRecord::USERNAME]  );
            if($data[WxPayRecord::PAY_METHOD]=='wx_pay')
            {
                $payMethod = '微信支付';
            }
            elseif($data[WxPayRecord::PAY_METHOD]=='cash_pay')
            {
                $payMethod = '货到付款';
            }
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 3 ) . $row, $payMethod  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 4 ) . $row, $data[WxPayRecord::PAY_START_DATE]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 5 ) . $row, $data[WxPayRecord::PAY_END_DATE]  );
            $objPHPExcel->getActiveSheet()->setCellValue( PHPExcel_Cell::stringFromColumnIndex( 6 ) . $row, $data[WxPayRecord::PAY_VALUE]  );
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