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

class UrgentNoticeReadRecord extends Model
{
    const URGENT_NOTICE_READ_RECORD_ID = 'urgent_notice_read_record_id';
    const CHANNEL_ARTICLE_ID = 'channel_article_id';
    const WX_USER_ID = 'wx_user_id';



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
                'urgent_notice_read_record',
                'urgent_notice_read_record_id',
                [
                    'urgent_notice_read_record_id' => ['name' => _META_('mp.urgent_notice_read_record.urgent_notice_read_record_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'channel_article_id' => ['name' => _META_('mp.urgent_notice_read_record.channel_article_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'wx_user_id' => ['name' => _META_('mp.urgent_notice_read_record.wx_user_id'), 'type' => 'text', 'length' => 64, 'required' => true],
                ],
                [
                    'auto_increment_id' => 'urgent_notice_read_record_id',
                    'unique_keys' => [['channel_article_id', 'wx_user_id']],
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
    public function getUrgentNoticeReadRecordID()
    {
        return $this->__get(self::URGENT_NOTICE_READ_RECORD_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return UrgentNoticeReadRecord
     */
    public function setUrgentNoticeReadRecordID($value)
    {
        $this->__set(self::URGENT_NOTICE_READ_RECORD_ID, $value);

        return $this;
    }

    /**
     * Gets channel_article.channel_article_id
     * @return int
     */
    public function getChannelArticleID()
    {
        return $this->__get(self::CHANNEL_ARTICLE_ID);
    }

    /**
     * Sets channel_article.channel_article_id
     * @param int $value
     * @return UrgentNoticeReadRecord
     */
    public function setChannelArticleID($value)
    {
        $this->__set(self::CHANNEL_ARTICLE_ID, $value);

        return $this;
    }

    /**
     * Gets 微信用户openid
     * @return string
     */
    public function getWxUserID()
    {
        return $this->__get(self::WX_USER_ID);
    }

    /**
     * Sets 微信用户openid
     * @param string $value
     * @return UrgentNoticeReadRecord
     */
    public function setWxUserID($value)
    {
        $this->__set(self::WX_USER_ID, $value);

        return $this;
    }
}
?>