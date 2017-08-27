<?php
//Don't edit this file which is generated by Bluefin Lance.
//You can put custom business logic into a Business class under MP\Business namespace.
namespace MP\Model\Mp;

use Bluefin\App;
use Bluefin\Convention;
use Bluefin\VarText;
use Bluefin\Data\Type;
use Bluefin\Data\Model;
use Bluefin\Data\Database;
use Bluefin\Data\ModelMetadata;
use Bluefin\Data\DbExpr;

class AppUser extends Model
{
    const CREATE_TIME = 'create_time';
    const VIP_NO = 'vip_no';
    const PHONE = 'phone';
    const PASSWORD = 'password';
    const IS_ADMIN = 'is_admin';
    const NICK = 'nick';
    const NAME = 'name';
    const LAST_ACCESS_YMD = 'last_access_ymd';
    const COMMENT = 'comment';
    const IDIOGRAPH = 'idiograph';
    const PROVINCE = 'province';
    const CITY = 'city';
    const COMMUNITY_NAME = 'community_name';
    const ADDRESS = 'address';
    const BIRTH = 'birth';
    const GENDER = 'gender';
    const HEAD_PIC = 'head_pic';
    const MESSAGE_DATE = 'message_date';
    const CARD_ID = 'card_id';
    const EMAIL = 'email';
    const CURRENT_COMMUNITY_ID = 'current_community_id';
    const LATITUDEUSER = 'latitudeUser';
    const LONGITUDEUSER = 'longitudeUser';
    const IS_RECEIVE_MESSAGE = 'is_receive_message';
    const BAIDU_USER_ID = 'baidu_user_id';
    const BAIDU_CHANNEL_ID = 'baidu_channel_id';
    const LAST_ACCESS = 'last_access';
    const IS_QUIT = 'is_quit';



    protected static $__metadata;

    /**
     * @static
     * @return \Bluefin\Data\ModelMetadata
     */
    public static function s_metadata()
    {
        if (!isset(self::$__metadata))
        {
            self::$__metadata = new ModelMetadata(
                'mp',
                'app_user',
                'create_time',
                [
                    'create_time' => ['name' => _META_('mp.app_user.create_time'), 'type' => 'datetime', 'required' => true],
                    'vip_no' => ['name' => _META_('mp.app_user.vip_no'), 'type' => 'int', 'length' => 20, 'db_insert' => true, 'required' => true],
                    'phone' => ['name' => _META_('mp.app_user.phone'), 'type' => 'text', 'length' => 32],
                    'password' => ['name' => _META_('mp.app_user.password'), 'type' => 'text', 'length' => 32, 'required' => true],
                    'is_admin' => ['name' => _META_('mp.app_user.is_admin'), 'type' => 'int', 'length' => 1, 'db_insert' => true, 'required' => true],
                    'nick' => ['name' => _META_('mp.app_user.nick'), 'type' => 'text', 'length' => 64],
                    'name' => ['name' => _META_('mp.app_user.name'), 'type' => 'text', 'length' => 128],
                    'last_access_ymd' => ['name' => _META_('mp.app_user.last_access_ymd'), 'type' => 'int', 'length' => 10, 'db_insert' => true, 'required' => true],
                    'comment' => ['name' => _META_('mp.app_user.comment'), 'type' => 'text', 'length' => 128],
                    'idiograph' => ['name' => _META_('mp.app_user.idiograph'), 'type' => 'text', 'length' => 255],
                    'province' => ['name' => _META_('mp.app_user.province'), 'type' => 'text', 'length' => 128],
                    'city' => ['name' => _META_('mp.app_user.city'), 'type' => 'text', 'length' => 64],
                    'community_name' => ['name' => _META_('mp.app_user.community_name'), 'type' => 'text', 'length' => 255],
                    'address' => ['name' => _META_('mp.app_user.address'), 'type' => 'text', 'length' => 255],
                    'birth' => ['name' => _META_('mp.app_user.birth'), 'type' => 'int', 'length' => 10],
                    'gender' => ['name' => _META_('mp.app_user.gender'), 'type' => 'text', 'length' => 6],
                    'head_pic' => ['name' => _META_('mp.app_user.head_pic'), 'type' => 'url'],
                    'message_date' => ['name' => _META_('mp.app_user.message_date'), 'type' => 'text', 'length' => 16, 'db_insert' => true],
                    'card_id' => ['name' => _META_('mp.app_user.card_id'), 'type' => 'text', 'length' => 255],
                    'email' => ['name' => _META_('mp.app_user.email'), 'type' => 'text', 'length' => 255],
                    'current_community_id' => ['name' => _META_('mp.app_user.current_community_id'), 'type' => 'int', 'length' => 10, 'db_insert' => true, 'required' => true],
                    'latitudeUser' => ['name' => _META_('mp.app_user.latitudeUser'), 'type' => 'text', 'length' => 255],
                    'longitudeUser' => ['name' => _META_('mp.app_user.longitudeUser'), 'type' => 'text', 'length' => 255],
                    'is_receive_message' => ['name' => _META_('mp.app_user.is_receive_message'), 'type' => 'int', 'length' => 1, 'db_insert' => true, 'required' => true],
                    'baidu_user_id' => ['name' => _META_('mp.app_user.baidu_user_id'), 'type' => 'text', 'length' => 32],
                    'baidu_channel_id' => ['name' => _META_('mp.app_user.baidu_channel_id'), 'type' => 'text', 'length' => 32],
                    'last_access' => ['name' => _META_('mp.app_user.last_access'), 'type' => 'datetime'],
                    'is_quit' => ['name' => _META_('mp.app_user.is_quit'), 'type' => 'bool', 'db_insert' => true],
                ],
                [
                    'unique_keys' => [['vip_no']],
                ],
                [
                ],
                [
                ],
                [
                    Model::OP_CREATE => NULL,
                    Model::OP_GET => NULL,
                    Model::OP_UPDATE => NULL,
                    Model::OP_DELETE => NULL,
                ]
            );
        }

        return self::$__metadata;
    }

