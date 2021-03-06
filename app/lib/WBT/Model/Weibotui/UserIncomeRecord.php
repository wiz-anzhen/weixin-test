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

class UserIncomeRecord extends Model
{
    const SERIAL_NO = 'serial_no';
    const AMOUNT = 'amount';
    const USER = 'user';
    const SOURCE = 'source';

    const WITH_USER = 'user_income_record.user:user.user_id';


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
                'user_income_record',
                'serial_no',
                [
                    'serial_no' => ['name' => _META_('weibotui.user_income_record.serial_no'), 'type' => 'text', 'length' => 20, 'required' => true],
                    'amount' => ['name' => _META_('weibotui.user_income_record.amount'), 'type' => 'money', 'precision' => 2, 'required' => true],
                    'user' => ['name' => _META_('weibotui.user_income_record.user'), 'type' => 'int', 'length' => 10, 'min' => 100000, 'required' => true],
                    'source' => ['name' => _META_('weibotui.user_income_record.source'), 'type' => 'text', 'max' => 20, 'required' => true, 'enum' => new UserBusinessType(), 'db_insert' => true],
                ],
                [
                    'owner_field' => 'user',
                    'triggers' => ['AFTER-INSERT']
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

    public function owner()
    {
        return $this->__get('user');
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
     * @return UserIncomeRecord
     */
    public function setSerialNo($value)
    {
        $this->__set(self::SERIAL_NO, $value);

        return $this;
    }

    /**
     * Gets 收入金额
     * @return float
     */
    public function getAmount()
    {
        return $this->__get(self::AMOUNT);
    }

    /**
     * Sets 收入金额
     * @param float $value
     * @return UserIncomeRecord
     */
    public function setAmount($value)
    {
        $this->__set(self::AMOUNT, $value);

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
     * @return UserIncomeRecord
     */
    public function setUser($value)
    {
        $this->__set(self::USER, $value);

        return $this;
    }

    /**
     * Gets 收入来源
     * @return string
     */
    public function getSource()
    {
        return $this->__get(self::SOURCE);
    }

    /**
     * Gets 收入来源 display name
     * @return string
     */
    public function getSource_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('source');
        return $option['enum']::getDisplayName($this->__get(self::SOURCE));
    }

    /**
     * Sets 收入来源
     * @param string $value
     * @return UserIncomeRecord
     */
    public function setSource($value)
    {
        $this->__set(self::SOURCE, $value);

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

    protected function _afterInsert()
    {
        App::getInstance()->log()->verbose('UserIncomeRecord::_afterInsert', 'diag');
        //收入后更新收入账户余额
        $userAsset = new UserAsset($this->user);
        $userAsset->income_balance += $this->amount;
        $userAsset->update();
        
    }
}
?>