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

class Order extends Model
{
    const ORDER_ID = 'order_id';
    const MP_USER_ID = 'mp_user_id';
    const COMMUNITY_ID = 'community_id';
    const WX_USER_ID = 'wx_user_id';
    const COMMENT = 'comment';
    const CUSTOMER_NAME = 'customer_name';
    const TEL = 'tel';
    const ADDRESS = 'address';
    const TOTAL_PRICE = 'total_price';
    const TOTAL_NUM = 'total_num';
    const CREATE_TIME = 'create_time';
    const FINISH_TIME = 'finish_time';
    const CS_ID = 'cs_id';
    const CS_GROUP_ID = 'cs_group_id';
    const PAY_FINISHED = 'pay_finished';
    const STORE_TYPE = 'store_type';
    const STATUS = 'status';
    const PAY_METHOD = 'pay_method';
    const REASON = 'reason';



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
                'order',
                'order_id',
                [
                    'order_id' => ['name' => _META_('mp.order.order_id'), 'type' => 'text', 'length' => 32, 'required' => true],
                    'mp_user_id' => ['name' => _META_('mp.order.mp_user_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'community_id' => ['name' => _META_('mp.order.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'wx_user_id' => ['name' => _META_('mp.order.wx_user_id'), 'type' => 'text', 'length' => 64, 'required' => true],
                    'comment' => ['name' => _META_('mp.order.comment'), 'type' => 'text', 'length' => 255],
                    'customer_name' => ['name' => _META_('mp.order.customer_name'), 'type' => 'text', 'length' => 64],
                    'tel' => ['name' => _META_('mp.order.tel'), 'type' => 'text', 'length' => 64],
                    'address' => ['name' => _META_('mp.order.address'), 'type' => 'text', 'length' => 255],
                    'total_price' => ['name' => _META_('mp.order.total_price'), 'type' => 'money', 'precision' => 2, 'required' => true],
                    'total_num' => ['name' => _META_('mp.order.total_num'), 'type' => 'int', 'length' => 10, 'db_insert' => true, 'required' => true],
                    'create_time' => ['name' => _META_('mp.order.create_time'), 'type' => 'datetime', 'required' => true],
                    'finish_time' => ['name' => _META_('mp.order.finish_time'), 'type' => 'datetime'],
                    'cs_id' => ['name' => _META_('mp.order.cs_id'), 'type' => 'int', 'length' => 10],
                    'cs_group_id' => ['name' => _META_('mp.order.cs_group_id'), 'type' => 'int', 'length' => 10],
                    'pay_finished' => ['name' => _META_('mp.order.pay_finished'), 'type' => 'bool', 'db_insert' => true, 'required' => true],
                    'store_type' => ['name' => _META_('mp.order.store_type'), 'type' => 'text', 'length' => 32],
                    'status' => ['name' => _META_('mp.order.status'), 'type' => 'text', 'max' => 32, 'required' => true, 'enum' => new OrderStatus(), 'db_insert' => true],
                    'pay_method' => ['name' => _META_('mp.order.pay_method'), 'type' => 'text', 'max' => 32, 'enum' => new PayMethod(), 'db_insert' => true],
                    'reason' => ['name' => _META_('mp.order.reason'), 'type' => 'text', 'max' => 32, 'enum' => new ReasonType(), 'db_insert' => true],
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
     * Gets 订单号
     * @return string
     */
    public function getOrderID()
    {
        return $this->__get(self::ORDER_ID);
    }

    /**
     * Sets 订单号
     * @param string $value
     * @return Order
     */
    public function setOrderID($value)
    {
        $this->__set(self::ORDER_ID, $value);

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
     * @return Order
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
     * @return Order
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets 微信用户id
     * @return string
     */
    public function getWxUserID()
    {
        return $this->__get(self::WX_USER_ID);
    }

    /**
     * Sets 微信用户id
     * @param string $value
     * @return Order
     */
    public function setWxUserID($value)
    {
        $this->__set(self::WX_USER_ID, $value);

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
     * @return Order
     */
    public function setComment($value)
    {
        $this->__set(self::COMMENT, $value);

        return $this;
    }

    /**
     * Gets 客户姓名
     * @return string
     */
    public function getCustomerName()
    {
        return $this->__get(self::CUSTOMER_NAME);
    }

    /**
     * Sets 客户姓名
     * @param string $value
     * @return Order
     */
    public function setCustomerName($value)
    {
        $this->__set(self::CUSTOMER_NAME, $value);

        return $this;
    }

    /**
     * Gets 电话
     * @return string
     */
    public function getTel()
    {
        return $this->__get(self::TEL);
    }

    /**
     * Sets 电话
     * @param string $value
     * @return Order
     */
    public function setTel($value)
    {
        $this->__set(self::TEL, $value);

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
     * @return Order
     */
    public function setAddress($value)
    {
        $this->__set(self::ADDRESS, $value);

        return $this;
    }

    /**
     * Gets 订单总价
     * @return float
     */
    public function getTotalPrice()
    {
        return $this->__get(self::TOTAL_PRICE);
    }

    /**
     * Sets 订单总价
     * @param float $value
     * @return Order
     */
    public function setTotalPrice($value)
    {
        $this->__set(self::TOTAL_PRICE, $value);

        return $this;
    }

    /**
     * Gets 订单总数量
     * @return int
     */
    public function getTotalNum()
    {
        return $this->__get(self::TOTAL_NUM);
    }

    /**
     * Sets 订单总数量
     * @param int $value
     * @return Order
     */
    public function setTotalNum($value)
    {
        $this->__set(self::TOTAL_NUM, $value);

        return $this;
    }

    /**
     * Gets 订单创建时间
     * @return string
     */
    public function getCreateTime()
    {
        return $this->__get(self::CREATE_TIME);
    }

    /**
     * Sets 订单创建时间
     * @param string $value
     * @return Order
     */
    public function setCreateTime($value)
    {
        $this->__set(self::CREATE_TIME, $value);

        return $this;
    }

    /**
     * Gets 交易完成时间
     * @return string
     */
    public function getFinishTime()
    {
        return $this->__get(self::FINISH_TIME);
    }

    /**
     * Sets 交易完成时间
     * @param string $value
     * @return Order
     */
    public function setFinishTime($value)
    {
        $this->__set(self::FINISH_TIME, $value);

        return $this;
    }

    /**
     * Gets 客服专员id
     * @return int
     */
    public function getCsID()
    {
        return $this->__get(self::CS_ID);
    }

    /**
     * Sets 客服专员id
     * @param int $value
     * @return Order
     */
    public function setCsID($value)
    {
        $this->__set(self::CS_ID, $value);

        return $this;
    }

    /**
     * Gets 客服专员分组id
     * @return int
     */
    public function getCsGroupID()
    {
        return $this->__get(self::CS_GROUP_ID);
    }

    /**
     * Sets 客服专员分组id
     * @param int $value
     * @return Order
     */
    public function setCsGroupID($value)
    {
        $this->__set(self::CS_GROUP_ID, $value);

        return $this;
    }

    /**
     * Gets 支付状态
     * @return bool
     */
    public function getPayFinished()
    {
        return $this->__get(self::PAY_FINISHED);
    }

    /**
     * Sets 支付状态
     * @param bool $value
     * @return Order
     */
    public function setPayFinished($value)
    {
        $this->__set(self::PAY_FINISHED, $value);

        return $this;
    }

    /**
     * Gets 商城类型
     * @return string
     */
    public function getStoreType()
    {
        return $this->__get(self::STORE_TYPE);
    }

    /**
     * Sets 商城类型
     * @param string $value
     * @return Order
     */
    public function setStoreType($value)
    {
        $this->__set(self::STORE_TYPE, $value);

        return $this;
    }

    /**
     * Gets 交易状态
     * @return string
     */
    public function getStatus()
    {
        return $this->__get(self::STATUS);
    }

    /**
     * Gets 交易状态 display name
     * @return string
     */
    public function getStatus_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('status');
        return $option['enum']::getDisplayName($this->__get(self::STATUS));
    }

    /**
     * Sets 交易状态
     * @param string $value
     * @return Order
     */
    public function setStatus($value)
    {
        $this->__set(self::STATUS, $value);

        return $this;
    }

    /**
     * Gets 付款方式
     * @return string
     */
    public function getPayMethod()
    {
        return $this->__get(self::PAY_METHOD);
    }

    /**
     * Gets 付款方式 display name
     * @return string
     */
    public function getPayMethod_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('pay_method');
        return $option['enum']::getDisplayName($this->__get(self::PAY_METHOD));
    }

    /**
     * Sets 付款方式
     * @param string $value
     * @return Order
     */
    public function setPayMethod($value)
    {
        $this->__set(self::PAY_METHOD, $value);

        return $this;
    }

    /**
     * Gets 原因
     * @return string
     */
    public function getReason()
    {
        return $this->__get(self::REASON);
    }

    /**
     * Gets 原因 display name
     * @return string
     */
    public function getReason_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('reason');
        return $option['enum']::getDisplayName($this->__get(self::REASON));
    }

    /**
     * Sets 原因
     * @param string $value
     * @return Order
     */
    public function setReason($value)
    {
        $this->__set(self::REASON, $value);

        return $this;
    }
}
?>