    public function __construct($condition = null)
    {
        parent::__construct(self::s_metadata());

        if (isset($condition))
        {
            $this->load($condition);
        }
        else
        {
            $this->reset();
        }
    }

    /**
     * Gets 创建时间
     * @return string
     */
    public function getCreateTime()
    {
        return $this->__get(self::CREATE_TIME);
    }

    /**
     * Sets 创建时间
     * @param string $value
     * @return AppUser
     */
    public function setCreateTime($value)
    {
        $this->__set(self::CREATE_TIME, $value);

        return $this;
    }

    /**
     * Gets 会员号
     * @return int
     */
    public function getVipNo()
    {
        return $this->__get(self::VIP_NO);
    }

    /**
     * Sets 会员号
     * @param int $value
     * @return AppUser
     */
    public function setVipNo($value)
    {
        $this->__set(self::VIP_NO, $value);

        return $this;
    }

    /**
     * Gets 注册用户登录手机号
     * @return string
     */
    public function getPhone()
    {
        return $this->__get(self::PHONE);
    }

    /**
     * Sets 注册用户登录手机号
     * @param string $value
     * @return AppUser
     */
    public function setPhone($value)
    {
        $this->__set(self::PHONE, $value);

        return $this;
    }

    /**
     * Gets 注册用户登录密码
     * @return string
     */
    public function getPassword()
    {
        return $this->__get(self::PASSWORD);
    }

    /**
     * Sets 注册用户登录密码
     * @param string $value
     * @return AppUser
     */
    public function setPassword($value)
    {
        $this->__set(self::PASSWORD, $value);

        return $this;
    }

    /**
     * Gets 是否是管理员
     * @return int
     */
    public function getIsAdmin()
    {
        return $this->__get(self::IS_ADMIN);
    }

    /**
     * Sets 是否是管理员
     * @param int $value
     * @return AppUser
     */
    public function setIsAdmin($value)
    {
        $this->__set(self::IS_ADMIN, $value);

        return $this;
    }

    /**
     * Gets 昵称
     * @return string
     */
    public function getNick()
    {
        return $this->__get(self::NICK);
    }

