<?php

namespace WBT\Business\Weixin;

use Bluefin\App;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\MpAdmin;
use MP\Model\Mp\SuperAdmin;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\CompanyAdmin;
use MP\Model\Mp\MpUserNav;
use MP\Model\Mp\WxNavigationType;
use MP\Model\Mp\WxSubMenu;
use MP\Model\Mp\WeixinMessageType;
use MP\Model\Mp\Community;
use WBT\Business\Weixin\CommunityBusiness;

class MpUserBusiness extends BaseBusiness
{
    //更新
    public static function setMpUserCounts($mpUserID, $ymd)
    {
        $zhuhuVerify = HouseMemberBusiness::getMpUserVerifyZhuhuCount($mpUserID);
        $yezhuVerify = HouseMemberBusiness::getMpUserVerifyYezhuCount($mpUserID);
        $zhuhuCount = HouseMemberBusiness::getMpUserZhuhuCount($mpUserID);
        $yezhuCount = HouseMemberBusiness::getMpUserYezhuCount($mpUserID);
        ReportBusiness::setReportCount($mpUserID,$ymd, $zhuhuCount,$yezhuCount,$zhuhuVerify,$yezhuVerify);

        return true;
    }

    public static function test(){
        MpUser::fetchCount(['mp_user_id' => 12345]);
    }
    public static function getMpUserByMpAdminId( $userId ) {
        $sql = <<<EOF
SELECT `mu`.*
FROM `mp_admin` `ma`
LEFT JOIN `mp_user` `mu` ON `ma`.`mp_user_id` = `mu`.`mp_user_id`
WHERE `ma`.`user_id` = ? AND `mu`.`valid`=1
EOF;

        return _QUERY( 'fcrm', $sql, [ 'user_id' => $userId ] );
    }


    /**
     * 获取公众帐号列表。
     *
     * @param array $condition
     * @param array $paging
     * @param       $ranking
     * @param array $outputColumns
     * @return mixed
     */
    public static function getMpAccountList( array $condition = [ MpUser::VALID => 1 ], array &$paging = NULL, $ranking,
                                             array &$outputColumns = NULL ) {

        return MpUser::fetchRowsWithCount( [ '*' ], $condition, NULL, $ranking, $paging, $outputColumns );
    }

    public static function getMpUserList($username, $ranking, &$paging, $outputColumns)
    {
        $condition = null;

        if (SuperAdmin::fetchCount([SuperAdmin::USERNAME => $username]) > 0)
        {
            ///$condition = [ MpUser::VALID => 1 ];
            $condition = [  ];
        }
        elseif (CompanyAdmin::fetchCount([CompanyAdmin::USERNAME => $username]) > 0)
        {
            $companyAdmin = CompanyAdmin::fetchRowsWithCount(CompanyAdmin::MP_USER_ID,[CompanyAdmin::USERNAME => $username]);
            if(empty($companyAdmin))
            {
                return [];
            }
            $mpUserId = [];
            foreach($companyAdmin as $item)
            {
                $mpUserId[] = $item[CompanyAdmin::MP_USER_ID];
            }
            $condition = [MpUser::VALID => 1,MpUser::MP_USER_ID=>$mpUserId];;
        }
        else
        {
            $mpAdmin = MpAdmin::fetchRowsWithCount(MpAdmin::MP_USER_ID, [MpAdmin::USERNAME => $username]);
            if(empty($mpAdmin))
            {
                return [];
            }

            $mpUserId = [];
            foreach ($mpAdmin as $item)
            {
                $mpUserId[] = $item[MpAdmin::MP_USER_ID];
            }
            $condition = [MpUser::VALID => 1, MpUser::MP_USER_ID => $mpUserId];
        }

        return MpUser::fetchRowsWithCount(['*'], $condition, null, $ranking, $paging, $outputColumns);
    }

    /**
     * 生成公众帐号api中的token， 本函数生成的token长度为15， 都是字母
     * token必须为英文或者数字，长度为3到32个字符 (摘自mp.weixin.qq.com )
     *
     * @static
     *
     */
    public static function generateMpToken() {
        $str         = 'abcdefghigklmnopqrstuvwxyz';
        $maxStrIndex = strlen( $str ) - 1;
        $token       = '';
        $tokenLen    = 15;

        for ($i = 0; $i < $tokenLen; ++$i) {
            $r = rand( 0, $maxStrIndex );
            $token .= $str[$r];
        }

        return $token;
    }

    // api中的url标志符， 优化，加上日期前缀
    public static function generateMpApiID() {
        $str         = '123456789abcdefghigklmnopqrstuvwxyz';
        $maxStrIndex = strlen( $str ) - 1;
        $apiID       = '';
        $apiIDLen    = 10;

        for ($i = 0; $i < $apiIDLen; ++$i) {
            $r = rand( 0, $maxStrIndex );
            $apiID .= $str[$r];
        }

        return $apiID;
    }

