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

class Restaurant extends Model
{
    const RESTAURANT_ID = 'restaurant_id';
    const _CREATED_AT = '_created_at';
    const MP_USER_ID = 'mp_user_id';
    const COMMUNITY_ID = 'community_id';
    const TITLE = 'title';
    const COMMENT = 'comment';
    const BOUND_COMMUNITY_ID = 'bound_community_id';



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
                'restaurant',
                'restaurant_id',
                [
                    'restaurant_id' => ['name' => _META_('mp.restaurant.restaurant_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    '_created_at' => ['name' => _META_('mp.restaurant._created_at'), 'type' => 'timestamp', 'required' => true, 'default' => new DbExpr('NOW()'), 'roc' => true, 'rou' => true],
                    'mp_user_id' => ['name' => _META_('mp.restaurant.mp_user_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'community_id' => ['name' => _META_('mp.restaurant.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'title' => ['name' => _META_('mp.restaurant.title'), 'type' => 'text', 'length' => 64, 'required' => true],
                    'comment' => ['name' => _META_('mp.restaurant.comment'), 'type' => 'text'],
                    'bound_community_id' => ['name' => _META_('mp.restaurant.bound_community_id'), 'type' => 'int', 'length' => 10],
                ],
                [
                    'auto_increment_id' => 'restaurant_id',
                    'create_timestamp' => '_created_at',
                    'unique_keys' => [['title', 'community_id']],
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
    public function getRestaurantID()
    {
        return $this->__get(self::RESTAURANT_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return Restaurant
     */
    public function setRestaurantID($value)
    {
        $this->__set(self::RESTAURANT_ID, $value);

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
     * @return Restaurant
     */
    public function setCreatedAt($value)
    {
        $this->__set(self::_CREATED_AT, $value);

        return $this;
    }

    /**
     * Gets 公众账号id
     * @return int
     */
    public function getMpUserID()
    {
        return $this->__get(self::MP_USER_ID);
    }

    /**
     * Sets 公众账号id
     * @param int $value
     * @return Restaurant
     */
    public function setMpUserID($value)
    {
        $this->__set(self::MP_USER_ID, $value);

        return $this;
    }

    /**
     * Gets community.community_id
     * @return int
     */
    public function getCommunityID()
    {
        return $this->__get(self::COMMUNITY_ID);
    }

    /**
     * Sets community.community_id
     * @param int $value
     * @return Restaurant
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets 名称
     * @return string
     */
    public function getTitle()
    {
        return $this->__get(self::TITLE);
    }

    /**
     * Sets 名称
     * @param string $value
     * @return Restaurant
     */
    public function setTitle($value)
    {
        $this->__set(self::TITLE, $value);

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
     * @return Restaurant
     */
    public function setComment($value)
    {
        $this->__set(self::COMMENT, $value);

        return $this;
    }

    /**
     * Gets 绑定小区id
     * @return int
     */
    public function getBoundCommunityID()
    {
        return $this->__get(self::BOUND_COMMUNITY_ID);
    }

    /**
     * Sets 绑定小区id
     * @param int $value
     * @return Restaurant
     */
    public function setBoundCommunityID($value)
    {
        $this->__set(self::BOUND_COMMUNITY_ID, $value);

        return $this;
    }
}
?>