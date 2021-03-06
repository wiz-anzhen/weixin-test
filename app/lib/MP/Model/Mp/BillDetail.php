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

class BillDetail extends Model
{
    const BILL_DETAIL_ID = 'bill_detail_id';
    const BILL_ID = 'bill_id';
    const COMMUNITY_ID = 'community_id';
    const BILL_DAY = 'bill_day';
    const BILL_DETAIL_NAME = 'bill_detail_name';
    const BILLING_CYCLE = 'billing_cycle';
    const DETAIL_PAYMENT = 'detail_payment';
    const DETAIL_REMARKS = 'detail_remarks';



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
                'bill_detail',
                'bill_detail_id',
                [
                    'bill_detail_id' => ['name' => _META_('mp.bill_detail.bill_detail_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'bill_id' => ['name' => _META_('mp.bill_detail.bill_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'community_id' => ['name' => _META_('mp.bill_detail.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'bill_day' => ['name' => _META_('mp.bill_detail.bill_day'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'bill_detail_name' => ['name' => _META_('mp.bill_detail.bill_detail_name'), 'type' => 'text', 'length' => 128, 'required' => true],
                    'billing_cycle' => ['name' => _META_('mp.bill_detail.billing_cycle'), 'type' => 'text', 'length' => 128, 'required' => true],
                    'detail_payment' => ['name' => _META_('mp.bill_detail.detail_payment'), 'type' => 'money', 'precision' => 2, 'required' => true],
                    'detail_remarks' => ['name' => _META_('mp.bill_detail.detail_remarks'), 'type' => 'text', 'length' => 255],
                ],
                [
                    'auto_increment_id' => 'bill_detail_id',
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
    public function getBillDetailID()
    {
        return $this->__get(self::BILL_DETAIL_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return BillDetail
     */
    public function setBillDetailID($value)
    {
        $this->__set(self::BILL_DETAIL_ID, $value);

        return $this;
    }

    /**
     * Gets bill.bill_id
     * @return int
     */
    public function getBillID()
    {
        return $this->__get(self::BILL_ID);
    }

    /**
     * Sets bill.bill_id
     * @param int $value
     * @return BillDetail
     */
    public function setBillID($value)
    {
        $this->__set(self::BILL_ID, $value);

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
     * @return BillDetail
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets 账单日期
     * @return int
     */
    public function getBillDay()
    {
        return $this->__get(self::BILL_DAY);
    }

    /**
     * Sets 账单日期
     * @param int $value
     * @return BillDetail
     */
    public function setBillDay($value)
    {
        $this->__set(self::BILL_DAY, $value);

        return $this;
    }

    /**
     * Gets 业主欠费明细收费项目名称
     * @return string
     */
    public function getBillDetailName()
    {
        return $this->__get(self::BILL_DETAIL_NAME);
    }

    /**
     * Sets 业主欠费明细收费项目名称
     * @param string $value
     * @return BillDetail
     */
    public function setBillDetailName($value)
    {
        $this->__set(self::BILL_DETAIL_NAME, $value);

        return $this;
    }

    /**
     * Gets 计费周期
     * @return string
     */
    public function getBillingCycle()
    {
        return $this->__get(self::BILLING_CYCLE);
    }

    /**
     * Sets 计费周期
     * @param string $value
     * @return BillDetail
     */
    public function setBillingCycle($value)
    {
        $this->__set(self::BILLING_CYCLE, $value);

        return $this;
    }

    /**
     * Gets 应收金额
     * @return float
     */
    public function getDetailPayment()
    {
        return $this->__get(self::DETAIL_PAYMENT);
    }

    /**
     * Sets 应收金额
     * @param float $value
     * @return BillDetail
     */
    public function setDetailPayment($value)
    {
        $this->__set(self::DETAIL_PAYMENT, $value);

        return $this;
    }

    /**
     * Gets 备注
     * @return string
     */
    public function getDetailRemarks()
    {
        return $this->__get(self::DETAIL_REMARKS);
    }

    /**
     * Sets 备注
     * @param string $value
     * @return BillDetail
     */
    public function setDetailRemarks($value)
    {
        $this->__set(self::DETAIL_REMARKS, $value);

        return $this;
    }
}
?>