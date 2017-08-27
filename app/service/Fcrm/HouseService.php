<?php

require_once 'MpUserServiceBase.php';
require_once '../lib/Bluefin/bluefin.php';
require_once APP. "/lib/WBT/Business/HouseMemberExcelBusiness.php";
require_once APP. "/lib/WBT/Business/CategoryExcelBusiness.php";
define('EXCEL_PATH', ROOT . "/excel/");

use Bluefin\Service;
use MP\Model\Mp\TopDirectory;
use MP\Model\Mp\Directory;
use MP\Model\Mp\HouseMember;
use WBT\Business\Weixin\HouseMemberBusiness;
use MP\Model\Mp\WxUser;

class HouseService extends MpUserServiceBase{
    //添加
    public function insert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [
            HouseMember::MP_USER_ID,
            HouseMember::COMMUNITY_ID,
            HouseMember::HOUSE_ADDRESS,
            HouseMember::HOUSE_NO,
            HouseMember::HOUSE_AREA,
            HouseMember::NAME,
            HouseMember::MEMBER_TYPE,
            HouseMember::PROCUREMENT_POWER_TYPE,
            HouseMember::BIRTHDAY,
            HouseMember::PHONE1,
            HouseMember::PHONE2,
            HouseMember::PHONE3,
            HouseMember::ADD_TYPE => 'wuye',
            HouseMember::WX_USER_ID,
            HouseMember::COMMENT,
        ] );


        $data[HouseMember::MODIFY_TIME] = date('Y-m-d H:i:s',time());
        $houseMember = new HouseMember();
        $houseMember->apply($data);

        if($houseMember->insertInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '不能和其他住户信息重复'];
        }


    }
    //添加并认证
    public function insertCheck()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [
            HouseMember::MP_USER_ID,
            HouseMember::COMMUNITY_ID,
            HouseMember::HOUSE_ADDRESS,
            HouseMember::HOUSE_NO,
            HouseMember::HOUSE_AREA,
            HouseMember::NAME,
            HouseMember::MEMBER_TYPE,
            HouseMember::PROCUREMENT_POWER_TYPE,
            HouseMember::BIRTHDAY,
            HouseMember::PHONE1,
            HouseMember::PHONE2,
            HouseMember::PHONE3,
            HouseMember::ADD_TYPE => 'wuye',
            HouseMember::WX_USER_ID,
            HouseMember::COMMENT,
            'vip_no','cs_group','cs'
        ] );

        $data[HouseMember::MODIFY_TIME] = date('Y-m-d H:i:s',time());
        $houseMember = new HouseMember();
        $houseMember->apply($data);
        if(!isset($data ['vip_no']))
        {
            return['errno' => 1,'error' => '会员号 不能为空'];
        }
        else
        {
            $wxUser = new WxUser([WxUser::VIP_NO => $data['vip_no']]);
            if(!$wxUser->isEmpty())
            {
                if($houseMember->insertInRestraintOfUniqueKey())
                {
                    $houseMember = new HouseMember([HouseMember::HOUSE_NO => $data[HouseMember::HOUSE_NO],HouseMember::HOUSE_ADDRESS => $data[HouseMember::HOUSE_ADDRESS],HouseMember::NAME => $data[HouseMember::NAME]]);
                    $data['house_member_id'] = $houseMember->getHouseMemberID();
                    return HouseMemberBusiness::check($data );
                }
                else
                {
                    return['errno' => 1,'error' => '不能和其他住户信息重复'];
                }

            }
            else
            {
                return ['errno' => 1, 'error' => '系统中不存在此会员号，请您核对后重新输入'];
            }
        }



    }
    //修改
    public function update()
    {
        $data = $this->_app->request()->getArray(
            [
            HouseMember::HOUSE_MEMBER_ID,
            HouseMember::HOUSE_ADDRESS,
            HouseMember::HOUSE_NO,
            HouseMember::HOUSE_AREA,
            HouseMember::NAME,
            HouseMember::MEMBER_TYPE,
            HouseMember::PROCUREMENT_POWER_TYPE,
            HouseMember::BIRTHDAY,
            HouseMember::PHONE1,
            HouseMember::PHONE2,
            HouseMember::PHONE3,
            HouseMember::COMMENT,
        ] );
        $data[HouseMember::MODIFY_TIME] = date('Y-m-d H:i:s',time());

        $houseMember = new HouseMember();
        $houseMember->apply($data);

        if($houseMember->updateInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '不能和其他住户信息重复'];
        }
    }
    //认证
    public function check()
    {
        $data = $this->_app->request()->getArray(
            [
                'phone1','name','house_member_id','vip_no','cs_group','cs','community_id','house_address',HouseMember::PROCUREMENT_POWER_TYPE,
            ] );
        if(!isset($data['vip_no']))
        {
            return['errno' => 1,'error' => '会员号 不能为空'];
        }
        else
        {
            return HouseMemberBusiness::check($data);
        }

    }

    //删除
    public function remove()
    {
        $houseMemberId = $this->_app->request()->get( HouseMember::HOUSE_MEMBER_ID );
        $communityId = $this->_app->request()->getQueryParam( HouseMember::COMMUNITY_ID );

        return HouseMemberBusiness::delete( $houseMemberId,$communityId);
    }

    //重置
    public function reset()
    {
        $houseMemberId = $this->_app->request()->get( HouseMember::HOUSE_MEMBER_ID );
        $communityId = $this->_app->request()->getQueryParam( HouseMember::COMMUNITY_ID );
        return HouseMemberBusiness::reset( $houseMemberId,$communityId);
    }

    public function removeAll()
    {
        $mpUserId = $this->_app->request()->get( HouseMember::MP_USER_ID );
        $communityId = $this->_app->request()->getQueryParam( HouseMember::COMMUNITY_ID );

        return HouseMemberBusiness::deleteAll( $mpUserId,$communityId);
    }

    public function fileUpload()
    {
        $result = ['errno' => 0];
        $mpUserID = $_GET['mp_user_id'];
        $communityId = $_GET['community_id'];
        $relativePath = self::getRelativeDir();
        if(empty($relativePath))
        {
            return [ 'errno' => 1, 'error' => '上传失败' ];
        }

        if (!empty($_FILES) )
        {
            $fileParts = pathinfo($_FILES['Filedata']['name']);

            $ext = $fileParts['extension'];
            $fileType = strtolower($ext);

            if ($fileType == 'xlsx') // File extensions
            {
                $fileTmpName = $_FILES['Filedata']['tmp_name'];
                log_debug("[$fileTmpName][$fileType]");

                try
                {
                    $res = HouseMemberExcelBusiness::readCommunityFromExcel($fileTmpName, $fileType);
                    if ($res['errno'] == 0)
                    {
                        if (!HouseMemberBusiness::insertFromExcel($res, $mpUserID, $communityId))
                        {
                            $result['errno'] = 1;
                            $result['error'] = '写入数据库时出错。';
                        }
                    }
                    else
                    {
                        $result['errno'] = $res['errno'];
                        $result['error'] = $res['error'];
                    }

                }
                catch (\Exception $e)
                {
                    $result['errno'] = 1;
                    $result['error'] = $e->getMessage();
                }

            }
            else
            {
                $result['errno'] = 1;
                $result['error'] = "请使用后缀为.xlsx的Excle文件";
            }
        }
        return $result;
    }

    public function categoryUpload()
    {
        $result = ['errno' => 0];
        $mpUserID = $_GET['mp_user_id'];
        $communityId = $_GET['community_id'];
        $storeID = $_GET['store_id'];
        $categoryID = $_GET['category_id'];
        $relativePath = self::getRelativeDir();
        if(empty($relativePath))
        {
            return [ 'errno' => 1, 'error' => '上传失败' ];
        }

        if (!empty($_FILES) )
        {
            $fileParts = pathinfo($_FILES['Filedata']['name']);

            $ext = $fileParts['extension'];
            $fileType = strtolower($ext);

            if ($fileType == 'xlsx') // File extensions
            {
                $fileTmpName = $_FILES['Filedata']['tmp_name'];
                log_debug("[$fileTmpName][$fileType]");

                try
                {
                    $res = CategoryExcelBusiness::readCommunityFromExcel($fileTmpName, $fileType);
                    //log_debug("========================",$res);
                    if ($res['errno'] == 0)
                    {
                        if (!CategoryExcelBusiness::insertFromExcel($res, $mpUserID, $communityId, $storeID, $categoryID))
                        {
                            $result['errno'] = 1;
                            $result['error'] = '写入数据库时出错。';
                        }
                    }
                    else
                    {
                        $result['errno'] = $res['errno'];
                        $result['error'] = $res['error'];
                    }

                }
                catch (\Exception $e)
                {
                    $result['errno'] = 1;
                    $result['error'] = $e->getMessage();
                }

            }
            else
            {
                $result['errno'] = 1;
                $result['error'] = "请使用后缀为.xlsx的Excle文件";
            }
        }
        return $result;
    }

    private function getRelativeDir()
    {
        $ymd =  date( 'Ymd' );
        // name and path
        $relativePath = "/ueditor/php/upload/$ymd/";

        $imgRealPath = WEB_ROOT . $relativePath;
        if (!is_dir( $imgRealPath ))
        {
            mkdir( $imgRealPath );
        }

        if (!is_dir( $imgRealPath ))
        {
            log_fatal("创建文件夹失败。[imgRealPath:$imgRealPath]");
            return null;
        }

        return $relativePath;
    }

    //添加
    public function insertProcurement()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [
            HouseMember::MP_USER_ID,
            HouseMember::COMMUNITY_ID,
            HouseMember::NAME,
            HouseMember::MEMBER_TYPE,
            HouseMember::PROCUREMENT_POWER_TYPE,
            HouseMember::PART_ID,
            HouseMember::PHONE1,
            HouseMember::PHONE2,
            HouseMember::PHONE3,
            HouseMember::ADD_TYPE => 'wuye',
            HouseMember::WX_USER_ID,
            HouseMember::COMMENT,
        ] );