    public static function generateMpUserId() {
        $str = '1234567890';
        $maxStrIndex = strlen($str) - 1;
        $mpUserId = '';
        $length = 5;
        for ($i = 0; $i < $length; ++$i) {
            $r = rand(0, $maxStrIndex);
            $mpUserId .= $str[$r];
        }
        return $mpUserId;
    }

    public static function getMpUserIDByApiID( $apiID ) {
        $mpUser = new MpUser([ MpUser::API_ID => $apiID ]);
        if ($mpUser->isEmpty()) {
            return NULL;
        }

        return $mpUser->getMpUserID();
    }


    /**
     * 添加公众帐号
     * @static
     * @param $userID
     * @param $mpName string 公众帐号名称
     * @return bool
     */
    public static function addMpAccount( $userID, $mpName ) {
        $token = self::generateMpToken();
        $apiID = self::generateMpApiID();

        $mpUser = new MpUser([ MpUser::API_ID => $apiID ]);
        if (!$mpUser->isEmpty()) {
            $apiID = self::generateMpApiID();
        }

        $mpUser->reset();


        $mpUser->setToken( $token )->setApiID( $apiID )->setMpName( $mpName )->insert();

        return TRUE;
    }


    // 编辑公众帐号
    public static function editMpAccount( $userID, $mpUserID, $mpName, $token ) {
        // apiID冲突了再重试一次
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserID ]);
        if ($mpUser->isEmpty()) {
            log_warning( "could not find mp_user.[mpUserID:$mpUserID]" );

            return FALSE;
        }

        $mpUser->setToken( $token )->setMpName( $mpName )->save();

        return TRUE;
    }

    // 删除 公众帐号
    public static function deleteMpAccount( $userID, $mpUserID ) {
        // apiID冲突了再重试一次
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserID ]);
        if ($mpUser->isEmpty()) {
            log_warning( "could not find mp_user.[mpUserID:$mpUserID]" );

            return FALSE;
        }

        $mpUser->delete();

        return TRUE;
    }

    public static function userHasPowerOfMpUserID( $userID, $mpUserID ) {
        $mpUser = new MpUser([ MpUser::MP_USER_ID => $mpUserID ]);
        if ($mpUser->isEmpty()) {
            log_warning( "invalid mpUserID($mpUserID)" );

            return FALSE;
        }


        return TRUE;
    }

    public static function getMpUser( $mpUserID )
    {
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        if ($mpUser->isEmpty())
        {
            return NULL;
        }

        return $mpUser;
    }

    public static function getMpUserName( $mpUserID ) {
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        if ($mpUser->isEmpty()) {
            return NULL;
        }

        return $mpUser->getMpName();
    }

    public static function update( $mpUserID, $followedContent, $noMatchContent, $locationX, $locationY, $menuCover ) {
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);

        if ($mpUser->isEmpty()) {
            log_warn( "Could not find MpUser($mpUserID)" );

            return FALSE;
        }

        $mpUser->setLocationX( $locationX );
        $mpUser->setLocationY( $locationY );
        $mpUser->update();

        return TRUE;
    }

    public static function superAdminUpdate( $mpUserID, $data ) {
        $mpUser = new MpUser([MpUser::MP_USER_ID => $mpUserID]);
        if ($mpUser->isEmpty()) {
            log_warn( "Could not find MpUser($mpUserID)" );

            return FALSE;
        }
        $mpUser->apply($data)->update();

        return TRUE;
    }

    public static function superAdminAdd( $data )
    {
        $mpUser                         = new MpUser();
        $data['valid']                  = $data['valid'] == 1 ? 1 : 0;
        $mpUserId = self::generateMpUserId();
        $mpUser->setMpName( $data[MpUser::MP_NAME] )
            ->setValid( $data[MpUser::VALID] )
            ->setComment( $data[MpUser::COMMENT] )
            ->setToken( self::generateMpToken() )
            ->setApiID( self::generateMpApiID() )
            ->setMpUserID( $mpUserId )
            ->insert();
        //新建一个公共账号后添加一个同名虚拟的社区
        $community_data = [Community::NAME=>$data[MpUser::MP_NAME],
                           Community::IS_VIRTUAL=>1,
                           Community::MP_USER_ID=>$mpUserId];
        CommunityBusiness::communityInsert($community_data);
        return TRUE;
    }

    public static function getNavigationItemByWxMenuClick(array $wxSubMenu, MpUser &$mpUser, WxUser &$wxUser)
    {
        $type = $wxSubMenu[WxSubMenu::CONTENT_TYPE];

        return null;
    }



}

