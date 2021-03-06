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

class SuperAdmin extends Model
{
    const USERNAME = 'username';
    const COMMENT = 'comment';
    const HAS_DELETE_POWER = 'has_delete_power';



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
                'super_admin',
                'username',
                [
                    'username' => ['name' => _META_('mp.super_admin.username'), 'type' => 'text', 'length' => 128, 'required' => true],
                    'comment' => ['name' => _META_('mp.super_admin.comment'), 'type' => 'text'],
                    'has_delete_power' => ['name' => _META_('mp.super_admin.has_delete_power'), 'type' => 'int', 'length' => 1, 'db_insert' => true, 'required' => true],
                ],
                [
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
     * Gets user.username
     * @return string
     */
    public function getUsername()
    {
        return $this->__get(self::USERNAME);
    }

    /**
     * Sets user.username
     * @param string $value
     * @return SuperAdmin
     */
    public function setUsername($value)
    {
        $this->__set(self::USERNAME, $value);

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
     * @return SuperAdmin
     */
    public function setComment($value)
    {
        $this->__set(self::COMMENT, $value);

        return $this;
    }

    /**
     * Gets 删除公众账号权限
     * @return int
     */
    public function getHasDeletePower()
    {
        return $this->__get(self::HAS_DELETE_POWER);
    }

    /**
     * Sets 删除公众账号权限
     * @param int $value
     * @return SuperAdmin
     */
    public function setHasDeletePower($value)
    {
        $this->__set(self::HAS_DELETE_POWER, $value);

        return $this;
    }
}
?>