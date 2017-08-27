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

class Province extends Model
{
    const CODE = 'code';
    const _IS_DELETED = '_is_deleted';
    const NAME = 'name';
    const SHORT_NAME = 'short_name';
    const ADMIN_CODE = 'admin_code';
    const TYPE = 'type';
    const COUNTRY = 'country';
    const CAPITAL_CITY = 'capital_city';

    const WITH_COUNTRY = 'province.country:country.code';
    const WITH_CAPITAL_CITY = 'province.capital_city:city.code';


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
                'province',
                'code',
                [
                    'code' => ['name' => _META_('weibotui.province.code'), 'type' => 'idname', 'required' => true],
                    '_is_deleted' => ['name' => _META_('weibotui.province._is_deleted'), 'type' => 'bool', 'required' => true, 'db_insert' => true, 'roc' => true, 'rou' => true],
                    'name' => ['name' => _META_('weibotui.province.name'), 'type' => 'text', 'min' => 1, 'max' => 40],
                    'short_name' => ['name' => _META_('weibotui.province.short_name'), 'type' => 'text', 'min' => 1, 'max' => 20],
                    'admin_code' => ['name' => _META_('weibotui.province.admin_code'), 'type' => 'idname'],
                    'type' => ['name' => _META_('weibotui.province.type'), 'type' => 'text', 'max' => 20, 'enum' => new ProvinceType(), 'db_insert' => true],
                    'country' => ['name' => _META_('weibotui.province.country'), 'type' => 'idname', 'required' => true],
                    'capital_city' => ['name' => _META_('weibotui.province.capital_city'), 'type' => 'idname'],
                ],
                [
                    'logical_deletion' => '_is_deleted',
                ],
                [
                    'country' => self::WITH_COUNTRY,
                    'capital_city' => self::WITH_CAPITAL_CITY,
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
     * Gets 编码
     * @return string
     */
    public function getCode()
    {
        return $this->__get(self::CODE);
    }

    /**
     * Sets 编码
     * @param string $value
     * @return Province
     */
    public function setCode($value)
    {
        $this->__set(self::CODE, $value);

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
     * @return Province
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
     * @return Province
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
     * @return Province
     */
    public function setShortName($value)
    {
        $this->__set(self::SHORT_NAME, $value);

        return $this;
    }

    /**
     * Gets 行政区号
     * @return string
     */
    public function getAdminCode()
    {
        return $this->__get(self::ADMIN_CODE);
    }

    /**
     * Sets 行政区号
     * @param string $value
     * @return Province
     */
    public function setAdminCode($value)
    {
        $this->__set(self::ADMIN_CODE, $value);

        return $this;
    }

    /**
     * Gets 地区类型
     * @return string
     */
    public function getType()
    {
        return $this->__get(self::TYPE);
    }

    /**
     * Gets 地区类型 display name
     * @return string
     */
    public function getType_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('type');
        return $option['enum']::getDisplayName($this->__get(self::TYPE));
    }

    /**
     * Sets 地区类型
     * @param string $value
     * @return Province
     */
    public function setType($value)
    {
        $this->__set(self::TYPE, $value);

        return $this;
    }

    /**
     * Gets 国家
     * @return string
     */
    public function getCountry()
    {
        return $this->__get(self::COUNTRY);
    }

    /**
     * Sets 国家
     * @param string $value
     * @return Province
     */
    public function setCountry($value)
    {
        $this->__set(self::COUNTRY, $value);

        return $this;
    }

    /**
     * Gets 市
     * @return string
     */
    public function getCapitalCity()
    {
        return $this->__get(self::CAPITAL_CITY);
    }

    /**
     * Sets 市
     * @param string $value
     * @return Province
     */
    public function setCapitalCity($value)
    {
        $this->__set(self::CAPITAL_CITY, $value);

        return $this;
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\Country
     */
    public function getCountry_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\Country();
        }

        if (isset($this->_links['country']))
        {
            return $this->_links['country'];
        }

        return ($this->_links['country'] = new \WBT\Model\Weibotui\Country($this->getCountry()));
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\City
     */
    public function getCapitalCity_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\City();
        }

        if (isset($this->_links['capital_city']))
        {
            return $this->_links['capital_city'];
        }

        return ($this->_links['capital_city'] = new \WBT\Model\Weibotui\City($this->getCapitalCity()));
    }
}
?>