<?php
/**
 * Created by PhpStorm.
 * User: tu
 * Date: 14-12-8
 * Time: 下午2:44
 */
use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\MpUser;
use MP\Model\Mp\WxMenu;
use WBT\Business\Weixin\WxMenuBusiness;
use WBT\Business\App\uploadImgBusiness;
use WBT\Business\ConfigBusiness;
use MP\Model\Mp\MpUserConfigType;
use WBT\Business\Weixin\WxUserBusiness;
use WBT\Business\SmsBusiness;
use WBT\Business\VoiceBusiness;
use MP\Model\Mp\AppUser;
use MP\Model\Mp\UserNotify;
use WBT\Business\Weixin\UserNotifyBusiness;
use Bluefin\Data\Database;
use MP\Model\Mp\AppNoticeReadRecord;
use MP\Model\Mp\Community;
use \WBT\Business\Weixin\CarouselBusiness;
use MP\Model\Mp\TopDirectory;
use MP\Model\Mp\HouseMember;
use WBT\Business\Weixin\DirectoryBusiness;
use MP\Model\Mp\Directory;
use WBT\Business\PushBusiness;
use MP\Model\Mp\WxUser;
use MP\Model\Mp\Province;
use MP\Model\Mp\City;
use MP\Model\Mp\Area;
class AppService extends Service  {
    //获取底部菜单  登陆用户
    public function getMenu()
    {
        /*$paging = array();
        $outputColumns = array();
        $ranking = [WxMenu::SORT_NO];
        $mpUserID = "21817";
        $data = WxMenuBusiness::getWxMenuList([MpUser::MP_USER_ID => $mpUserID], $paging, $ranking, $outputColumns);
        $data[0]["url"] = "http://dev.spm.weibotui.com/app/directory/list?community_id=8&top_directory_id=30&mp_user_id=".$mpUserID;
        $data[1]["url"] = "http://dev.spm.weibotui.com/app/directory/list?community_id=8&top_directory_id=31&mp_user_id=".$mpUserID;
        $data[2]["url"] = "http://dev.spm.weibotui.com/app/directory/list?community_id=4&top_directory_id=32&mp_user_id=".$mpUserID;*/
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $communityId =  App::getInstance()->request()->get('community_id');
        //$mpUserID = '9530';
        $phone = App::getInstance()->request()->get('phone');
        $expr = "top_dir_no in ('10','20','30')";
        $con =  new \Bluefin\Data\DbCondition($expr);
        $condition = [$con, TopDirectory::COMMUNITY_ID=>$communityId,TopDirectory::MP_USER_ID=>$mpUserID];
        $ranking = [TopDirectory::TOP_DIR_NO ];
        $data = TopDirectory::fetchRows(['*'],$condition,null,$ranking);
        foreach($data as $key=>$value)
        {
            $data_data = array();
            $topDir = new TopDirectory([TopDirectory::TOP_DIR_NO=>$value['top_dir_no'],TopDirectory::COMMUNITY_ID=>$value['community_id']]);
            $data_data = DirectoryBusiness::getList( [ Directory::TOP_DIRECTORY_ID => $topDir->getTopDirectoryID() ],$paging,null );
            //log_debug('data========',$data_data);
            if(count($data_data) == 1)
            {
                /*
                $pie = explode('?',$data_data['0']['common_content']);
                $data[$key]['url'] = ConfigBusiness::getHost().'/app/directory/list?'.$pie[1].'&phone='.$phone;
                */
                $data_data['0']['common_content'] = str_replace("wx_user","app",$data_data['0']['common_content'] );
                $data[$key]['url'] = $data_data['0']['common_content'].'&phone='.$phone;
            }
            else
            {
                $data[$key]['url'] = ConfigBusiness::getHost().'/app/directory/list?community_id='.$value['community_id'].'&top_directory_id='.$value['top_directory_id'].'&mp_user_id='.$value['mp_user_id'].'&phone='.$phone;
            }
            //$data[$key]['url'] = ConfigBusiness::getHost().'/app/directory/list?community_id='.$value['community_id'].'&top_directory_id='.$value['top_directory_id'].'&mp_user_id='.$value['mp_user_id'];
        }
        //log_debug('url=======================',$data);
        $status = 0;
        $message = '查询成功';
        $this::response($status,$message,$data);
    }
    //获取游客体验底部菜单
    //url没有做 先不展示
    public function touristMenu()
    {
        /*$paging = array();
        $outputColumns = array();
        $ranking = [WxMenu::SORT_NO];
        $mpUserID = "15906";
        $data = WxMenuBusiness::getWxMenuList([MpUser::MP_USER_ID => $mpUserID], $paging, $ranking, $outputColumns);*/
        $mpUserID = App::getInstance()->request()->get('mp_user_id');
        $communityId =  App::getInstance()->request()->get('community_id');
        $mpUserID = '43451';
        $communityId = '74';
        $expr = "top_dir_no in ('10','20','30')";
        $con =  new \Bluefin\Data\DbCondition($expr);
        $condition = [$con, TopDirectory::COMMUNITY_ID=>$communityId,TopDirectory::MP_USER_ID=>$mpUserID];
        $data = TopDirectory::fetchRows(['*'],$condition);
        foreach($data as $key=>$value)
        {
            $data[$key]['url'] = ConfigBusiness::getHost().'/app/directory/list?community_id='.$value['community_id'].'&top_directory_id='.$value['top_directory_id'].'&mp_user_id='.$value['mp_user_id'];
        }
        log_debug('data===',$data);

        $status = 0;
        $message = '查询成功';
        $this::response($status,$message,$data);
    }
    //获取验证码
    public function getVerifyCode()
    {
        $phone    = App::getInstance()->request()->get('phone');
        if(empty($phone)){
            $status = 1;
            $message = "参数为空";
            $this::response($status,$message,$data = "");
            return;
        }

        //获取短信验证码配置值
        $verifyCodeID = ConfigBusiness::mpUserConfig("21817");

        if(!$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID])
        {
            $status = 2;
            $message = "系统验证码id有误";
            $this::response($status,$message,$data = "");
            return;
        }
        $verifyCode = WxUserBusiness::generateVerifyPhoneCode();
        SmsBusiness::sendTemplate($phone, $verifyCode,$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID]);
        $status = 0;
        $message = '查询成功';
        $this::response($status,$message,$verifyCode);
    }
    //获取城市
    public function getCity()
    {
        $city = Community::fetchColumn([Community::CITY],[Community::IS_APP=>1]);
        //城市去重
        $city = array_unique($city);
        $newCity = ['请选择城市'];
        foreach($city as $val)
        {
            $newCity[] = $val;
        }
        $status = 0;
        $message = '获取成功';
        self::response($status,$message,$newCity);
    }
    //获取城市所在小区
    public function getCommunity()
    {
        $city    = App::getInstance()->request()->get('city');
        if(empty($city))
        {
            $status = 1;
            $message = '参数为空';
            $data = '';
            self::response($status,$message,$data);
            return;
        }
        //$communityArr = Community::fetchColumn([Community::NAME],[Community::IS_APP=>1,Community::CITY=>$city]);
        $data = Community::fetchRows(['*'],[Community::IS_APP=>1,Community::CITY=>$city]);
        //$newCommunity = [];
        $status = 0;
        $message = '获取成功';
        self::response($status,$message,$data);
    }
    //用户认证
    public function userAuth()
    {
        $city    = App::getInstance()->request()->get('city');
        $community = App::getInstance()->request()->get('community');
        $phone = App::getInstance()->request()->get('phone');
        $communityId = App::getInstance()->request()->get('community_id');
        $userId    = App::getInstance()->request()->get('userid');
        $channelId = App::getInstance()->request()->get('channelid');
        if(empty($city) || empty($community) || empty($phone) || empty($communityId)|| empty($userId)|| empty($channelId))
        {
            $status = 1;
            $message = '参数为空';
            $data = '';
            self::response($status,$message,$data);
            return;
        }
        $expr = sprintf("`phone1` = %s or `phone2` = %s or `phone3`=%s", $phone, $phone, $phone);
        $dbCondition = new \Bluefin\Data\DbCondition($expr);

        $condition = [$dbCondition,HouseMember::COMMUNITY_ID => $communityId];

        $houseMember = new HouseMember($condition);
        if(!$houseMember->isEmpty())
        {
            $appUser = new AppUser([AppUser::PHONE=>$phone]);
            $wx_user = new WxUser([WxUser::WX_USER_ID=>$houseMember->getWxUserID()]);
            if(!$wx_user->isEmpty())
            {
                $vip_no = $wx_user->getVipNo();
            }
            else
            {
                $vip_no = rand(100000,999999);
                $houseMember->setWxUserID($phone)->update();
            }
            if(!$appUser->isEmpty())
            {
                $appUser->setCity($city)
                    ->setCommunityName($community)
                    ->setCurrentCommunityID($communityId)
                    ->setBaiduUserID($userId)
                    ->setVipNo($vip_no)
                    ->setBaiduChannelID($channelId)
                    ->setLastAccess(date('Y-m-d H:i:s',time()))
                    ->update();
                $community_obj = new Community([Community::COMMUNITY_ID=>$communityId]);
                $head_img = $appUser->getHeadPic();
                if(empty($head_img))
                {
                    $head_img = $wx_user->getHeadPic();
                }
                $status = 0;
                $message = '认证成功';
                $data = ['community_id'=>$communityId,'community_name'=>$community_obj->getName(),'mp_user_id'=>$community_obj->getMpUserID(),'head_img'=>$head_img];
            }
            else
            {
                $status = 4;
                $message = '认证失败';
                $data = ['community_id'=>'','community_name'=>'','mp_user_id'=>'','head_img'=>''];
            }
        }
        else
        {
            $hs = new HouseMember([$dbCondition]);
            if(!$hs->isEmpty())
            {
                $community_obj = new Community([Community::COMMUNITY_ID=>$hs->getCommunityID()]);
                $status = 2;
                $message = '请选择正确小区';
                $data = ['community_id'=>$hs->getCommunityID(),'community_name'=>$community_obj->getName(),'mp_user_id'=>$community_obj->getMpUserID(),'head_img'=>''];
            }
            else
            {
                $community_obj = new Community([Community::COMMUNITY_ID=>$communityId]);
                $status = 3;
                $message = '';
                $data = ['community_id'=>$communityId,'community_mobile'=>$community_obj->getPhone(),'mp_user_id'=>$community_obj->getMpUserID(),'head_img'=>''];
            }

        }

        self::response($status,$message,$data);
    }