log_debug("=============================",$data);
        $data[HouseMember::MODIFY_TIME] = date('Y-m-d H:i:s',time());
        $houseMember = new HouseMember();
        $houseMember->apply($data);

        if($houseMember->insertInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '不能和其他住户信息重复'];
        }


    }
    //添加并认证
    public function insertCheckProcurement()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [
            HouseMember::MP_USER_ID,
            HouseMember::COMMUNITY_ID,
            HouseMember::NAME,
            HouseMember::MEMBER_TYPE,
            HouseMember::PROCUREMENT_POWER_TYPE,
            HouseMember::PART_ID,
            HouseMember::PHONE1,
            HouseMember::PHONE2,
            HouseMember::PHONE3,
            HouseMember::ADD_TYPE => 'wuye',
            HouseMember::WX_USER_ID,
            HouseMember::COMMENT,
            'vip_no',
        ] );

        $data[HouseMember::MODIFY_TIME] = date('Y-m-d H:i:s',time());
        $houseMember = new HouseMember();
        $houseMember->apply($data);
        if(!isset($data ['vip_no']))
        {
            return['errno' => 1,'error' => '会员号 不能为空'];
        }
        else
        {
            $wxUser = new WxUser([WxUser::VIP_NO => $data['vip_no']]);
            if(!$wxUser->isEmpty())
            {
                if($houseMember->insertInRestraintOfUniqueKey())
                {
                    $houseMember = new HouseMember([HouseMember::HOUSE_NO => $data[HouseMember::HOUSE_NO],HouseMember::HOUSE_ADDRESS => $data[HouseMember::HOUSE_ADDRESS],HouseMember::NAME => $data[HouseMember::NAME]]);
                    $data['house_member_id'] = $houseMember->getHouseMemberID();
                    return HouseMemberBusiness::check($data );
                }
                else
                {
                    return['errno' => 1,'error' => '不能和其他住户信息重复'];
                }

            }
            else
            {
                return ['errno' => 1, 'error' => '系统中不存在此会员号，请您核对后重新输入'];
            }
        }



    }
    //修改
    public function updateProcurement()
    {
        $data = $this->_app->request()->getArray(
            [
                HouseMember::HOUSE_MEMBER_ID,
                HouseMember::NAME,
                HouseMember::MEMBER_TYPE,
                HouseMember::PROCUREMENT_POWER_TYPE,
                HouseMember::PART_ID,
                HouseMember::PHONE1,
                HouseMember::PHONE2,
                HouseMember::PHONE3,
                HouseMember::COMMENT,
            ] );
        $data[HouseMember::MODIFY_TIME] = date('Y-m-d H:i:s',time());

        $houseMember = new HouseMember();
        $houseMember->apply($data);

        if($houseMember->updateInRestraintOfUniqueKey())
        {
            return['errno' => 0];
        }
        else
        {
            return['errno' => 1,'error' => '不能和其他住户信息重复'];
        }
    }
    //认证
    public function checkProcurement()
    {
        $data = $this->_app->request()->getArray(
            [
                'phone1','name','house_member_id','vip_no','community_id',HouseMember::PROCUREMENT_POWER_TYPE,
                HouseMember::MEMBER_TYPE,
                HouseMember::PART_ID,
            ] );
        if(!isset($data['vip_no']))
        {
            return['errno' => 1,'error' => '会员号 不能为空'];
        }
        else
        {
            return HouseMemberBusiness::check($data);
        }

    }

}
