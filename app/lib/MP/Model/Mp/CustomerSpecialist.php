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

class CustomerSpecialist extends Model
{
    const CUSTOMER_SPECIALIST_ID = 'customer_specialist_id';
    const STAFF_ID = 'staff_id';
    const MP_USER_ID = 'mp_user_id';
    const COMMUNITY_ID = 'community_id';
    const VIP_NO = 'vip_no';
    const WX_USER_ID = 'wx_user_id';
    const CUSTOMER_SPECIALIST_GROUP_ID = 'customer_specialist_group_id';
    const NAME = 'name';
    const PHONE = 'phone';
    const COMMENT = 'comment';
    const HOLIDAY = 'holiday';
    const VALID = 'valid';



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
                'customer_specialist',
                'customer_specialist_id',
                [
                    'customer_specialist_id' => ['name' => _META_('mp.customer_specialist.customer_specialist_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'staff_id' => ['name' => _META_('mp.customer_specialist.staff_id'), 'type' => 'text', 'length' => 32, 'required' => true],
                    'mp_user_id' => ['name' => _META_('mp.customer_specialist.mp_user_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'community_id' => ['name' => _META_('mp.customer_specialist.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'vip_no' => ['name' => _META_('mp.customer_specialist.vip_no'), 'type' => 'int', 'length' => 20, 'db_insert' => true],
                    'wx_user_id' => ['name' => _META_('mp.customer_specialist.wx_user_id'), 'type' => 'text', 'length' => 64],
                    'customer_specialist_group_id' => ['name' => _META_('mp.customer_specialist.customer_specialist_group_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'name' => ['name' => _META_('mp.customer_specialist.name'), 'type' => 'text', 'length' => 64, 'required' => true],
                    'phone' => ['name' => _META_('mp.customer_specialist.phone'), 'type' => 'text', 'length' => 64, 'required' => true],
                    'comment' => ['name' => _META_('mp.customer_specialist.comment'), 'type' => 'text', 'length' => 128],
                    'holiday' => ['name' => _META_('mp.customer_specialist.holiday'), 'type' => 'text'],
                    'valid' => ['name' => _META_('mp.customer_specialist.valid'), 'type' => 'bool', 'db_insert' => true, 'required' => true],
                ],
                [
                    'auto_increment_id' => 'customer_specialist_id',
                    'unique_keys' => [['staff_id', 'mp_user_id']],
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
    public function getCustomerSpecialistID()
    {
        return $this->__get(self::CUSTOMER_SPECIALIST_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return CustomerSpecialist
     */
    public function setCustomerSpecialistID($value)
    {
        $this->__set(self::CUSTOMER_SPECIALIST_ID, $value);

        return $this;
    }

    /**
     * Gets 工号
     * @return string
     */
    public function getStaffID()
    {
        return $this->__get(self::STAFF_ID);
    }

    /**
     * Sets 工号
     * @param string $value
     * @return CustomerSpecialist
     */
    public function setStaffID($value)
    {
        $this->__set(self::STAFF_ID, $value);

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
     * @return CustomerSpecialist
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
     * @return CustomerSpecialist
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

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
     * @return CustomerSpecialist
     */
    public function setVipNo($value)
    {
        $this->__set(self::VIP_NO, $value);

        return $this;
    }

    /**
     * Gets 微信用户openid
     * @return string
     */
    public function getWxUserID()
    {
        return $this->__get(self::WX_USER_ID);
    }

    /**
     * Sets 微信用户openid
     * @param string $value
     * @return CustomerSpecialist
     */
    public function setWxUserID($value)
    {
        $this->__set(self::WX_USER_ID, $value);

        return $this;
    }

    /**
     * Gets customer_specialist_group.customer_specialist_group_id
     * @return int
     */
    public function getCustomerSpecialistGroupID()
    {
        return $this->__get(self::CUSTOMER_SPECIALIST_GROUP_ID);
    }

    /**
     * Sets customer_specialist_group.customer_specialist_group_id
     * @param int $value
     * @return CustomerSpecialist
     */
    public function setCustomerSpecialistGroupID($value)
    {
        $this->__set(self::CUSTOMER_SPECIALIST_GROUP_ID, $value);

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
     * @return CustomerSpecialist
     */
    public function setName($value)
    {
        $this->__set(self::NAME, $value);

        return $this;
    }

    /**
     * Gets 电话号码
     * @return string
     */
    public function getPhone()
    {
        return $this->__get(self::PHONE);
    }

    /**
     * Sets 电话号码
     * @param string $value
     * @return CustomerSpecialist
     */
    public function setPhone($value)
    {
        $this->__set(self::PHONE, $value);

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
     * @return CustomerSpecialist
     */
    public function setComment($value)
    {
        $this->__set(self::COMMENT, $value);

        return $this;
    }

    /**
     * Gets 休假日期
     * @return string
     */
    public function getHoliday()
    {
        return $this->__get(self::HOLIDAY);
    }

    /**
     * Sets 休假日期
     * @param string $value
     * @return CustomerSpecialist
     */
    public function setHoliday($value)
    {
        $this->__set(self::HOLIDAY, $value);

        return $this;
    }

    /**
     * Gets 是否有效
     * @return bool
     */
    public function getValid()
    {
        return $this->__get(self::VALID);
    }

    /**
     * Sets 是否有效
     * @param bool $value
     * @return CustomerSpecialist
     */
    public function setValid($value)
    {
        $this->__set(self::VALID, $value);

        return $this;
    }
}
?>