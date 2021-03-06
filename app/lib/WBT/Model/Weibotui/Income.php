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

class Income extends Model
{
    const SERIAL_NO = 'serial_no';
    const BILL_ID = 'bill_id';
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


    const TO_SUCCEED = '_succeed';
    const TO_FAIL = '_fail';
    const TO_CANCEL = '_cancel';

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
                'income',
                'serial_no',
                [
                    'serial_no' => ['name' => _META_('weibotui.income.serial_no'), 'type' => 'text', 'length' => 20, 'default' => new \Bluefin\Data\InvalidData(), 'required' => true],
                    'bill_id' => ['name' => _META_('weibotui.income.bill_id'), 'type' => 'text', 'length' => 20, 'required' => true],
                    'vendor_no' => ['name' => _META_('weibotui.income.vendor_no'), 'type' => 'text', 'length' => 32],
                    'total_amount' => ['name' => _META_('weibotui.income.total_amount'), 'type' => 'money', 'precision' => 2, 'required' => true],
                    'type' => ['name' => _META_('weibotui.income.type'), 'type' => 'text', 'max' => 20, 'required' => true, 'enum' => new IncomeType(), 'db_insert' => true],
                    'payment_method' => ['name' => _META_('weibotui.income.payment_method'), 'type' => 'text', 'max' => 20, 'required' => true, 'enum' => new PaymentMethod(), 'db_insert' => true],
                    'status' => ['name' => _META_('weibotui.income.status'), 'type' => 'idname', 'required' => true, 'state' => new TransactionStatus(), 'db_insert' => true],
                    'ongoing_time' => ['name' => _META_('weibotui.income.ongoing_time'), 'type' => 'datetime'],
                    'done_time' => ['name' => _META_('weibotui.income.done_time'), 'type' => 'datetime'],
                    'failed_time' => ['name' => _META_('weibotui.income.failed_time'), 'type' => 'datetime'],
                    'cancelled_time' => ['name' => _META_('weibotui.income.cancelled_time'), 'type' => 'datetime'],
                    'status_log' => ['name' => _META_('weibotui.income.status_log'), 'type' => 'text', 'max' => 1000, 'default' => 'ongoing'],
                ],
                [
                    'has_states' => 'status',
                    'unique_keys' => [['bill_id', 'type']],
                    'triggers' => ['BEFORE-INSERT']
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
                    '_succeed' => ['ongoing' => ['weibotui' => ['*vendor*']], ],
                    '_fail' => ['ongoing' => ['weibotui' => ['*vendor*']], ],
                    '_cancel' => ['ongoing' => ['weibotui' => ['*system*']], ],
                ]
            );
        }

        return self::$__metadata;
    }

    /**
     * @param string $serialNo
     * @param array $params
     * @return \WBT\Model\Weibotui\Income
     * @throws \Bluefin\Exception\RequestException
     */
    public static function doSucceed($serialNo, array $params = null)
    {
        App::getInstance()->log()->verbose('Income::doSucceed', 'diag');

        if (is_array($serialNo))
        {
            $income = new Income();
            $income->populate($serialNo);
            $serialNo = $income->pk();
        }
        else
        {
            $income = new Income($serialNo);
        }
        _NON_EMPTY($income);

        $aclStatus = self::checkActionPermission(self::TO_SUCCEED, $income->data());
        if ($aclStatus !== Model::ACL_ACCEPTED)
        {
            if (ENV == 'dev')
            {
                throw new \Bluefin\Exception\RequestException(\Bluefin\Common::getStatusCodeMessage($aclStatus) . ' @ ' . __METHOD__, $aclStatus);
            }
            throw new \Bluefin\Exception\RequestException(null, $aclStatus);
        }

        $currentState = $income->getStatus();
        $methodName = "{$currentState}ToSucceed";
        return self::$methodName($serialNo, $params, $income);
    }

    public static function ongoingToSucceed($serialNo, array $params = null, Model $cachedModel = null)
    {
        App::getInstance()->log()->verbose('Income::ongoingToSucceed', 'diag');

        if (!isset($params)) throw new \Bluefin\Exception\InvalidRequestException();

        $fieldParams = array_get_all($params, ['vendor_no']);
        _ARG_EXISTS('vendor_no', $fieldParams);

        $db = self::s_metadata()->getDatabase()->getAdapter();
        $db->beginTransaction();

        try
        {
            if (isset($cachedModel))
            {
                $income = $cachedModel;
            }
            else
            {
                $income = new Income($serialNo);
                _NON_EMPTY($income);

                $aclStatus = self::checkActionPermission(self::TO_SUCCEED, $income->data());
                if ($aclStatus !== Model::ACL_ACCEPTED)
                {
                    throw new \Bluefin\Exception\RequestException(null, $aclStatus);
                }

                $currentState = $income->getStatus();
                if ($currentState != 'ongoing')
                {
                    throw new \Bluefin\Exception\InvalidRequestException();
                }
            }

            //Set target state
            $income->setStatus(TransactionStatus::DONE);

            //Apply input paramters
            $income->apply($fieldParams);

            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, true);
            $affected = $income->update(['serial_no' => $serialNo, 'status' => 'ongoing']);
            if ($affected <= 0)
            {
                App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);
                throw new \Bluefin\Exception\DataException(_APP_("The record to operate is not in expected state."));
            }

            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();

            throw $e;
        }

        return $income;
    }

    /**
     * @param string $serialNo
     * @param array $params
     * @return \WBT\Model\Weibotui\Income
     * @throws \Bluefin\Exception\RequestException
     */
    public static function doFail($serialNo, array $params = null)
    {
        App::getInstance()->log()->verbose('Income::doFail', 'diag');

        if (is_array($serialNo))
        {
            $income = new Income();
            $income->populate($serialNo);
            $serialNo = $income->pk();
        }
        else
        {
            $income = new Income($serialNo);
        }
        _NON_EMPTY($income);

        $aclStatus = self::checkActionPermission(self::TO_FAIL, $income->data());
        if ($aclStatus !== Model::ACL_ACCEPTED)
        {
            if (ENV == 'dev')
            {
                throw new \Bluefin\Exception\RequestException(\Bluefin\Common::getStatusCodeMessage($aclStatus) . ' @ ' . __METHOD__, $aclStatus);
            }
            throw new \Bluefin\Exception\RequestException(null, $aclStatus);
        }

        $currentState = $income->getStatus();
        $methodName = "{$currentState}ToFail";
        return self::$methodName($serialNo, $params, $income);
    }

    public static function ongoingToFail($serialNo, array $params = null, Model $cachedModel = null)
    {
        App::getInstance()->log()->verbose('Income::ongoingToFail', 'diag');

        if (!isset($params)) throw new \Bluefin\Exception\InvalidRequestException();

        $fieldParams = array_get_all($params, ['vendor_no']);

        $db = self::s_metadata()->getDatabase()->getAdapter();
        $db->beginTransaction();

        try
        {
            if (isset($cachedModel))
            {
                $income = $cachedModel;
            }
            else
            {
                $income = new Income($serialNo);
                _NON_EMPTY($income);

                $aclStatus = self::checkActionPermission(self::TO_FAIL, $income->data());
                if ($aclStatus !== Model::ACL_ACCEPTED)
                {
                    throw new \Bluefin\Exception\RequestException(null, $aclStatus);
                }

                $currentState = $income->getStatus();
                if ($currentState != 'ongoing')
                {
                    throw new \Bluefin\Exception\InvalidRequestException();
                }
            }

            //Set target state
            $income->setStatus(TransactionStatus::FAILED);

            //Apply input paramters
            $income->apply($fieldParams);

            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, true);
            $affected = $income->update(['serial_no' => $serialNo, 'status' => 'ongoing']);
            if ($affected <= 0)
            {
                App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);
                throw new \Bluefin\Exception\DataException(_APP_("The record to operate is not in expected state."));
            }

            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();

            throw $e;
        }

        return $income;
    }

    /**
     * @param string $serialNo
     * @param array $params
     * @return \WBT\Model\Weibotui\Income
     * @throws \Bluefin\Exception\RequestException
     */
    public static function doCancel($serialNo, array $params = null)
    {
        App::getInstance()->log()->verbose('Income::doCancel', 'diag');

        if (is_array($serialNo))
        {
            $income = new Income();
            $income->populate($serialNo);
            $serialNo = $income->pk();
        }
        else
        {
            $income = new Income($serialNo);
        }
        _NON_EMPTY($income);

        $aclStatus = self::checkActionPermission(self::TO_CANCEL, $income->data());
        if ($aclStatus !== Model::ACL_ACCEPTED)
        {
            if (ENV == 'dev')
            {
                throw new \Bluefin\Exception\RequestException(\Bluefin\Common::getStatusCodeMessage($aclStatus) . ' @ ' . __METHOD__, $aclStatus);
            }
            throw new \Bluefin\Exception\RequestException(null, $aclStatus);
        }

        $currentState = $income->getStatus();
        $methodName = "{$currentState}ToCancel";
        return self::$methodName($serialNo, $params, $income);
    }

    public static function ongoingToCancel($serialNo, array $params = null, Model $cachedModel = null)
    {
        App::getInstance()->log()->verbose('Income::ongoingToCancel', 'diag');

        $db = self::s_metadata()->getDatabase()->getAdapter();
        $db->beginTransaction();

        try
        {
            if (isset($cachedModel))
            {
                $income = $cachedModel;
            }
            else
            {
                $income = new Income($serialNo);
                _NON_EMPTY($income);

                $aclStatus = self::checkActionPermission(self::TO_CANCEL, $income->data());
                if ($aclStatus !== Model::ACL_ACCEPTED)
                {
                    throw new \Bluefin\Exception\RequestException(null, $aclStatus);
                }

                $currentState = $income->getStatus();
                if ($currentState != 'ongoing')
                {
                    throw new \Bluefin\Exception\InvalidRequestException();
                }
            }

            //Set target state
            $income->setStatus(TransactionStatus::CANCELLED);

            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, true);
            $affected = $income->update(['serial_no' => $serialNo, 'status' => 'ongoing']);
            if ($affected <= 0)
            {
                App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);
                throw new \Bluefin\Exception\DataException(_APP_("The record to operate is not in expected state."));
            }

            $income->_afterCancelled();
            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();

            throw $e;
        }

        return $income;
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
     * @return Income
     */
    public function setSerialNo($value)
    {
        $this->__set(self::SERIAL_NO, $value);

        return $this;
    }

    /**
     * Gets 订单号
     * @return string
     */
    public function getBillID()
    {
        return $this->__get(self::BILL_ID);
    }

    /**
     * Sets 订单号
     * @param string $value
     * @return Income
     */
    public function setBillID($value)
    {
        $this->__set(self::BILL_ID, $value);

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
     * @return Income
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
     * @return Income
     */
    public function setTotalAmount($value)
    {
        $this->__set(self::TOTAL_AMOUNT, $value);

        return $this;
    }

    /**
     * Gets 收入类型
     * @return string
     */
    public function getType()
    {
        return $this->__get(self::TYPE);
    }

    /**
     * Gets 收入类型 display name
     * @return string
     */
    public function getType_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('type');
        return $option['enum']::getDisplayName($this->__get(self::TYPE));
    }

    /**
     * Sets 收入类型
     * @param string $value
     * @return Income
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
     * @return Income
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
     * @return Income
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
     * @return Income
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
     * @return Income
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
     * @return Income
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
     * @return Income
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
     * @return Income
     */
    public function setStatusLog($value)
    {
        $this->__set(self::STATUS_LOG, $value);

        return $this;
    }

    protected function _beforeInsert()
    {
        App::getInstance()->log()->verbose('Income::_beforeInsert', 'diag');
        $this->serial_no = \WBT\Data\TransactionCode::getSerialNo($this->type, $this->bill_id);
        
    }

    protected function _afterCancelled(array $INPUT = null)
    {
        App::getInstance()->log()->verbose('Income::_afterCancelled', 'diag');    
        $this->delete();
        
    }
}
?>