    /**
     * Sets 昵称
     * @param string $value
     * @return AppUser
     */
    public function setNick($value)
    {
        $this->__set(self::NICK, $value);

        return $this;
    }

    /**
     * Gets 姓名
     * @return string
     */
    public function getName()
    {
        return $this->__get(self::NAME);
    }

    /**
     * Sets 姓名
     * @param string $value
     * @return AppUser
     */
    public function setName($value)
    {
        $this->__set(self::NAME, $value);

        return $this;
    }

    /**
     * Gets 上次访问日期
     * @return int
     */
    public function getLastAccessYmd()
    {
        return $this->__get(self::LAST_ACCESS_YMD);
    }

    /**
     * Sets 上次访问日期
     * @param int $value
     * @return AppUser
     */
    public function setLastAccessYmd($value)
    {
        $this->__set(self::LAST_ACCESS_YMD, $value);

        return $this;
    }

    /**
     * Gets 备注
     * @return string
     */
    public function getComment()
    {
        return $this->__get(self::COMMENT);
    }

    /**
     * Sets 备注
     * @param string $value
     * @return AppUser
     */
    public function setComment($value)
    {
        $this->__set(self::COMMENT, $value);

        return $this;
    }

    /**
     * Gets 个人签名
     * @return string
     */
    public function getIdiograph()
    {
        return $this->__get(self::IDIOGRAPH);
    }

    /**
     * Sets 个人签名
     * @param string $value
     * @return AppUser
     */
    public function setIdiograph($value)
    {
        $this->__set(self::IDIOGRAPH, $value);

        return $this;
    }

    /**
     * Gets 所在省份
     * @return string
     */
    public function getProvince()
    {
        return $this->__get(self::PROVINCE);
    }

    /**
     * Sets 所在省份
     * @param string $value
     * @return AppUser
     */
    public function setProvince($value)
    {
        $this->__set(self::PROVINCE, $value);

        return $this;
    }

    /**
     * Gets 所在城市
     * @return string
     */
    public function getCity()
    {
        return $this->__get(self::CITY);
    }

    /**
     * Sets 所在城市
     * @param string $value
     * @return AppUser
     */
    public function setCity($value)
    {
        $this->__set(self::CITY, $value);

        return $this;
    }

    /**
     * Gets 社区名字
     * @return string
     */
    public function getCommunityName()
    {
        return $this->__get(self::COMMUNITY_NAME);
    }

    /**
     * Sets 社区名字
     * @param string $value
     * @return AppUser
     */
    public function setCommunityName($value)
    {
        $this->__set(self::COMMUNITY_NAME, $value);

        return $this;
    }

    /**
     * Gets 地址
     * @return string
     */
    public function getAddress()
    {
        return $this->__get(self::ADDRESS);
    }

    /**
     * Sets 地址
     * @param string $value
     * @return AppUser
     */
    public function setAddress($value)
    {
        $this->__set(self::ADDRESS, $value);

        return $this;
    }

    /**
     * Gets 生日
     * @return int
     */
    public function getBirth()
    {
        return $this->__get(self::BIRTH);
    }

    /**
     * Sets 生日
     * @param int $value
     * @return AppUser
     */
    public function setBirth($value)
    {
        $this->__set(self::BIRTH, $value);

        return $this;
    }

    /**
     * Gets 性别
     * @return string
     */
    public function getGender()
    {
        return $this->__get(self::GENDER);
    }

    /**
     * Sets 性别
     * @param string $value
     * @return AppUser
     */
    public function setGender($value)
    {
        $this->__set(self::GENDER, $value);

        return $this;
    }

    /**
     * Gets 头像
     * @return string
     */
    public function getHeadPic()
    {
        return $this->__get(self::HEAD_PIC);
    }

    /**
     * Sets 头像
     * @param string $value
     * @return AppUser
     */
    public function setHeadPic($value)
    {
        $this->__set(self::HEAD_PIC, $value);

        return $this;
    }

