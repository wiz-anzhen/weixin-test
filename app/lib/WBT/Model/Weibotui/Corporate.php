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

class Corporate extends Model
{
    const CORPORATE_ID = 'corporate_id';
    const _UPDATED_AT = '_updated_at';
    const _IS_DELETED = '_is_deleted';
    const NAME = 'name';
    const SHORT_NAME = 'short_name';
    const WEBSITE = 'website';
    const DESCRIPTION = 'description';
    const ADDRESS = 'address';
    const ADMIN = 'admin';
    const STATUS = 'status';
    const UNVERIFIED_TIME = 'unverified_time';
    const VERIFIED_TIME = 'verified_time';
    const STATUS_LOG = 'status_log';

    const WITH_ADDRESS = 'corporate.address:address.address_id';
    const WITH_ADMIN = 'corporate.admin:user.user_id';


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
                'corporate',
                'corporate_id',
                [
                    'corporate_id' => ['name' => _META_('weibotui.corporate.corporate_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    '_updated_at' => ['name' => _META_('weibotui.corporate._updated_at'), 'type' => 'timestamp', 'required' => true, 'db_insert' => true, 'roc' => true, 'rou' => true],
                    '_is_deleted' => ['name' => _META_('weibotui.corporate._is_deleted'), 'type' => 'bool', 'required' => true, 'db_insert' => true, 'roc' => true, 'rou' => true],
                    'name' => ['name' => _META_('weibotui.corporate.name'), 'type' => 'text', 'min' => 1, 'max' => 40],
                    'short_name' => ['name' => _META_('weibotui.corporate.short_name'), 'type' => 'text', 'min' => 1, 'max' => 20],
                    'website' => ['name' => _META_('weibotui.corporate.website'), 'type' => 'url', 'required' => true],
                    'description' => ['name' => _META_('weibotui.corporate.description'), 'type' => 'text', 'required' => true],
                    'address' => ['name' => _META_('weibotui.corporate.address'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true],
                    'admin' => ['name' => _META_('weibotui.corporate.admin'), 'type' => 'int', 'length' => 10, 'min' => 100000, 'required' => true],
                    'status' => ['name' => _META_('weibotui.corporate.status'), 'type' => 'idname', 'required' => true, 'db_insert' => true, 'state' => new CorporateStatus()],
                    'unverified_time' => ['name' => _META_('weibotui.corporate.unverified_time'), 'type' => 'datetime'],
                    'verified_time' => ['name' => _META_('weibotui.corporate.verified_time'), 'type' => 'datetime'],
                    'status_log' => ['name' => _META_('weibotui.corporate.status_log'), 'type' => 'text', 'max' => 1000, 'default' => 'unverified'],
                ],
                [
                    'auto_increment_id' => 'corporate_id',
                    'update_timestamp' => '_updated_at',
                    'logical_deletion' => '_is_deleted',
                    'has_states' => 'status',
                    'unique_keys' => [['name'], ['short_name'], ['admin']],
                ],
                [
                    'address' => self::WITH_ADDRESS,
                    'admin' => self::WITH_ADMIN,
                ],
                [
                    'staff_in_corporate' => ['corporate_id', 'company', false, 'user', 'user', 'user_id'],
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
    public function getCorporateID()
    {
        return $this->__get(self::CORPORATE_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return Corporate
     */
    public function setCorporateID($value)
    {
        $this->__set(self::CORPORATE_ID, $value);

        return $this;
    }

    /**
     * Gets updatedat
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->__get(self::_UPDATED_AT);
    }

    /**
     * Sets updatedat
     * @param string $value
     * @return Corporate
     */
    public function setUpdatedAt($value)
    {
        $this->__set(self::_UPDATED_AT, $value);

        return $this;
    }

    /**
     * Gets isdeleted
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->__get(self::_IS_DELETED);
    }

    /**
     * Sets isdeleted
     * @param bool $value
     * @return Corporate
     */
    public function setIsDeleted($value)
    {
        $this->__set(self::_IS_DELETED, $value);

        return $this;
    }

    /**
     * Gets 名称
     * @return string
     */
    public function getName()
    {
        return $this->__get(self::NAME);
    }

    /**
     * Sets 名称
     * @param string $value
     * @return Corporate
     */
    public function setName($value)
    {
        $this->__set(self::NAME, $value);

        return $this;
    }

    /**
     * Gets 简称
     * @return string
     */
    public function getShortName()
    {
        return $this->__get(self::SHORT_NAME);
    }

    /**
     * Sets 简称
     * @param string $value
     * @return Corporate
     */
    public function setShortName($value)
    {
        $this->__set(self::SHORT_NAME, $value);

        return $this;
    }

    /**
     * Gets 企业网址
     * @return string
     */
    public function getWebsite()
    {
        return $this->__get(self::WEBSITE);
    }

    /**
     * Sets 企业网址
     * @param string $value
     * @return Corporate
     */
    public function setWebsite($value)
    {
        $this->__set(self::WEBSITE, $value);

        return $this;
    }

    /**
     * Gets 企业简介
     * @return string
     */
    public function getDescription()
    {
        return $this->__get(self::DESCRIPTION);
    }

    /**
     * Sets 企业简介
     * @param string $value
     * @return Corporate
     */
    public function setDescription($value)
    {
        $this->__set(self::DESCRIPTION, $value);

        return $this;
    }

    /**
     * Gets 地址
     * @return int
     */
    public function getAddress()
    {
        return $this->__get(self::ADDRESS);
    }

    /**
     * Sets 地址
     * @param int $value
     * @return Corporate
     */
    public function setAddress($value)
    {
        $this->__set(self::ADDRESS, $value);

        return $this;
    }

    /**
     * Gets 管理员
     * @return int
     */
    public function getAdmin()
    {
        return $this->__get(self::ADMIN);
    }

    /**
     * Sets 管理员
     * @param int $value
     * @return Corporate
     */
    public function setAdmin($value)
    {
        $this->__set(self::ADMIN, $value);

        return $this;
    }

    /**
     * Gets 企业状态
     * @return string
     */
    public function getStatus()
    {
        return $this->__get(self::STATUS);
    }

    /**
     * Gets 企业状态 display name
     * @return string
     */
    public function getStatus_StateValue()
    {
        $option = $this->metadata()->getFilterOption('status');
        return $option['state']::getDisplayName($this->__get(self::STATUS));
    }

    /**
     * Sets 企业状态
     * @param string $value
     * @return Corporate
     */
    public function setStatus($value)
    {
        $this->__set(self::STATUS, $value);

        return $this;
    }

    /**
     * Gets 未审核时间
     * @return string
     */
    public function getUnverifiedTime()
    {
        return $this->__get(self::UNVERIFIED_TIME);
    }

    /**
     * Sets 未审核时间
     * @param string $value
     * @return Corporate
     */
    public function setUnverifiedTime($value)
    {
        $this->__set(self::UNVERIFIED_TIME, $value);

        return $this;
    }

    /**
     * Gets 已审核时间
     * @return string
     */
    public function getVerifiedTime()
    {
        return $this->__get(self::VERIFIED_TIME);
    }

    /**
     * Sets 已审核时间
     * @param string $value
     * @return Corporate
     */
    public function setVerifiedTime($value)
    {
        $this->__set(self::VERIFIED_TIME, $value);

        return $this;
    }

    /**
     * Gets 企业状态历史
     * @return string
     */
    public function getStatusLog()
    {
        return $this->__get(self::STATUS_LOG);
    }

    /**
     * Sets 企业状态历史
     * @param string $value
     * @return Corporate
     */
    public function setStatusLog($value)
    {
        $this->__set(self::STATUS_LOG, $value);

        return $this;
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\Address
     */
    public function getAddress_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\Address();
        }

        if (isset($this->_links['address']))
        {
            return $this->_links['address'];
        }

        return ($this->_links['address'] = new \WBT\Model\Weibotui\Address($this->getAddress()));
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\User
     */
    public function getAdmin_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\User();
        }

        if (isset($this->_links['admin']))
        {
            return $this->_links['admin'];
        }

        return ($this->_links['admin'] = new \WBT\Model\Weibotui\User($this->getAdmin()));
    }
}
?>