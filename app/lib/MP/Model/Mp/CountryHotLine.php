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

class CountryHotLine extends Model
{
    const COUNTRY_HOT_LINE_ID = 'country_hot_line_id';
    const POPULATED_COUNTRY = 'populated_country';
    const HOT_LINE = 'hot_line';



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
                'country_hot_line',
                'country_hot_line_id',
                [
                    'country_hot_line_id' => ['name' => _META_('mp.country_hot_line.country_hot_line_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'populated_country' => ['name' => _META_('mp.country_hot_line.populated_country'), 'type' => 'text', 'length' => 128, 'required' => true],
                    'hot_line' => ['name' => _META_('mp.country_hot_line.hot_line'), 'type' => 'text', 'length' => 64, 'required' => true],
                ],
                [
                    'auto_increment_id' => 'country_hot_line_id',
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
    public function getCountryHotLineID()
    {
        return $this->__get(self::COUNTRY_HOT_LINE_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return CountryHotLine
     */
    public function setCountryHotLineID($value)
    {
        $this->__set(self::COUNTRY_HOT_LINE_ID, $value);

        return $this;
    }

    /**
     * Gets 居民区
     * @return string
     */
    public function getPopulatedCountry()
    {
        return $this->__get(self::POPULATED_COUNTRY);
    }

    /**
     * Sets 居民区
     * @param string $value
     * @return CountryHotLine
     */
    public function setPopulatedCountry($value)
    {
        $this->__set(self::POPULATED_COUNTRY, $value);

        return $this;
    }

    /**
     * Gets 服务热线
     * @return string
     */
    public function getHotLine()
    {
        return $this->__get(self::HOT_LINE);
    }

    /**
     * Sets 服务热线
     * @param string $value
     * @return CountryHotLine
     */
    public function setHotLine($value)
    {
        $this->__set(self::HOT_LINE, $value);

        return $this;
    }
}
?>