//用户注册
    public function userReg()
    {
        $phone    = App::getInstance()->request()->get('phone');
        $pwd    = App::getInstance()->request()->get('pwd');
        $confirmpwd    = App::getInstance()->request()->get('confirmpwd');
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $status = 0;
        $message = '注册成功';
        if(!$appUser->isEmpty())
        {
            $status = 1;
            $message = '该用户已被注册';
            $data = '';
            self::response($status,$message,$data);
            return;
        }
        //发送手机验证码

        //生成会员号
        $mpUser = new MpUser([MpUser::MP_USER_ID => "43451"]);
        $vipNo =  WxUserBusiness::generateVipNo($mpUser);

        $appUser = new AppUser();
        $appUser->setPhone($phone)
            ->setPassword($pwd)
            ->setCreateTime(date('Y-m-d H:i:s',time()))
            ->setLastAccess(date('Y-m-d H:i:s',time()))->setVipNo($vipNo)
            ->insert();
        $data = '';
        self::response($status,$message,$data);
    }
    //用户获取验证码接口
    public function getCode()
    {
        $phone    = App::getInstance()->request()->get('phone');
        if(empty($phone))
        {
            $status = 1;
            $message = "参数为空";
            self::response($status,$message,$data='');
            return;
        }
        $verifyCode = self::verifyCode($phone);
        $status = 0;
        $message = '发送验证码成功';
        self::response($status,$message,$verifyCode);
    }

    //注册获取验证码
    public static  function verifyCode($phone)
    {
        if(empty($phone)){
            $status = 1;
            return $status;
        }

        //获取短信验证码配置值
        $verifyCodeID = ConfigBusiness::mpUserConfig("43451");

        if(!$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID])
        {
            $status = 1;
            return $status;
        }
        $verifyCode = WxUserBusiness::generateVerifyPhoneCode();
        SmsBusiness::sendTemplate($phone, $verifyCode,$verifyCodeID[MpUserConfigType::VERIFY_CODE_ID]);
        return $verifyCode;
    }
    //重置密码
    public function resetPassword()
    {
        $phone    = App::getInstance()->request()->get('phone');
        $pwd    = App::getInstance()->request()->get('pwd');
        if(empty($phone) || empty($pwd))
        {
            $status = 1;
            $message = "参数为空";
            self::response($status,$message,$data='');
            return;
        }
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if($appUser->isEmpty())
        {
            $status = 2;
            $message = '该用户还没有注册';
            $data = '';
            self::response($status,$message,$data);
            return ;
        }
        $appUser->setPassword($pwd);
        $appUser->update();
        $status = 0;
        $message = '重置成功';
        self::response($status,$message,$data='');
    }
    //用户登录
    public function login()
    {
        $phone    = App::getInstance()->request()->get('phone');
        $pwd    = App::getInstance()->request()->get('pwd');
        if(empty($phone) || empty($pwd))
        {
            $status = 1;
            $message = "参数为空";
            self::response($status,$message,$data='');
            return;
        }
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if($appUser->isEmpty())
        {
            $status = 2;
            $message = '该用户还没有注册';
            $data = '';
            self::response($status,$message,$data);
            return ;
        }
        $appUser = new AppUser([AppUser::PHONE=>$phone,
            AppUser::PASSWORD=>$pwd]);
        if(!$appUser->isEmpty())
        {
            $city = $appUser->getCity();
            if(!(empty($city)))
            {
                $isAuth = 0;//已经认证
                $data = ['community_id'=>$appUser->getCurrentCommunityID(),'isAuth'=>$isAuth];

            }
            else
            {
                $isAuth = 1;//未认证
                $data = ['community_id'=>'','isAuth'=>$isAuth];
            }
            $status = 0;
            $message = '登录成功';

            self::response($status,$message,$data);
        }else{
            $status = 3;
            $message = '密码错误';
            $data = '';
            self::response($status,$message,$data);
        }
    }
    //获取模板消息接口
    public function userNotify()
    {
        $mpUserId    = App::getInstance()->request()->get('mp_user_id');
        $phone    = App::getInstance()->request()->get('phone');
        if(empty($mpUserId)||empty($phone))
        {
            $status = 1;
            $message = "参数为空";
            self::response($status,$message,$data='');
            return;
        }
        $mpUserId    = "43451";
        $paging = []; // 先初始化为空
        $outputColumns = UserNotify::s_metadata()->getFilterOptions();
        $expr = "create_time >= '".date('Y-m-d',time())." 00:00:00' and create_time <= '".date('Y-m-d',time())." 23:59:59'";

        $dbCondition = new \Bluefin\Data\DbCondition($expr);


        $condition     = [$dbCondition, UserNotify::MP_USER_ID => $mpUserId,UserNotify::SEND_STATUS => 'send_finished'];
        $ranking       = [ UserNotify::USER_NOTIFY_ID =>true ];
        $data          = UserNotifyBusiness::getList( $condition, $paging, $ranking, $outputColumns );
        log_debug('data=====',$data);
        $newData = array();
        if(!empty($data))
        {
            foreach($data as $value)
            {
                $appNoticeRead = new AppNoticeReadRecord([AppNoticeReadRecord::APP_ARTICLE_ID=>$value['user_notify_id'],
                    AppNoticeReadRecord::APP_PHONE=>$phone]);
                if($appNoticeRead->isEmpty())
                {
                    $isRead = 0;
                }
                else
                {
                    $isRead = 1;
                }
                $unread_records = self::getUnreadRecords($phone,$mpUserId);
                $value['unreadRecords'] = $unread_records;
                $value['isRead '] = $isRead;
                $newData[] = $value;
            }
            $data = $newData;
        }

        $status = 0;
        $message = '获取成功';
        self::response($status,$message,$data);
    }
    //读取消息接口
    public function noticeRecord()
    {
        $phone    = App::getInstance()->request()->get('phone');
        $app_article_id    = App::getInstance()->request()->get('app_article_id');
        $mpUserId    = App::getInstance()->request()->get('mp_user_id');
        if(empty($phone) || empty($app_article_id) || empty($mpUserId))
        {
            $status = 1;
            $message = "参数为空";
            self::response($status,$message,$data='');
            return;
        }
        $mpUserId    = '43451';
        $app_notice_record = new AppNoticeReadRecord([AppNoticeReadRecord::APP_ARTICLE_ID=>$app_article_id,
            AppNoticeReadRecord::APP_PHONE=>$phone]);
        if($app_notice_record->isEmpty()){
            $app_notice_record = new AppNoticeReadRecord();
            $app_notice_record->setAppArticleID($app_article_id)
                ->setAppPhone($phone)
                ->setNoticeReadTime(date('Ymd',time()))
                ->insert();
        }
        $unread_records = self::getUnreadRecords($phone,$mpUserId);
        $status = 0;
        $message = '查询成功';
        $data = ['isRead'=>1,'unreadRecords'=>$unread_records];
        self::response($status,$message,$data);

    }
    //获取未读消息数量
    public static function getUnreadRecords($phone,$mpUserId)
    {
        $unread_records = 0;
        //获取今天发布了多少条消息
        $expr = "create_time >= '".date('Y-m-d',time())." 00:00:00' and create_time <= '".date('Y-m-d',time())." 23:59:59'";

        $dbCondition = new \Bluefin\Data\DbCondition($expr);


        $condition     = [$dbCondition, UserNotify::MP_USER_ID => $mpUserId,UserNotify::SEND_STATUS => 'send_finished'];

        $count          = UserNotifyBusiness::getCount($condition);
        if($count == 0)
        {
            $unread_records = $count;
        }else
        {
            $condition = [AppNoticeReadRecord::APP_PHONE => $phone,AppNoticeReadRecord::NOTICE_READ_TIME=>date('Ymd',time())];
            $readRecords = AppNoticeReadRecord::fetchCount($condition);
            $unread_records = $count-$readRecords;

        }
        return $unread_records;
    }
    //获取新注册用户欢迎用语
    public function followedContent()
    {
        $mpUserId    = App::getInstance()->request()->get('mp_user_id');
        if(empty($mpUserId))
        {
            $status = 1;
            $message = "参数为空";
            self::response($status,$message,$data='');
            return;
        }
        //测试先写死 mpUserID 15906  在dev上建了一个金管家的模拟公共账号
        $mpUserId = '43451';
        $mpUser = new MpUser([MpUser::MP_USER_ID=>$mpUserId]);
        if(!$mpUser->isEmpty())
        {
            $data = ['followedContent'=>$mpUser->getFollowedContent()];
        }
        else
        {
            $data = ['followedContent'=>''];
        }
        $status = 0;
        $message = '查询成功';
        self::response($status,$message,$data);
    }
    //获取app引导页-图片
    public function albumPic()
    {
        $albumId = App::getInstance()->request()->get('album_id');
        if(empty($albumId))
        {
            $status = 1;
            $message = '';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        //app引导页相册id为31
        $albumId = 40;
        $data = CarouselBusiness::getPictureList($albumId);
        $status = 0;
        $message = '获取成功';
        self::response($status,$message,$data);
    }
    //获取用户地理位置
    public function geographicalPosition()
    {
        $phone    = App::getInstance()->request()->get('phone');
        $latitudeUser    = App::getInstance()->request()->get('latitudeUser');//纬度
        $longitudeUser  = App::getInstance()->request()->get('longitudeUser');//经度
        if(empty($phone) || empty($latitudeUser) || empty($longitudeUser))
        {
            $status = 1;
            $message = '参数为空';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if($appUser->isEmpty())
        {
            $status = 2;
            $message = '未知用户';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        else
        {
            $appUser->setLatitudeuser($latitudeUser)
                ->setLongitudeuser($longitudeUser)
                ->update();
            $status = 0;
            $message = '更新成功';
            $data ='';
            self::response($status,$message,$data);
        }
    }

    //获取用户是否接收push消息
    public function receiveMessage()
    {
        $phone    = App::getInstance()->request()->get('phone');
        $isReceiveMessage = App::getInstance()->request()->get('is_ok');
        if(empty($phone) || empty($isReceiveMessage))
        {
            $status = 1;
            $message = '参数为空';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if($appUser->isEmpty())
        {
            $status = 2;
            $message = '未知用户';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        else
        {
            $appUser->setIsReceiveMessage($isReceiveMessage)->update();
            $status = 0;
            $message = '更新成功';
            $data ='';
            self::response($status,$message,$data);
        }
    }

    //根据用户手机号判断该用户有没有注册
    public function isReg()
    {
        $phone    = App::getInstance()->request()->get('phone');
        if(empty($phone))
        {
            $status = 2;
            $message = '参数为空';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if($appUser->isEmpty())
        {
            $status = 0;
            $message = '未注册';
            $data='';
            self::response($status,$message,$data);
        }
        else
        {
            $status = 1;
            $message = '已注册';
            $data='';
            self::response($status,$message,$data);
        }
    }
    //get user baidu userid and channelid

    public function pushInfo()
    {
        $phone    = App::getInstance()->request()->get('phone');
        $userId    = App::getInstance()->request()->get('userid');
        $channelId = App::getInstance()->request()->get('channelid');
        if(empty($phone) || empty($userId) || empty($channelId))
        {
            $status = 2;
            $message = '参数为空';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        if($appUser->isEmpty())
        {
            $status = 1;
            $message = '该用户不存在';
            $data='';
            self::response($status,$message,$data);
        }
        else
        {
            $appUser->setBaiduUserID($userId)->setBaiduChannelID($channelId)->update();
            $status = 0;
            $message = '更新成功';
            $data='';
            self::response($status,$message,$data);
        }
    }

    //获取游客百度云推送userid
    public function CustomerPushInfo()
    {
        $userId    = App::getInstance()->request()->get('userid');
        $channelId = App::getInstance()->request()->get('channelid');
        if( empty($userId) || empty($channelId))
        {
            $status = 2;
            $message = '参数为空';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        $status = 0;
        $message = '注册成功';
        $appUser = new AppUser([AppUser::BAIDU_USER_ID=>$userId,AppUser::BAIDU_CHANNEL_ID=>$channelId]);
        if($appUser->isEmpty())
        {
            //生成会员号
            $mpUser = new MpUser([MpUser::MP_USER_ID => "43451"]);
            $vipNo =  WxUserBusiness::generateVipNo($mpUser);
            $appUser = new AppUser();
            $phone = '游客'.rand(100000,999999);
            $pwd = '';
            $appUser->setPhone($phone)
                ->setPassword($pwd)
                ->setVipNo($vipNo)
                ->setCreateTime(date('Y-m-d H:i:s',time()))
                ->setLastAccess(date('Y-m-d H:i:s',time()))
                ->insert();

        }
        else
        {
            $appUser->setLastAccess(date('Y-m-d H:i:s',time()))->update();
        }
        $data = '';
        self::response($status,$message,$data);
    }

    /**
     * @param $phone
     * @param $filename
     * @param $data
     * return $head_img
     */
    public function uploadHeadImg()
    {
        $phone    = App::getInstance()->request()->get('phone');
        $file_name =  App::getInstance()->request()->get('filename');
        if(empty($phone))
        {
            $status = 2;
            $message = '参数为空';
            $data='';
            self::response($status,$message,$data);
            return;
        }

        $res = uploadImgBusiness::uploadImg($file_name);
        $status = 0;
        $message = '上传成功';
        $data['head_img'] = $res['img_url'];
        self::response($status,$message,$data);
    }
    //获取省市列表
    public function getPro()
    {
        $province = Province::fetchRows(['*']);
        foreach($province as $key=>$value)
        {
            $arr = ['province_id'=>$value['province_id'],'name'=>$value['name']];
            $newArr[] = $arr;
        }
        $status =0;
        $message = '获取成功';
        self::response($status,$message,$newArr);
    }
    //获取热门城市列表
    public function getHotCity()
    {
        $hotCity = City::fetchRows(['*'],[City::SORT=>1]);
        foreach($hotCity as $key=>$value)
        {
            $arr = ['city_id'=>$value['city_id'],'name'=>$value['name']];
            $newArr[] = $arr;
        }
        $status =0;
        $message = '获取成功';
        self::response($status,$message,$newArr);
    }
    //获取城市列表 new
    public function getNewCity()
    {
        $pro_id    = App::getInstance()->request()->get('pro_id');
        if(empty($pro_id))
        {
            $status = 2;
            $message = '参数为空';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        $city = City::fetchRows(['*'],[City::PROVINCE_ID=>$pro_id]);
        foreach($city as $key=>$value)
        {
            $arr = ['city_id'=>$value['city_id'],'name'=>$value['name']];
            $newArr[] = $arr;
        }
        $status =0;
        $message = '获取成功';
        self::response($status,$message,$newArr);
    }

    //获取区县列表
    public function getArea()
    {
        $city_id    = App::getInstance()->request()->get('city_id');
        if(empty($city_id))
        {
            $status = 2;
            $message = '参数为空';
            $data='';
            self::response($status,$message,$data);
            return;
        }
        $area = Area::fetchRows(['*'],[Area::CITY_ID=>$city_id]);
        foreach($area as $key=>$value)
        {
            $arr = ['area_id'=>$value['area_id'],'name'=>$value['name']];
            $newArr[] = $arr;
        }
        $status =0;
        $message = '获取成功';
        self::response($status,$message,$newArr);
    }


    public static function response($status,$message,$data)
    {
        $result['errno'] = $status;
        $result['msg'] = $message;
        $result['data'] = $data;
        echo  (json_encode($result));
        return;
    }

    public function tlc()
    {
        /*$phone = '18353364515';
        $mpUserId = '21817';
        $count = self::getUnreadRecords($phone,$mpUserId);
        echo $count;*/
        $phone = '18353364515';
        $appUser = new AppUser([AppUser::PHONE=>$phone]);
        $message = '你好，左邻有你';
        //$res = PushMessageBusiness::baiduSendIOSMessage($appUser->getBaiduUserID(),$message);
    }

    public function getNewCommunity()
    {
        $city    = App::getInstance()->request()->get('city');
        $type    = App::getInstance()->request()->get('type');
        log_debug('city============',$city);
        log_debug('type============',$type);
        if(empty($city) || empty($type))
        {
            $status = 1;
            $message = '参数为空';
            $data = '';
            self::response($status,$message,$data);
            return;
        }
        if($type == 1)
        {
            $pie = explode(" ",$city);
            $city_name = $pie[0];
            $area = $pie[1];
        }elseif($type == 2)
        {
            $pie = explode(" ",$city);
            $city_name = $pie[1];
            $area = $pie[2];
        }
        //$communityArr = Community::fetchColumn([Community::NAME],[Community::IS_APP=>1,Community::CITY=>$city]);
        $data = Community::fetchRows(['*'],[Community::VALID=>1,Community::IS_APP=>1,Community::CITY=>$city_name,Community::AREA=>$area]);
        foreach($data as $key=>$value)
        {
            $arr = ['community_id'=>$value['community_id'],'name'=>$value['name'],'mp_user_id'=>$value['mp_user_id']];
            $newArr[] = $arr;
        }
        $status = 0;
        $message = '获取成功';
        self::response($status,$message,$newArr);
    }


} 