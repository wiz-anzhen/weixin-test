<?php

namespace WBT\Business\Weixin;

use MP\Model\Mp\CommunityAdmin;
use MP\Model\Mp\CompanyAdmin;
use MP\Model\Mp\CommunityAdminPowerType;
use MP\Model\Mp\WxUser;
use WBT\Model\Weibotui\User;

/* 点菜相关业务 */
class CompanyAdminBusiness extends BaseBusiness
{

    //列表的显示
    public static function getCompanyAdminList(array $condition, array &$paging = null, $ranking, array $outputColumns = null)
    {
        return CompanyAdmin::fetchRowsWithCount( [ '*' ], $condition, null, $ranking, $paging, $outputColumns );
    }
    //数据的录入
    public static function insert($data,$password,$userName)
    {
        if (!preg_match('/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/',$userName))
        {
            return [ 'errno' => 1, 'error' => '邮箱格式不正确' ];
        }

        $obj = new User([User::USERNAME => $userName]);
        if ($obj->isEmpty())
        {
            if(strlen($password)<6)
            {
                return [ 'errno' => 1, 'error' => '密码长度不能小于6' ];
            }
            $obj->setPassword($password)->setUsername($userName)->setStatus("activated")->insert();
        }
        else
        {
            if(!empty($password))
            {
                if(strlen($password)<6)
                {
                    return [ 'errno' => 1, 'error' => '密码长度不能小于6' ];
                }
                $obj->setPassword($password)->setStatus("activated")->update();
            }
        }

        $obj = new CompanyAdmin();
        $obj->apply( $data );
        try {
            $obj->insert();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }
        return [ 'errno' => 0 ];
    }
    //修改
    public static function update( $id,$data )
    {
        $obj = new CompanyAdmin([ CompanyAdmin::COMPANY_ADMIN_ID => $id]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find CompanyAdmin($id)" );

            return ['errno' => 1, 'error' => '找不到记录'];
        }

        $obj->apply( $data );

        try {
            $obj->update();
        } catch (\Exception $e) {
            return ['errno' => 1, 'error' => $e->getMessage()];
        }
        return ['errno' => 0];
    }
    //删除
    public static function delete( $id )
    {
        $obj = new CompanyAdmin([ CompanyAdmin::COMPANY_ADMIN_ID => $id]);

        if ($obj->isEmpty()) {
            log_debug( "Could not find CompanyAdmin($id)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        try {
            $obj->delete();
        } catch (\Exception $e) {
            return [ 'errno' => 1, 'error' => $e->getMessage() ];
        }

        return [ 'errno' => 0 ];
    }
    public static function updatePassword( $password,$userName )
    {
        $obj = new User([User::USERNAME => $userName]);
        if(strlen($password)<6)
          {
              return [ 'errno' => 1, 'error' => '密码长度不能小于6' ];
          }
        $obj->setPassword($password)->update();
        return [ 'errno' => 0 ];
    }

    public static function saveNotifyTime($time,$userName)
    {
        $obj = new CommunityAdmin([CommunityAdmin::USERNAME => $userName]);
        if ($obj->isEmpty()) {
            log_debug( "Could not find CommunityAdmin($userName)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $obj->setOrderNotifyTime($time)->update();
        return [ 'errno' => 0 ];
    }

    public static function saveAnswerId($answerId,$userName)
    {
        $obj = new CommunityAdmin([CommunityAdmin::USERNAME => $userName]);
        if ($obj->isEmpty()) {
            log_debug( "Could not find CommunityAdmin($userName)" );

            return [ 'errno' => 1, 'error' => '找不到记录' ];
        }
        $obj->setAnswerNotifyID($answerId)->update();
        return [ 'errno' => 0 ];
    }
}