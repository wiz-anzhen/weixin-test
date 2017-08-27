<?php
//Don't edit this file which is generated by Bluefin Lance.
//You can put custom business logic into a Business class under WBT\Business namespace.
namespace WBT\Model\Weibotui;

use Bluefin\App;
use Bluefin\Convention;
use Bluefin\VarText;
use Bluefin\Data\Type;
use Bluefin\Data\Model;
use Bluefin\Data\Database;
use Bluefin\Data\ModelMetadata;
use Bluefin\Data\DbExpr;

class PersonalProfile extends Model
{
    const PERSONAL_PROFILE_ID = 'personal_profile_id';
    const _UPDATED_AT = '_updated_at';
    const _IS_DELETED = '_is_deleted';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const DISPLAY_NAME = 'display_name';
    const NICK_NAME = 'nick_name';
    const PHOTO = 'photo';
    const AVATAR = 'avatar';
    const EMAIL = 'email';
    const EMAIL_VERIFIED = 'email_verified';
    const ID_NO = 'id_no';
    const BIRTHDAY = 'birthday';
    const MOBILE = 'mobile';
    const MOBILE_VERIFIED = 'mobile_verified';
    const HOME_PHONE = 'home_phone';
    const OFFICE_PHONE = 'office_phone';
    const HOMEPAGE = 'homepage';
    const QQ = 'qq';
    const DESCRIPTION = 'description';
    const EXTRA = 'extra';
    const USER = 'user';
    const GENDER = 'gender';
    const ADDRESS = 'address';
    const NAME_ORDER = 'name_order';

