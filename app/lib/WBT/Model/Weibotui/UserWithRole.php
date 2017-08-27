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

class UserWithRole extends Model
{
    const USER_WITH_ROLE_ID = 'user_with_role_id';
    const _CREATED_AT = '_created_at';
    const USER = 'user';
    const ROLE = 'role';

    const WITH_USER = 'user_with_role.user:user.user_id';


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
                'user_with_role',
                'user_with_role_id',
                [
                    'user_with_role_id' => ['name' => _META_('weibotui.user_with_role.user_with_role_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    '_created_at' => ['name' => _META_('weibotui.user_with_role._created_at'), 'type' => 'timestamp', 'required' => true, 'default' => new DbExpr('NOW()'), 'roc' => true, 'rou' => true],
                    'user' => ['name' => _META_('weibotui.user_with_role.user'), 'type' => 'int', 'length' => 10, 'min' => 100000, 'required' => true],
                    'role' => ['name' => _META_('weibotui.user_with_role.role'), 'type' => 'text', 'max' => 20, 'required' => true, 'enum' => new UserRole(), 'db_insert' => true],
                ],
                [
                    'auto_increment_id' => 'user_with_role_id',
                    'create_timestamp' => '_created_at',
                    'unique_keys' => [['user', 'role']],
                    'triggers' => ['AFTER-UPDATE']
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
     * Gets id
     * @return int
     */
    public function getUserWithRoleID()
    {
        return $this->__get(self::USER_WITH_ROLE_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return UserWithRole
     */
    public function setUserWithRoleID($value)
    {
        $this->__set(self::USER_WITH_ROLE_ID, $value);

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
     * @return UserWithRole
     */
    public function setCreatedAt($value)
    {
        $this->__set(self::_CREATED_AT, $value);

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
     * @return UserWithRole
     */
    public function setUser($value)
    {
        $this->__set(self::USER, $value);

        return $this;
    }

    /**
     * Gets 用户角色
     * @return string
     */
    public function getRole()
    {
        return $this->__get(self::ROLE);
    }

    /**
     * Gets 用户角色 display name
     * @return string
     */
    public function getRole_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('role');
        return $option['enum']::getDisplayName($this->__get(self::ROLE));
    }

    /**
     * Sets 用户角色
     * @param string $value
     * @return UserWithRole
     */
    public function setRole($value)
    {
        $this->__set(self::ROLE, $value);

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

    protected function _afterUpdate()
    {
        App::getInstance()->log()->verbose('UserWithRole::_afterUpdate', 'diag');
        if (\WBT\Business\AuthBusiness::getLoggedInUserId() == $this->getUser())
        {
            \WBT\Business\AuthBusiness::refreshLoggedInUserRoles();
        }
        
    }
}
?>