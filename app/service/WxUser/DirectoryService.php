<?php

use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\Directory;
use MP\Model\Mp\DirectoryType;
use MP\Model\Mp\WxUser;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\Weixin\DirectoryBusiness;

// 普通用户账户设置相关api
// todo : 安全方面，增加token，
class DirectoryService extends Service
{
    public function dailyTraffic()
    {
        $wxUserId = App::getInstance()->request()->get('wx_user_id');
        $mpUserId = App::getInstance()->request()->get('mp_user_id',0);
        $directoryId = App::getInstance()->request()->get('directory_id');
        $communityId = App::getInstance()->request()->get('community_id');
        // 更新目录统计数据
        DirectoryBusiness::trafficUpdate($wxUserId,$mpUserId,$directoryId,$communityId);
    }

}
