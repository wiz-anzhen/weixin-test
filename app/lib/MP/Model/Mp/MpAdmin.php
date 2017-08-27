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

class MpAdmin extends Model
{
    const MP_ADMIN_ID = 'mp_admin_id';
    const USERNAME = 'username';
    const MP_USER_ID = 'mp_user_id';
    const COMMENT = 'comment';



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
                'mp_admin',
                'mp_admin_id',
                [
                    'mp_admin_id' => ['name' => _META_('mp.mp_admin.mp_admin_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'username' => ['name' => _META_('mp.mp_admin.username'), 'type' => 'text', 'length' => 128, 'required' => true],
                    'mp_user_id' => ['name' => _META_('mp.mp_admin.mp_user_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'comment' => ['name' => _META_('mp.mp_admin.comment'), 'type' => 'text'],
                ],
                [
                    'auto_increment_id' => 'mp_admin_id',
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
     * Gets id
     * @return int
     */
    public function getMpAdminID()
    {
        return $this->__get(self::MP_ADMIN_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return MpAdmin
     */
    public function setMpAdminID($value)
    {
        $this->__set(self::MP_ADMIN_ID, $value);

        return $this;
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
     * @return MpAdmin
     */
    public function setUsername($value)
    {
        $this->__set(self::USERNAME, $value);

        return $this;
    }

    /**
     * Gets mp_user.mp_user_id
     * @return int
     */
    public function getMpUserID()
    {
        return $this->__get(self::MP_USER_ID);
    }

    /**
     * Sets mp_user.mp_user_id
     * @param int $value
     * @return MpAdmin
     */
    public function setMpUserID($value)
    {
        $this->__set(self::MP_USER_ID, $value);

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
     * @return MpAdmin
     */
    public function setComment($value)
    {
        $this->__set(self::COMMENT, $value);

        return $this;
    }
}
?>