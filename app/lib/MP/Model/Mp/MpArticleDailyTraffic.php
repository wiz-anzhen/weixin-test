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

class MpArticleDailyTraffic extends Model
{
    const MP_ARTICLE_DAILY_TRAFFIC_ID = 'mp_article_daily_traffic_id';
    const MP_USER_ID = 'mp_user_id';
    const COMMUNITY_ID = 'community_id';
    const MP_ARTICLE_ID = 'mp_article_id';
    const YMD = 'ymd';
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
                'mp_article_daily_traffic',
                'mp_article_daily_traffic_id',
                [
                    'mp_article_daily_traffic_id' => ['name' => _META_('mp.mp_article_daily_traffic.mp_article_daily_traffic_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'mp_user_id' => ['name' => _META_('mp.mp_article_daily_traffic.mp_user_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'community_id' => ['name' => _META_('mp.mp_article_daily_traffic.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'mp_article_id' => ['name' => _META_('mp.mp_article_daily_traffic.mp_article_id'), 'type' => 'text', 'length' => 32, 'required' => true],
                    'ymd' => ['name' => _META_('mp.mp_article_daily_traffic.ymd'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'pv' => ['name' => _META_('mp.mp_article_daily_traffic.pv'), 'type' => 'int', 'length' => 10, 'db_insert' => true, 'required' => true],
                ],
                [
                    'auto_increment_id' => 'mp_article_daily_traffic_id',
                    'unique_keys' => [['mp_article_id', 'ymd']],
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
    public function getMpArticleDailyTrafficID()
    {
        return $this->__get(self::MP_ARTICLE_DAILY_TRAFFIC_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return MpArticleDailyTraffic
     */
    public function setMpArticleDailyTrafficID($value)
    {
        $this->__set(self::MP_ARTICLE_DAILY_TRAFFIC_ID, $value);

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
     * @return MpArticleDailyTraffic
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
     * @return MpArticleDailyTraffic
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets 素材管理id
     * @return string
     */
    public function getMpArticleID()
    {
        return $this->__get(self::MP_ARTICLE_ID);
    }

    /**
     * Sets 素材管理id
     * @param string $value
     * @return MpArticleDailyTraffic
     */
    public function setMpArticleID($value)
    {
        $this->__set(self::MP_ARTICLE_ID, $value);

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
     * @return MpArticleDailyTraffic
     */
    public function setYmd($value)
    {
        $this->__set(self::YMD, $value);

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
     * @return MpArticleDailyTraffic
     */
    public function setPv($value)
    {
        $this->__set(self::PV, $value);

        return $this;
    }
}
?>