    const WITH_USER = 'personal_profile.user:user.user_id';
    const WITH_ADDRESS = 'personal_profile.address:address.address_id';


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
                'weibotui',
                'personal_profile',
                'personal_profile_id',
                [
                    'personal_profile_id' => ['name' => _META_('weibotui.personal_profile.personal_profile_id'), 'type' => 'uuid', 'default' => new \Bluefin\Data\Functor\AutoUUID(), 'required' => true, 'rou' => true],
                    '_updated_at' => ['name' => _META_('weibotui.personal_profile._updated_at'), 'type' => 'timestamp', 'required' => true, 'db_insert' => true, 'roc' => true, 'rou' => true],
                    '_is_deleted' => ['name' => _META_('weibotui.personal_profile._is_deleted'), 'type' => 'bool', 'required' => true, 'db_insert' => true, 'roc' => true, 'rou' => true],
                    'first_name' => ['name' => _META_('weibotui.personal_profile.first_name'), 'type' => 'text', 'min' => 1, 'max' => 20],
                    'last_name' => ['name' => _META_('weibotui.personal_profile.last_name'), 'type' => 'text', 'min' => 1, 'max' => 20],
                    'display_name' => ['name' => _META_('weibotui.personal_profile.display_name'), 'type' => 'text', 'min' => 1, 'max' => 40],
                    'nick_name' => ['name' => _META_('weibotui.personal_profile.nick_name'), 'type' => 'text', 'min' => 1, 'max' => 20],
                    'photo' => ['name' => _META_('weibotui.personal_profile.photo'), 'type' => 'path'],
                    'avatar' => ['name' => _META_('weibotui.personal_profile.avatar'), 'type' => 'path'],
                    'email' => ['name' => _META_('weibotui.personal_profile.email'), 'type' => 'email'],
                    'email_verified' => ['name' => _META_('weibotui.personal_profile.email_verified'), 'type' => 'bool', 'db_insert' => true, 'required' => true],
                    'id_no' => ['name' => _META_('weibotui.personal_profile.id_no'), 'type' => 'text', 'max' => 20],
                    'birthday' => ['name' => _META_('weibotui.personal_profile.birthday'), 'type' => 'date'],
                    'mobile' => ['name' => _META_('weibotui.personal_profile.mobile'), 'type' => 'phone'],
                    'mobile_verified' => ['name' => _META_('weibotui.personal_profile.mobile_verified'), 'type' => 'bool', 'db_insert' => true, 'required' => true],
                    'home_phone' => ['name' => _META_('weibotui.personal_profile.home_phone'), 'type' => 'phone'],
                    'office_phone' => ['name' => _META_('weibotui.personal_profile.office_phone'), 'type' => 'phone'],
                    'homepage' => ['name' => _META_('weibotui.personal_profile.homepage'), 'type' => 'url'],
                    'qq' => ['name' => _META_('weibotui.personal_profile.qq'), 'type' => 'digits', 'max' => 20],
                    'description' => ['name' => _META_('weibotui.personal_profile.description'), 'type' => 'text'],
                    'extra' => ['name' => _META_('weibotui.personal_profile.extra'), 'type' => 'json'],
                    'user' => ['name' => _META_('weibotui.personal_profile.user'), 'type' => 'int', 'length' => 10, 'min' => 100000, 'required' => true],
                    'gender' => ['name' => _META_('weibotui.personal_profile.gender'), 'type' => 'text', 'max' => 20, 'enum' => new Gender(), 'db_insert' => true],
                    'address' => ['name' => _META_('weibotui.personal_profile.address'), 'type' => 'int', 'length' => 10, 'min' => 1],
                    'name_order' => ['name' => _META_('weibotui.personal_profile.name_order'), 'type' => 'text', 'max' => 20, 'required' => true, 'db_insert' => true, 'enum' => new PersonalNameOrder()],
                ],
                [
                    'auto_uuid' => 'personal_profile_id',
                    'update_timestamp' => '_updated_at',
                    'logical_deletion' => '_is_deleted',
                    'owner_field' => 'user',
                    'unique_keys' => [['id_no']],
                    'triggers' => ['AFTER-UPDATE']
                ],
                [
                    'user' => self::WITH_USER,
                    'address' => self::WITH_ADDRESS,
                ],
                [
                ],
                [
                    Model::OP_CREATE => NULL,
                    Model::OP_GET => NULL,
                    Model::OP_UPDATE => ['weibotui' => ['*any*']],
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

    public function owner()
    {
        return $this->__get('user');
    }

    /**
     * Gets uuid
     * @return string
     */
    public function getPersonalProfileID()
    {
        return $this->__get(self::PERSONAL_PROFILE_ID);
    }

    /**
     * Sets uuid
     * @param string $value
     * @return PersonalProfile
     */
    public function setPersonalProfileID($value)
    {
        $this->__set(self::PERSONAL_PROFILE_ID, $value);

        return $this;
    }

    /**
     * Gets updatedat
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->__get(self::_UPDATED_AT);
    }

    /**
     * Sets updatedat
     * @param string $value
     * @return PersonalProfile
     */
    public function setUpdatedAt($value)
    {
        $this->__set(self::_UPDATED_AT, $value);

        return $this;
    }

    /**
     * Gets isdeleted
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->__get(self::_IS_DELETED);
    }

    /**
     * Sets isdeleted
     * @param bool $value
     * @return PersonalProfile
     */
    public function setIsDeleted($value)
    {
        $this->__set(self::_IS_DELETED, $value);

        return $this;
    }

    /**
     * Gets 名
     * @return string
     */
    public function getFirstName()
    {
        return $this->__get(self::FIRST_NAME);
    }

    /**
     * Sets 名
     * @param string $value
     * @return PersonalProfile
     */
    public function setFirstName($value)
    {
        $this->__set(self::FIRST_NAME, $value);

        return $this;
    }

    /**
     * Gets 姓
     * @return string
     */
    public function getLastName()
    {
        return $this->__get(self::LAST_NAME);
    }

    /**
     * Sets 姓
     * @param string $value
     * @return PersonalProfile
     */
    public function setLastName($value)
    {
        $this->__set(self::LAST_NAME, $value);

        return $this;
    }

    /**
     * Gets 姓名
     * @return string
     */
    public function getDisplayName()
    {
        return $this->__get(self::DISPLAY_NAME);
    }

    /**
     * Sets 姓名
     * @param string $value
     * @return PersonalProfile
     */
    public function setDisplayName($value)
    {
        $this->__set(self::DISPLAY_NAME, $value);

        return $this;
    }

    /**
     * Gets 昵称
     * @return string
     */
    public function getNickName()
    {
        return $this->__get(self::NICK_NAME);
    }

    /**
     * Sets 昵称
     * @param string $value
     * @return PersonalProfile
     */
    public function setNickName($value)
    {
        $this->__set(self::NICK_NAME, $value);

        return $this;
    }

    /**
     * Gets 相片
     * @return string
     */
    public function getPhoto()
    {
        return $this->__get(self::PHOTO);
    }

    /**
     * Sets 相片
     * @param string $value
     * @return PersonalProfile
     */
    public function setPhoto($value)
    {
        $this->__set(self::PHOTO, $value);

        return $this;
    }

    /**
     * Gets 头像
     * @return string
     */
    public function getAvatar()
    {
        return $this->__get(self::AVATAR);
    }

    /**
     * Sets 头像
     * @param string $value
     * @return PersonalProfile
     */
    public function setAvatar($value)
    {
        $this->__set(self::AVATAR, $value);

        return $this;
    }

    /**
     * Gets 邮件地址
     * @return string
     */
    public function getEmail()
    {
        return $this->__get(self::EMAIL);
    }

    /**
     * Sets 邮件地址
     * @param string $value
     * @return PersonalProfile
     */
    public function setEmail($value)
    {
        $this->__set(self::EMAIL, $value);

        return $this;
    }

    /**
     * Gets 邮件地址验证
     * @return bool
     */
    public function getEmailVerified()
    {
        return $this->__get(self::EMAIL_VERIFIED);
    }

    /**
     * Sets 邮件地址验证
     * @param bool $value
     * @return PersonalProfile
     */
    public function setEmailVerified($value)
    {
        $this->__set(self::EMAIL_VERIFIED, $value);

        return $this;
    }

    /**
     * Gets 身份证
     * @return string
     */
    public function getIDNo()
    {
        return $this->__get(self::ID_NO);
    }

    /**
     * Sets 身份证
     * @param string $value
     * @return PersonalProfile
     */
    public function setIDNo($value)
    {
        $this->__set(self::ID_NO, $value);

        return $this;
    }

    /**
     * Gets 生日
     * @return string
     */
    public function getBirthday()
    {
        return $this->__get(self::BIRTHDAY);
    }

    /**
     * Sets 生日
     * @param string $value
     * @return PersonalProfile
     */
    public function setBirthday($value)
    {
        $this->__set(self::BIRTHDAY, $value);

        return $this;
    }

    /**
     * Gets 手机
     * @return string
     */
    public function getMobile()
    {
        return $this->__get(self::MOBILE);
    }

    /**
     * Sets 手机
     * @param string $value
     * @return PersonalProfile
     */
    public function setMobile($value)
    {
        $this->__set(self::MOBILE, $value);

        return $this;
    }

    /**
     * Gets 手机验证
     * @return bool
     */
    public function getMobileVerified()
    {
        return $this->__get(self::MOBILE_VERIFIED);
    }

    /**
     * Sets 手机验证
     * @param bool $value
     * @return PersonalProfile
     */
    public function setMobileVerified($value)
    {
        $this->__set(self::MOBILE_VERIFIED, $value);

        return $this;
    }

    /**
     * Gets 家庭电话
     * @return string
     */
    public function getHomePhone()
    {
        return $this->__get(self::HOME_PHONE);
    }

    /**
     * Sets 家庭电话
     * @param string $value
     * @return PersonalProfile
     */
    public function setHomePhone($value)
    {
        $this->__set(self::HOME_PHONE, $value);

        return $this;
    }

    /**
     * Gets 办公电话
     * @return string
     */
    public function getOfficePhone()
    {
        return $this->__get(self::OFFICE_PHONE);
    }

    /**
     * Sets 办公电话
     * @param string $value
     * @return PersonalProfile
     */
    public function setOfficePhone($value)
    {
        $this->__set(self::OFFICE_PHONE, $value);

        return $this;
    }

    /**
     * Gets 个人主页
     * @return string
     */
    public function getHomepage()
    {
        return $this->__get(self::HOMEPAGE);
    }

    /**
     * Sets 个人主页
     * @param string $value
     * @return PersonalProfile
     */
    public function setHomepage($value)
    {
        $this->__set(self::HOMEPAGE, $value);

        return $this;
    }

    /**
     * Gets qq
     * @return string
     */
    public function getQQ()
    {
        return $this->__get(self::QQ);
    }

    /**
     * Sets qq
     * @param string $value
     * @return PersonalProfile
     */
    public function setQQ($value)
    {
        $this->__set(self::QQ, $value);

        return $this;
    }

    /**
     * Gets 个人简介
     * @return string
     */
    public function getDescription()
    {
        return $this->__get(self::DESCRIPTION);
    }

    /**
     * Sets 个人简介
     * @param string $value
     * @return PersonalProfile
     */
    public function setDescription($value)
    {
        $this->__set(self::DESCRIPTION, $value);

        return $this;
    }

    /**
     * Gets 其他
     * @return string
     */
    public function getExtra()
    {
        return $this->__get(self::EXTRA);
    }

    /**
     * Sets 其他
     * @param string $value
     * @return PersonalProfile
     */
    public function setExtra($value)
    {
        $this->__set(self::EXTRA, $value);

        return $this;
    }

    /**
     * Gets 用户
     * @return int
     */
    public function getUser()
    {
        return $this->__get(self::USER);
    }

    /**
     * Sets 用户
     * @param int $value
     * @return PersonalProfile
     */
    public function setUser($value)
    {
        $this->__set(self::USER, $value);

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
     * Gets 性别 display name
     * @return string
     */
    public function getGender_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('gender');
        return $option['enum']::getDisplayName($this->__get(self::GENDER));
    }

    /**
     * Sets 性别
     * @param string $value
     * @return PersonalProfile
     */
    public function setGender($value)
    {
        $this->__set(self::GENDER, $value);

        return $this;
    }

    /**
     * Gets 联系地址
     * @return int
     */
    public function getAddress()
    {
        return $this->__get(self::ADDRESS);
    }

    /**
     * Sets 联系地址
     * @param int $value
     * @return PersonalProfile
     */
    public function setAddress($value)
    {
        $this->__set(self::ADDRESS, $value);

        return $this;
    }

    /**
     * Gets 姓名顺序
     * @return string
     */
    public function getNameOrder()
    {
        return $this->__get(self::NAME_ORDER);
    }

    /**
     * Gets 姓名顺序 display name
     * @return string
     */
    public function getNameOrder_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('name_order');
        return $option['enum']::getDisplayName($this->__get(self::NAME_ORDER));
    }

    /**
     * Sets 姓名顺序
     * @param string $value
     * @return PersonalProfile
     */
    public function setNameOrder($value)
    {
        $this->__set(self::NAME_ORDER, $value);

        return $this;
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\User
     */
    public function getUser_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\User();
        }

        if (isset($this->_links['user']))
        {
            return $this->_links['user'];
        }

        return ($this->_links['user'] = new \WBT\Model\Weibotui\User($this->getUser()));
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\Address
     */
    public function getAddress_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\Address();
        }

        if (isset($this->_links['address']))
        {
            return $this->_links['address'];
        }

        return ($this->_links['address'] = new \WBT\Model\Weibotui\Address($this->getAddress()));
    }

    protected function _afterUpdate()
    {
        App::getInstance()->log()->verbose('PersonalProfile::_afterUpdate', 'diag');
        if (\WBT\Business\AuthBusiness::getLoggedInUserId() == $this->getUser())
        {
            \WBT\Business\AuthBusiness::refreshLoggedInProfile();
        }
        
    }
}
?>