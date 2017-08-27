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

class WeiboTopic extends Model
{
    const WEIBO_TOPIC_ID = 'weibo_topic_id';
    const CONFIDENCE = 'confidence';
    const WEIBO = 'weibo';
    const CATETORY = 'catetory';

    const WITH_WEIBO = 'weibo_topic.weibo:weibo.weibo_id';
    const WITH_CATETORY = 'weibo_topic.catetory:topic_category.code';


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
                'weibo_topic',
                'weibo_topic_id',
                [
                    'weibo_topic_id' => ['name' => _META_('weibotui.weibo_topic.weibo_topic_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'confidence' => ['name' => _META_('weibotui.weibo_topic.confidence'), 'type' => 'float', 'required' => true],
                    'weibo' => ['name' => _META_('weibotui.weibo_topic.weibo'), 'type' => 'uuid', 'required' => true],
                    'catetory' => ['name' => _META_('weibotui.weibo_topic.catetory'), 'type' => 'idname', 'required' => true],
                ],
                [
                    'auto_increment_id' => 'weibo_topic_id',
                    'unique_keys' => [['weibo', 'catetory']],
                ],
                [
                    'weibo' => self::WITH_WEIBO,
                    'catetory' => self::WITH_CATETORY,
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
    public function getWeiboTopicID()
    {
        return $this->__get(self::WEIBO_TOPIC_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return WeiboTopic
     */
    public function setWeiboTopicID($value)
    {
        $this->__set(self::WEIBO_TOPIC_ID, $value);

        return $this;
    }

    /**
     * Gets 置信度
     * @return float
     */
    public function getConfidence()
    {
        return $this->__get(self::CONFIDENCE);
    }

    /**
     * Sets 置信度
     * @param float $value
     * @return WeiboTopic
     */
    public function setConfidence($value)
    {
        $this->__set(self::CONFIDENCE, $value);

        return $this;
    }

    /**
     * Gets 微博账号
     * @return string
     */
    public function getWeibo()
    {
        return $this->__get(self::WEIBO);
    }

    /**
     * Sets 微博账号
     * @param string $value
     * @return WeiboTopic
     */
    public function setWeibo($value)
    {
        $this->__set(self::WEIBO, $value);

        return $this;
    }

    /**
     * Gets 话题类别
     * @return string
     */
    public function getCatetory()
    {
        return $this->__get(self::CATETORY);
    }

    /**
     * Sets 话题类别
     * @param string $value
     * @return WeiboTopic
     */
    public function setCatetory($value)
    {
        $this->__set(self::CATETORY, $value);

        return $this;
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\Weibo
     */
    public function getWeibo_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\Weibo();
        }

        if (isset($this->_links['weibo']))
        {
            return $this->_links['weibo'];
        }

        return ($this->_links['weibo'] = new \WBT\Model\Weibotui\Weibo($this->getWeibo()));
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\TopicCategory
     */
    public function getCatetory_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\TopicCategory();
        }

        if (isset($this->_links['catetory']))
        {
            return $this->_links['catetory'];
        }

        return ($this->_links['catetory'] = new \WBT\Model\Weibotui\TopicCategory($this->getCatetory()));
    }
}
?>