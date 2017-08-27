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

class UserLoginRecord extends Model
{
    const USER_LOGIN_RECORD_ID = 'user_login_record_id';
    const _CREATED_AT = '_created_at';
    const IP_ADDRESS = 'ip_address';
    const USER = 'user';
    const TYPE = 'type';

    const WITH_USER = 'user_login_record.user:user.user_id';


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
                'user_login_record',
                'user_login_record_id',
                [
                    'user_login_record_id' => ['name' => _META_('weibotui.user_login_record.user_login_record_id'), 'type' => 'uuid', 'default' => new \Bluefin\Data\Functor\AutoUUID(), 'required' => true, 'rou' => true],
                    '_created_at' => ['name' => _META_('weibotui.user_login_record._created_at'), 'type' => 'timestamp', 'required' => true, 'default' => new DbExpr('NOW()'), 'roc' => true, 'rou' => true],
                    'ip_address' => ['name' => _META_('weibotui.user_login_record.ip_address'), 'type' => 'ipv4', 'default' => new \Bluefin\Data\Functor\VarTextProvider("{{gateway.client_ip}}"), 'required' => true],
                    'user' => ['name' => _META_('weibotui.user_login_record.user'), 'type' => 'int', 'length' => 10, 'min' => 100000, 'default' => new \Bluefin\Data\Functor\VarTextProvider("{{auth.weibotui.user_id}}")],
                    'type' => ['name' => _META_('weibotui.user_login_record.type'), 'type' => 'text', 'max' => 20, 'required' => true, 'enum' => new LoginType(), 'db_insert' => true],
                ],
                [
                    'auto_uuid' => 'user_login_record_id',
                    'create_timestamp' => '_created_at',
                ],
                [
                    'user' => self::WITH_USER,
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
     * Gets uuid
     * @return string
     */
    public function getUserLoginRecordID()
    {
        return $this->__get(self::USER_LOGIN_RECORD_ID);
    }

    /**
     * Sets uuid
     * @param string $value
     * @return UserLoginRecord
     */
    public function setUserLoginRecordID($value)
    {
        $this->__set(self::USER_LOGIN_RECORD_ID, $value);

        return $this;
    }

    /**
     * Gets createdat
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->__get(self::_CREATED_AT);
    }

    /**
     * Sets createdat
     * @param string $value
     * @return UserLoginRecord
     */
    public function setCreatedAt($value)
    {
        $this->__set(self::_CREATED_AT, $value);

        return $this;
    }

    /**
     * Gets 登录地址
     * @return string
     */
    public function getIPAddress()
    {
        return $this->__get(self::IP_ADDRESS);
    }

    /**
     * Sets 登录地址
     * @param string $value
     * @return UserLoginRecord
     */
    public function setIPAddress($value)
    {
        $this->__set(self::IP_ADDRESS, $value);

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
     * @return UserLoginRecord
     */
    public function setUser($value)
    {
        $this->__set(self::USER, $value);

        return $this;
    }

    /**
     * Gets 登录来源类型
     * @return string
     */
    public function getType()
    {
        return $this->__get(self::TYPE);
    }

    /**
     * Gets 登录来源类型 display name
     * @return string
     */
    public function getType_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('type');
        return $option['enum']::getDisplayName($this->__get(self::TYPE));
    }

    /**
     * Sets 登录来源类型
     * @param string $value
     * @return UserLoginRecord
     */
    public function setType($value)
    {
        $this->__set(self::TYPE, $value);

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
}
?>