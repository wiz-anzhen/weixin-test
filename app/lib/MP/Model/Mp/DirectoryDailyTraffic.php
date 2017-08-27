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

class DirectoryDailyTraffic extends Model
{
    const DIRECTORY_DAILY_TRAFFIC_ID = 'directory_daily_traffic_id';
    const MP_USER_ID = 'mp_user_id';
    const COMMUNITY_ID = 'community_id';
    const DIRECTORY_ID = 'directory_id';
    const YMD = 'ymd';
    const UV = 'uv';
    const PV = 'pv';



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
                'directory_daily_traffic',
                'directory_daily_traffic_id',
                [
                    'directory_daily_traffic_id' => ['name' => _META_('mp.directory_daily_traffic.directory_daily_traffic_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'mp_user_id' => ['name' => _META_('mp.directory_daily_traffic.mp_user_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'community_id' => ['name' => _META_('mp.directory_daily_traffic.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'directory_id' => ['name' => _META_('mp.directory_daily_traffic.directory_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'ymd' => ['name' => _META_('mp.directory_daily_traffic.ymd'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'uv' => ['name' => _META_('mp.directory_daily_traffic.uv'), 'type' => 'int', 'length' => 10, 'db_insert' => true, 'required' => true],
                    'pv' => ['name' => _META_('mp.directory_daily_traffic.pv'), 'type' => 'int', 'length' => 10, 'db_insert' => true, 'required' => true],
                ],
                [
                    'auto_increment_id' => 'directory_daily_traffic_id',
                    'unique_keys' => [['directory_id', 'ymd']],
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
    public function getDirectoryDailyTrafficID()
    {
        return $this->__get(self::DIRECTORY_DAILY_TRAFFIC_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return DirectoryDailyTraffic
     */
    public function setDirectoryDailyTrafficID($value)
    {
        $this->__set(self::DIRECTORY_DAILY_TRAFFIC_ID, $value);

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
     * @return DirectoryDailyTraffic
     */
    public function setMpUserID($value)
    {
        $this->__set(self::MP_USER_ID, $value);

        return $this;
    }

    /**
     * Gets ‘community.community_id’
     * @return int
     */
    public function getCommunityID()
    {
        return $this->__get(self::COMMUNITY_ID);
    }

    /**
     * Sets ‘community.community_id’
     * @param int $value
     * @return DirectoryDailyTraffic
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets 二级目录id
     * @return int
     */
    public function getDirectoryID()
    {
        return $this->__get(self::DIRECTORY_ID);
    }

    /**
     * Sets 二级目录id
     * @param int $value
     * @return DirectoryDailyTraffic
     */
    public function setDirectoryID($value)
    {
        $this->__set(self::DIRECTORY_ID, $value);

        return $this;
    }

    /**
     * Gets 统计日期
     * @return int
     */
    public function getYmd()
    {
        return $this->__get(self::YMD);
    }

    /**
     * Sets 统计日期
     * @param int $value
     * @return DirectoryDailyTraffic
     */
    public function setYmd($value)
    {
        $this->__set(self::YMD, $value);

        return $this;
    }

    /**
     * Gets 每日独立访客量
     * @return int
     */
    public function getUv()
    {
        return $this->__get(self::UV);
    }

    /**
     * Sets 每日独立访客量
     * @param int $value
     * @return DirectoryDailyTraffic
     */
    public function setUv($value)
    {
        $this->__set(self::UV, $value);

        return $this;
    }

    /**
     * Gets 每日点击量
     * @return int
     */
    public function getPv()
    {
        return $this->__get(self::PV);
    }

    /**
     * Sets 每日点击量
     * @param int $value
     * @return DirectoryDailyTraffic
     */
    public function setPv($value)
    {
        $this->__set(self::PV, $value);

        return $this;
    }
}
?>