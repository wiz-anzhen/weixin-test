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

class Payout extends Model
{
    const SERIAL_NO = 'serial_no';
    const VENDOR_NO = 'vendor_no';
    const TOTAL_AMOUNT = 'total_amount';
    const TYPE = 'type';
    const PAYMENT_METHOD = 'payment_method';
    const STATUS = 'status';
    const ONGOING_TIME = 'ongoing_time';
    const DONE_TIME = 'done_time';
    const FAILED_TIME = 'failed_time';
    const CANCELLED_TIME = 'cancelled_time';
    const STATUS_LOG = 'status_log';



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
                'payout',
                'serial_no',
                [
                    'serial_no' => ['name' => _META_('weibotui.payout.serial_no'), 'type' => 'text', 'length' => 20, 'required' => true],
                    'vendor_no' => ['name' => _META_('weibotui.payout.vendor_no'), 'type' => 'text', 'length' => 32],
                    'total_amount' => ['name' => _META_('weibotui.payout.total_amount'), 'type' => 'money', 'precision' => 2, 'required' => true],
                    'type' => ['name' => _META_('weibotui.payout.type'), 'type' => 'text', 'max' => 20, 'required' => true, 'enum' => new PayoutType(), 'db_insert' => true],
                    'payment_method' => ['name' => _META_('weibotui.payout.payment_method'), 'type' => 'text', 'max' => 20, 'required' => true, 'enum' => new PaymentMethod(), 'db_insert' => true],
                    'status' => ['name' => _META_('weibotui.payout.status'), 'type' => 'idname', 'required' => true, 'state' => new TransactionStatus(), 'db_insert' => true],
                    'ongoing_time' => ['name' => _META_('weibotui.payout.ongoing_time'), 'type' => 'datetime'],
                    'done_time' => ['name' => _META_('weibotui.payout.done_time'), 'type' => 'datetime'],
                    'failed_time' => ['name' => _META_('weibotui.payout.failed_time'), 'type' => 'datetime'],
                    'cancelled_time' => ['name' => _META_('weibotui.payout.cancelled_time'), 'type' => 'datetime'],
                    'status_log' => ['name' => _META_('weibotui.payout.status_log'), 'type' => 'text', 'max' => 1000, 'default' => 'ongoing'],
                ],
                [
                    'has_states' => 'status',
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
     * Gets 流水号
     * @return string
     */
    public function getSerialNo()
    {
        return $this->__get(self::SERIAL_NO);
    }

    /**
     * Sets 流水号
     * @param string $value
     * @return Payout
     */
    public function setSerialNo($value)
    {
        $this->__set(self::SERIAL_NO, $value);

        return $this;
    }

    /**
     * Gets 交易号
     * @return string
     */
    public function getVendorNo()
    {
        return $this->__get(self::VENDOR_NO);
    }

    /**
     * Sets 交易号
     * @param string $value
     * @return Payout
     */
    public function setVendorNo($value)
    {
        $this->__set(self::VENDOR_NO, $value);

        return $this;
    }

    /**
     * Gets 总金额
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->__get(self::TOTAL_AMOUNT);
    }

    /**
     * Sets 总金额
     * @param float $value
     * @return Payout
     */
    public function setTotalAmount($value)
    {
        $this->__set(self::TOTAL_AMOUNT, $value);

        return $this;
    }

    /**
     * Gets 支出类型
     * @return string
     */
    public function getType()
    {
        return $this->__get(self::TYPE);
    }

    /**
     * Gets 支出类型 display name
     * @return string
     */
    public function getType_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('type');
        return $option['enum']::getDisplayName($this->__get(self::TYPE));
    }

    /**
     * Sets 支出类型
     * @param string $value
     * @return Payout
     */
    public function setType($value)
    {
        $this->__set(self::TYPE, $value);

        return $this;
    }

    /**
     * Gets 支付方式
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->__get(self::PAYMENT_METHOD);
    }

    /**
     * Gets 支付方式 display name
     * @return string
     */
    public function getPaymentMethod_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('payment_method');
        return $option['enum']::getDisplayName($this->__get(self::PAYMENT_METHOD));
    }

    /**
     * Sets 支付方式
     * @param string $value
     * @return Payout
     */
    public function setPaymentMethod($value)
    {
        $this->__set(self::PAYMENT_METHOD, $value);

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
    public function getStatus_StateValue()
    {
        $option = $this->metadata()->getFilterOption('status');
        return $option['state']::getDisplayName($this->__get(self::STATUS));
    }

    /**
     * Sets 交易状态
     * @param string $value
     * @return Payout
     */
    public function setStatus($value)
    {
        $this->__set(self::STATUS, $value);

        return $this;
    }

    /**
     * Gets 进行中时间
     * @return string
     */
    public function getOngoingTime()
    {
        return $this->__get(self::ONGOING_TIME);
    }

    /**
     * Sets 进行中时间
     * @param string $value
     * @return Payout
     */
    public function setOngoingTime($value)
    {
        $this->__set(self::ONGOING_TIME, $value);

        return $this;
    }

    /**
     * Gets 已完成时间
     * @return string
     */
    public function getDoneTime()
    {
        return $this->__get(self::DONE_TIME);
    }

    /**
     * Sets 已完成时间
     * @param string $value
     * @return Payout
     */
    public function setDoneTime($value)
    {
        $this->__set(self::DONE_TIME, $value);

        return $this;
    }

    /**
     * Gets 失败时间
     * @return string
     */
    public function getFailedTime()
    {
        return $this->__get(self::FAILED_TIME);
    }

    /**
     * Sets 失败时间
     * @param string $value
     * @return Payout
     */
    public function setFailedTime($value)
    {
        $this->__set(self::FAILED_TIME, $value);

        return $this;
    }

    /**
     * Gets 已撤销时间
     * @return string
     */
    public function getCancelledTime()
    {
        return $this->__get(self::CANCELLED_TIME);
    }

    /**
     * Sets 已撤销时间
     * @param string $value
     * @return Payout
     */
    public function setCancelledTime($value)
    {
        $this->__set(self::CANCELLED_TIME, $value);

        return $this;
    }

    /**
     * Gets 交易状态历史
     * @return string
     */
    public function getStatusLog()
    {
        return $this->__get(self::STATUS_LOG);
    }

    /**
     * Sets 交易状态历史
     * @param string $value
     * @return Payout
     */
    public function setStatusLog($value)
    {
        $this->__set(self::STATUS_LOG, $value);

        return $this;
    }
}
?>