    /**
     * Gets 消息日期
     * @return string
     */
    public function getMessageDate()
    {
        return $this->__get(self::MESSAGE_DATE);
    }

    /**
     * Sets 消息日期
     * @param string $value
     * @return AppUser
     */
    public function setMessageDate($value)
    {
        $this->__set(self::MESSAGE_DATE, $value);

        return $this;
    }

    /**
     * Gets 卡号
     * @return string
     */
    public function getCardID()
    {
        return $this->__get(self::CARD_ID);
    }

    /**
     * Sets 卡号
     * @param string $value
     * @return AppUser
     */
    public function setCardID($value)
    {
        $this->__set(self::CARD_ID, $value);

        return $this;
    }

    /**
     * Gets 邮箱地址
     * @return string
     */
    public function getEmail()
    {
        return $this->__get(self::EMAIL);
    }

    /**
     * Sets 邮箱地址
     * @param string $value
     * @return AppUser
     */
    public function setEmail($value)
    {
        $this->__set(self::EMAIL, $value);

        return $this;
    }

    /**
     * Gets community.community_id
     * @return int
     */
    public function getCurrentCommunityID()
    {
        return $this->__get(self::CURRENT_COMMUNITY_ID);
    }

    /**
     * Sets community.community_id
     * @param int $value
     * @return AppUser
     */
    public function setCurrentCommunityID($value)
    {
        $this->__set(self::CURRENT_COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets 纬度
     * @return string
     */
    public function getLatitudeuser()
    {
        return $this->__get(self::LATITUDEUSER);
    }

    /**
     * Sets 纬度
     * @param string $value
     * @return AppUser
     */
    public function setLatitudeuser($value)
    {
        $this->__set(self::LATITUDEUSER, $value);

        return $this;
    }

    /**
     * Gets 经度
     * @return string
     */
    public function getLongitudeuser()
    {
        return $this->__get(self::LONGITUDEUSER);
    }

    /**
     * Sets 经度
     * @param string $value
     * @return AppUser
     */
    public function setLongitudeuser($value)
    {
        $this->__set(self::LONGITUDEUSER, $value);

        return $this;
    }

    /**
     * Gets 是否接收消息
     * @return int
     */
    public function getIsReceiveMessage()
    {
        return $this->__get(self::IS_RECEIVE_MESSAGE);
    }

    /**
     * Sets 是否接收消息
     * @param int $value
     * @return AppUser
     */
    public function setIsReceiveMessage($value)
    {
        $this->__set(self::IS_RECEIVE_MESSAGE, $value);

        return $this;
    }

    /**
     * Gets 百度userid
     * @return string
     */
    public function getBaiduUserID()
    {
        return $this->__get(self::BAIDU_USER_ID);
    }

    /**
     * Sets 百度userid
     * @param string $value
     * @return AppUser
     */
    public function setBaiduUserID($value)
    {
        $this->__set(self::BAIDU_USER_ID, $value);

        return $this;
    }

    /**
     * Gets 百度channelid
     * @return string
     */
    public function getBaiduChannelID()
    {
        return $this->__get(self::BAIDU_CHANNEL_ID);
    }

    /**
     * Sets 百度channelid
     * @param string $value
     * @return AppUser
     */
    public function setBaiduChannelID($value)
    {
        $this->__set(self::BAIDU_CHANNEL_ID, $value);

        return $this;
    }

    /**
     * Gets 上次访问时间
     * @return string
     */
    public function getLastAccess()
    {
        return $this->__get(self::LAST_ACCESS);
    }

    /**
     * Sets 上次访问时间
     * @param string $value
     * @return AppUser
     */
    public function setLastAccess($value)
    {
        $this->__set(self::LAST_ACCESS, $value);

        return $this;
    }

    /**
     * Gets 是否退出
     * @return bool
     */
    public function getIsQuit()
    {
        return $this->__get(self::IS_QUIT);
    }

    /**
     * Sets 是否退出
     * @param bool $value
     * @return AppUser
     */
    public function setIsQuit($value)
    {
        $this->__set(self::IS_QUIT, $value);

        return $this;
    }
}
?>