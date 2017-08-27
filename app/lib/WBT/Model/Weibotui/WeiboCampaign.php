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

class WeiboCampaign extends Model
{
    const WEIBO_CAMPAIGN_ID = 'weibo_campaign_id';
    const _TASK_ID = '_task_id';
    const NAME = 'name';
    const START_TIME = 'start_time';
    const END_TIME = 'end_time';
    const BUDGET = 'budget';
    const TOTAL_ORDER = 'total_order';
    const ACCEPTED_ORDER = 'accepted_order';
    const REFUSED_ORDER = 'refused_order';
    const EXPIRED_ORDER = 'expired_order';
    const PAID_ORDER = 'paid_order';
    const BAD_ORDER = 'bad_order';
    const ESTIMATE_COST = 'estimate_cost';
    const ESTIMATE_AUDIENCE = 'estimate_audience';
    const ACTUAL_COST = 'actual_cost';
    const ACTUAL_AUDIENCE = 'actual_audience';
    const TEXT = 'text';
    const IMAGE = 'image';
    const VIDEO = 'video';
    const RETWEETING_URL = 'retweeting_url';
    const COMMENT = 'comment';
    const USER = 'user';
    const TYPE = 'type';
    const STATUS = 'status';
    const UNPUBLISHED_TIME = 'unpublished_time';
    const PUBLISHED_TIME = 'published_time';
    const CLOSED_TIME = 'closed_time';
    const DELETED_TIME = 'deleted_time';
    const STATUS_LOG = 'status_log';

    const WITH_USER = 'weibo_campaign.user:user.user_id';

    const TO_PUBLISH = '_publish';
    const TO_CANCEL = '_cancel';
    const TO_ADD_INVENTORY = '_add_inventory';
    const TO_CLOSE = '_close';

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
                'weibo_campaign',
                'weibo_campaign_id',
                [
                    'weibo_campaign_id' => ['name' => _META_('weibotui.weibo_campaign.weibo_campaign_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    '_task_id' => ['name' => _META_('weibotui.weibo_campaign._task_id'), 'type' => 'int', 'no_input' => true],
                    'name' => ['name' => _META_('weibotui.weibo_campaign.name'), 'type' => 'text', 'length' => 100, 'required' => true],
                    'start_time' => ['name' => _META_('weibotui.weibo_campaign.start_time'), 'type' => 'datetime', 'required' => true],
                    'end_time' => ['name' => _META_('weibotui.weibo_campaign.end_time'), 'type' => 'datetime', 'required' => true],
                    'budget' => ['name' => _META_('weibotui.weibo_campaign.budget'), 'type' => 'money', 'precision' => 2],
                    'total_order' => ['name' => _META_('weibotui.weibo_campaign.total_order'), 'type' => 'int', 'db_insert' => true, 'no_input' => true, 'required' => true],
                    'accepted_order' => ['name' => _META_('weibotui.weibo_campaign.accepted_order'), 'type' => 'int', 'db_insert' => true, 'no_input' => true, 'required' => true],
                    'refused_order' => ['name' => _META_('weibotui.weibo_campaign.refused_order'), 'type' => 'int', 'db_insert' => true, 'no_input' => true, 'required' => true],
                    'expired_order' => ['name' => _META_('weibotui.weibo_campaign.expired_order'), 'type' => 'int', 'db_insert' => true, 'no_input' => true, 'required' => true],
                    'paid_order' => ['name' => _META_('weibotui.weibo_campaign.paid_order'), 'type' => 'int', 'db_insert' => true, 'no_input' => true, 'required' => true],
                    'bad_order' => ['name' => _META_('weibotui.weibo_campaign.bad_order'), 'type' => 'int', 'db_insert' => true, 'no_input' => true, 'required' => true],
                    'estimate_cost' => ['name' => _META_('weibotui.weibo_campaign.estimate_cost'), 'type' => 'money', 'db_insert' => true, 'no_input' => true, 'precision' => 2, 'required' => true],
                    'estimate_audience' => ['name' => _META_('weibotui.weibo_campaign.estimate_audience'), 'type' => 'int', 'length' => 20, 'db_insert' => true, 'no_input' => true, 'required' => true],
                    'actual_cost' => ['name' => _META_('weibotui.weibo_campaign.actual_cost'), 'type' => 'money', 'db_insert' => true, 'no_input' => true, 'precision' => 2, 'required' => true],
                    'actual_audience' => ['name' => _META_('weibotui.weibo_campaign.actual_audience'), 'type' => 'int', 'length' => 20, 'db_insert' => true, 'no_input' => true, 'required' => true],
                    'text' => ['name' => _META_('weibotui.weibo_campaign.text'), 'type' => 'text', 'max' => 200],
                    'image' => ['name' => _META_('weibotui.weibo_campaign.image'), 'type' => 'url'],
                    'video' => ['name' => _META_('weibotui.weibo_campaign.video'), 'type' => 'url'],
                    'retweeting_url' => ['name' => _META_('weibotui.weibo_campaign.retweeting_url'), 'type' => 'url'],
                    'comment' => ['name' => _META_('weibotui.weibo_campaign.comment'), 'type' => 'text'],
                    'user' => ['name' => _META_('weibotui.weibo_campaign.user'), 'type' => 'int', 'length' => 10, 'min' => 100000, 'required' => true, 'default' => new \Bluefin\Data\Functor\VarTextProvider("{{auth.weibotui.user_id}}"), 'rou' => true],
                    'type' => ['name' => _META_('weibotui.weibo_campaign.type'), 'type' => 'text', 'max' => 20, 'required' => true, 'rou' => true, 'enum' => new WeiboCampaignType(), 'db_insert' => true],
                    'status' => ['name' => _META_('weibotui.weibo_campaign.status'), 'type' => 'idname', 'required' => true, 'state' => new WeiboCampaignStatus(), 'db_insert' => true],
                    'unpublished_time' => ['name' => _META_('weibotui.weibo_campaign.unpublished_time'), 'type' => 'datetime'],
                    'published_time' => ['name' => _META_('weibotui.weibo_campaign.published_time'), 'type' => 'datetime'],
                    'closed_time' => ['name' => _META_('weibotui.weibo_campaign.closed_time'), 'type' => 'datetime'],
                    'deleted_time' => ['name' => _META_('weibotui.weibo_campaign.deleted_time'), 'type' => 'datetime'],
                    'status_log' => ['name' => _META_('weibotui.weibo_campaign.status_log'), 'type' => 'text', 'max' => 1000, 'default' => 'unpublished'],
                ],
                [
                    'auto_increment_id' => 'weibo_campaign_id',
                    'owner_field' => 'user',
                    'scheduled_task' => '_task_id',
                    'has_states' => 'status',
                ],
                [
                    'user' => self::WITH_USER,
                ],
                [
                ],
                [
                    Model::OP_CREATE => ['weibotui' => ['advertiser']],
                    Model::OP_GET => ['weibotui' => ['*owner*']],
                    Model::OP_UPDATE => NULL,
                    Model::OP_DELETE => NULL,
                    '_publish' => ['unpublished' => ['weibotui' => ['*owner*']], ],
                    '_cancel' => ['unpublished' => ['weibotui' => ['*owner*']], ],
                    '_add_inventory' => ['unpublished' => ['weibotui' => ['*owner*']], 'published' => ['weibotui' => ['*owner*']], ],
                    '_close' => ['published' => ['weibotui' => ['*system*']], ],
                ]
            );
        }

        return self::$__metadata;
    }

    /**
     * @param int $weiboCampaignID
     * @param array $params
     * @return \WBT\Model\Weibotui\WeiboCampaign
     * @throws \Bluefin\Exception\RequestException
     */
    public static function doPublish($weiboCampaignID, array $params = null)
    {
        App::getInstance()->log()->verbose('WeiboCampaign::doPublish', 'diag');

        if (is_array($weiboCampaignID))
        {
            $weiboCampaign = new WeiboCampaign();
            $weiboCampaign->populate($weiboCampaignID);
            $weiboCampaignID = $weiboCampaign->pk();
        }
        else
        {
            $weiboCampaign = new WeiboCampaign($weiboCampaignID);
        }
        _NON_EMPTY($weiboCampaign);

        $aclStatus = self::checkActionPermission(self::TO_PUBLISH, $weiboCampaign->data());
        if ($aclStatus !== Model::ACL_ACCEPTED)
        {
            if (ENV == 'dev')
            {
                throw new \Bluefin\Exception\RequestException(\Bluefin\Common::getStatusCodeMessage($aclStatus) . ' @ ' . __METHOD__, $aclStatus);
            }
            throw new \Bluefin\Exception\RequestException(null, $aclStatus);
        }

        $currentState = $weiboCampaign->getStatus();
        $methodName = "{$currentState}ToPublish";
        return self::$methodName($weiboCampaignID, $params, $weiboCampaign);
    }

    public static function unpublishedToPublish($weiboCampaignID, array $params = null, Model $cachedModel = null)
    {
        App::getInstance()->log()->verbose('WeiboCampaign::unpublishedToPublish', 'diag');

        $db = self::s_metadata()->getDatabase()->getAdapter();
        $db->beginTransaction();

        try
        {
            if (isset($cachedModel))
            {
                $weiboCampaign = $cachedModel;
            }
            else
            {
                $weiboCampaign = new WeiboCampaign($weiboCampaignID);
                _NON_EMPTY($weiboCampaign);

                $aclStatus = self::checkActionPermission(self::TO_PUBLISH, $weiboCampaign->data());
                if ($aclStatus !== Model::ACL_ACCEPTED)
                {
                    throw new \Bluefin\Exception\RequestException(null, $aclStatus);
                }

                $currentState = $weiboCampaign->getStatus();
                if ($currentState != 'unpublished')
                {
                    throw new \Bluefin\Exception\InvalidRequestException();
                }
            }

            //Set target state
            $weiboCampaign->setStatus(WeiboCampaignStatus::PUBLISHED);

            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, true);
            $affected = $weiboCampaign->update(['weibo_campaign_id' => $weiboCampaignID, 'status' => 'unpublished']);
            if ($affected <= 0)
            {
                App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);
                throw new \Bluefin\Exception\DataException(_APP_("The record to operate is not in expected state."));
            }

            $weiboCampaign->_afterPublished();
            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();

            throw $e;
        }

        return $weiboCampaign;
    }

    /**
     * @param int $weiboCampaignID
     * @param array $params
     * @return \WBT\Model\Weibotui\WeiboCampaign
     * @throws \Bluefin\Exception\RequestException
     */
    public static function doCancel($weiboCampaignID, array $params = null)
    {
        App::getInstance()->log()->verbose('WeiboCampaign::doCancel', 'diag');

        if (is_array($weiboCampaignID))
        {
            $weiboCampaign = new WeiboCampaign();
            $weiboCampaign->populate($weiboCampaignID);
            $weiboCampaignID = $weiboCampaign->pk();
        }
        else
        {
            $weiboCampaign = new WeiboCampaign($weiboCampaignID);
        }
        _NON_EMPTY($weiboCampaign);

        $aclStatus = self::checkActionPermission(self::TO_CANCEL, $weiboCampaign->data());
        if ($aclStatus !== Model::ACL_ACCEPTED)
        {
            if (ENV == 'dev')
            {
                throw new \Bluefin\Exception\RequestException(\Bluefin\Common::getStatusCodeMessage($aclStatus) . ' @ ' . __METHOD__, $aclStatus);
            }
            throw new \Bluefin\Exception\RequestException(null, $aclStatus);
        }

        $currentState = $weiboCampaign->getStatus();
        $methodName = "{$currentState}ToCancel";
        return self::$methodName($weiboCampaignID, $params, $weiboCampaign);
    }

    public static function unpublishedToCancel($weiboCampaignID, array $params = null, Model $cachedModel = null)
    {
        App::getInstance()->log()->verbose('WeiboCampaign::unpublishedToCancel', 'diag');

        $db = self::s_metadata()->getDatabase()->getAdapter();
        $db->beginTransaction();

        try
        {
            if (isset($cachedModel))
            {
                $weiboCampaign = $cachedModel;
            }
            else
            {
                $weiboCampaign = new WeiboCampaign($weiboCampaignID);
                _NON_EMPTY($weiboCampaign);

                $aclStatus = self::checkActionPermission(self::TO_CANCEL, $weiboCampaign->data());
                if ($aclStatus !== Model::ACL_ACCEPTED)
                {
                    throw new \Bluefin\Exception\RequestException(null, $aclStatus);
                }

                $currentState = $weiboCampaign->getStatus();
                if ($currentState != 'unpublished')
                {
                    throw new \Bluefin\Exception\InvalidRequestException();
                }
            }

            //Set target state
            $weiboCampaign->setStatus(WeiboCampaignStatus::DELETED);

            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, true);
            $affected = $weiboCampaign->update(['weibo_campaign_id' => $weiboCampaignID, 'status' => 'unpublished']);
            if ($affected <= 0)
            {
                App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);
                throw new \Bluefin\Exception\DataException(_APP_("The record to operate is not in expected state."));
            }

            $weiboCampaign->_afterDeleted();
            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, false);

            $db->commit();
        }
        catch (\Exception $e)
        {
            $db->rollback();

            throw $e;
        }

        return $weiboCampaign;
    }

    /**
     * @param int $weiboCampaignID
     * @param array $params
     * @return \WBT\Model\Weibotui\WeiboCampaign
     * @throws \Bluefin\Exception\RequestException
     */
    public static function doClose($weiboCampaignID, array $params = null)
    {
        App::getInstance()->log()->verbose('WeiboCampaign::doClose', 'diag');

        if (is_array($weiboCampaignID))
        {
            $weiboCampaign = new WeiboCampaign();
            $weiboCampaign->populate($weiboCampaignID);
            $weiboCampaignID = $weiboCampaign->pk();
        }
        else
        {
            $weiboCampaign = new WeiboCampaign($weiboCampaignID);
        }
        _NON_EMPTY($weiboCampaign);

        $aclStatus = self::checkActionPermission(self::TO_CLOSE, $weiboCampaign->data());
        if ($aclStatus !== Model::ACL_ACCEPTED)
        {
            if (ENV == 'dev')
            {
                throw new \Bluefin\Exception\RequestException(\Bluefin\Common::getStatusCodeMessage($aclStatus) . ' @ ' . __METHOD__, $aclStatus);
            }
            throw new \Bluefin\Exception\RequestException(null, $aclStatus);
        }

        $currentState = $weiboCampaign->getStatus();
        $methodName = "{$currentState}ToClose";
        return self::$methodName($weiboCampaignID, $params, $weiboCampaign);
    }

    public static function publishedToClose($weiboCampaignID, array $params = null, Model $cachedModel = null)
    {
        App::getInstance()->log()->verbose('WeiboCampaign::publishedToClose', 'diag');

        $db = self::s_metadata()->getDatabase()->getAdapter();
        $db->beginTransaction();

        try
        {
            if (isset($cachedModel))
            {
                $weiboCampaign = $cachedModel;
            }
            else
            {
                $weiboCampaign = new WeiboCampaign($weiboCampaignID);
                _NON_EMPTY($weiboCampaign);

                $aclStatus = self::checkActionPermission(self::TO_CLOSE, $weiboCampaign->data());
                if ($aclStatus !== Model::ACL_ACCEPTED)
                {
                    throw new \Bluefin\Exception\RequestException(null, $aclStatus);
                }

                $currentState = $weiboCampaign->getStatus();
                if ($currentState != 'published')
                {
                    throw new \Bluefin\Exception\InvalidRequestException();
                }
            }

            //Set target state
            $weiboCampaign->setStatus(WeiboCampaignStatus::CLOSED);

            App::getInstance()->setRegistry(Convention::KEYWORD_SYSTEM_ROLE, true);
            $affected = $weiboCampaign->update(['weibo_campaign_id' => $weiboCampaignID, 'status' => 'published']);
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

        return $weiboCampaign;
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
     * Gets id
     * @return int
     */
    public function getWeiboCampaignID()
    {
        return $this->__get(self::WEIBO_CAMPAIGN_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return WeiboCampaign
     */
    public function setWeiboCampaignID($value)
    {
        $this->__set(self::WEIBO_CAMPAIGN_ID, $value);

        return $this;
    }

    /**
     * Gets taskid
     * @return int
     */
    public function getTaskID()
    {
        return $this->__get(self::_TASK_ID);
    }

    /**
     * Sets taskid
     * @param int $value
     * @return WeiboCampaign
     */
    public function setTaskID($value)
    {
        $this->__set(self::_TASK_ID, $value);

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
     * @return WeiboCampaign
     */
    public function setName($value)
    {
        $this->__set(self::NAME, $value);

        return $this;
    }

    /**
     * Gets 起始时间
     * @return string
     */
    public function getStartTime()
    {
        return $this->__get(self::START_TIME);
    }

    /**
     * Sets 起始时间
     * @param string $value
     * @return WeiboCampaign
     */
    public function setStartTime($value)
    {
        $this->__set(self::START_TIME, $value);

        return $this;
    }

    /**
     * Gets 结束时间
     * @return string
     */
    public function getEndTime()
    {
        return $this->__get(self::END_TIME);
    }

    /**
     * Sets 结束时间
     * @param string $value
     * @return WeiboCampaign
     */
    public function setEndTime($value)
    {
        $this->__set(self::END_TIME, $value);

        return $this;
    }

    /**
     * Gets 预算
     * @return float
     */
    public function getBudget()
    {
        return $this->__get(self::BUDGET);
    }

    /**
     * Sets 预算
     * @param float $value
     * @return WeiboCampaign
     */
    public function setBudget($value)
    {
        $this->__set(self::BUDGET, $value);

        return $this;
    }

    /**
     * Gets 总单数
     * @return int
     */
    public function getTotalOrder()
    {
        return $this->__get(self::TOTAL_ORDER);
    }

    /**
     * Sets 总单数
     * @param int $value
     * @return WeiboCampaign
     */
    public function setTotalOrder($value)
    {
        $this->__set(self::TOTAL_ORDER, $value);

        return $this;
    }

    /**
     * Gets 接单数
     * @return int
     */
    public function getAcceptedOrder()
    {
        return $this->__get(self::ACCEPTED_ORDER);
    }

    /**
     * Sets 接单数
     * @param int $value
     * @return WeiboCampaign
     */
    public function setAcceptedOrder($value)
    {
        $this->__set(self::ACCEPTED_ORDER, $value);

        return $this;
    }

    /**
     * Gets 拒单数
     * @return int
     */
    public function getRefusedOrder()
    {
        return $this->__get(self::REFUSED_ORDER);
    }

    /**
     * Sets 拒单数
     * @param int $value
     * @return WeiboCampaign
     */
    public function setRefusedOrder($value)
    {
        $this->__set(self::REFUSED_ORDER, $value);

        return $this;
    }

    /**
     * Gets 流单数
     * @return int
     */
    public function getExpiredOrder()
    {
        return $this->__get(self::EXPIRED_ORDER);
    }

    /**
     * Sets 流单数
     * @param int $value
     * @return WeiboCampaign
     */
    public function setExpiredOrder($value)
    {
        $this->__set(self::EXPIRED_ORDER, $value);

        return $this;
    }

    /**
     * Gets 结单数
     * @return int
     */
    public function getPaidOrder()
    {
        return $this->__get(self::PAID_ORDER);
    }

    /**
     * Sets 结单数
     * @param int $value
     * @return WeiboCampaign
     */
    public function setPaidOrder($value)
    {
        $this->__set(self::PAID_ORDER, $value);

        return $this;
    }

    /**
     * Gets 坏单数
     * @return int
     */
    public function getBadOrder()
    {
        return $this->__get(self::BAD_ORDER);
    }

    /**
     * Sets 坏单数
     * @param int $value
     * @return WeiboCampaign
     */
    public function setBadOrder($value)
    {
        $this->__set(self::BAD_ORDER, $value);

        return $this;
    }

    /**
     * Gets 预计支出
     * @return float
     */
    public function getEstimateCost()
    {
        return $this->__get(self::ESTIMATE_COST);
    }

    /**
     * Sets 预计支出
     * @param float $value
     * @return WeiboCampaign
     */
    public function setEstimateCost($value)
    {
        $this->__set(self::ESTIMATE_COST, $value);

        return $this;
    }

    /**
     * Gets 预计受众人数
     * @return int
     */
    public function getEstimateAudience()
    {
        return $this->__get(self::ESTIMATE_AUDIENCE);
    }

    /**
     * Sets 预计受众人数
     * @param int $value
     * @return WeiboCampaign
     */
    public function setEstimateAudience($value)
    {
        $this->__set(self::ESTIMATE_AUDIENCE, $value);

        return $this;
    }

    /**
     * Gets 实际支出
     * @return float
     */
    public function getActualCost()
    {
        return $this->__get(self::ACTUAL_COST);
    }

    /**
     * Sets 实际支出
     * @param float $value
     * @return WeiboCampaign
     */
    public function setActualCost($value)
    {
        $this->__set(self::ACTUAL_COST, $value);

        return $this;
    }

    /**
     * Gets 实际受众人数
     * @return int
     */
    public function getActualAudience()
    {
        return $this->__get(self::ACTUAL_AUDIENCE);
    }

    /**
     * Sets 实际受众人数
     * @param int $value
     * @return WeiboCampaign
     */
    public function setActualAudience($value)
    {
        $this->__set(self::ACTUAL_AUDIENCE, $value);

        return $this;
    }

    /**
     * Gets 文本内容
     * @return string
     */
    public function getText()
    {
        return $this->__get(self::TEXT);
    }

    /**
     * Sets 文本内容
     * @param string $value
     * @return WeiboCampaign
     */
    public function setText($value)
    {
        $this->__set(self::TEXT, $value);

        return $this;
    }

    /**
     * Gets 图片链接
     * @return string
     */
    public function getImage()
    {
        return $this->__get(self::IMAGE);
    }

    /**
     * Sets 图片链接
     * @param string $value
     * @return WeiboCampaign
     */
    public function setImage($value)
    {
        $this->__set(self::IMAGE, $value);

        return $this;
    }

    /**
     * Gets 视频链接
     * @return string
     */
    public function getVideo()
    {
        return $this->__get(self::VIDEO);
    }

    /**
     * Sets 视频链接
     * @param string $value
     * @return WeiboCampaign
     */
    public function setVideo($value)
    {
        $this->__set(self::VIDEO, $value);

        return $this;
    }

    /**
     * Gets 转发链接
     * @return string
     */
    public function getRetweetingUrl()
    {
        return $this->__get(self::RETWEETING_URL);
    }

    /**
     * Sets 转发链接
     * @param string $value
     * @return WeiboCampaign
     */
    public function setRetweetingUrl($value)
    {
        $this->__set(self::RETWEETING_URL, $value);

        return $this;
    }

    /**
     * Gets 附加要求
     * @return string
     */
    public function getComment()
    {
        return $this->__get(self::COMMENT);
    }

    /**
     * Sets 附加要求
     * @param string $value
     * @return WeiboCampaign
     */
    public function setComment($value)
    {
        $this->__set(self::COMMENT, $value);

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
     * @return WeiboCampaign
     */
    public function setUser($value)
    {
        $this->__set(self::USER, $value);

        return $this;
    }

    /**
     * Gets 活动类型
     * @return string
     */
    public function getType()
    {
        return $this->__get(self::TYPE);
    }

    /**
     * Gets 活动类型 display name
     * @return string
     */
    public function getType_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('type');
        return $option['enum']::getDisplayName($this->__get(self::TYPE));
    }

    /**
     * Sets 活动类型
     * @param string $value
     * @return WeiboCampaign
     */
    public function setType($value)
    {
        $this->__set(self::TYPE, $value);

        return $this;
    }

    /**
     * Gets 状态
     * @return string
     */
    public function getStatus()
    {
        return $this->__get(self::STATUS);
    }

    /**
     * Gets 状态 display name
     * @return string
     */
    public function getStatus_StateValue()
    {
        $option = $this->metadata()->getFilterOption('status');
        return $option['state']::getDisplayName($this->__get(self::STATUS));
    }

    /**
     * Sets 状态
     * @param string $value
     * @return WeiboCampaign
     */
    public function setStatus($value)
    {
        $this->__set(self::STATUS, $value);

        return $this;
    }

    /**
     * Gets 未发布时间
     * @return string
     */
    public function getUnpublishedTime()
    {
        return $this->__get(self::UNPUBLISHED_TIME);
    }

    /**
     * Sets 未发布时间
     * @param string $value
     * @return WeiboCampaign
     */
    public function setUnpublishedTime($value)
    {
        $this->__set(self::UNPUBLISHED_TIME, $value);

        return $this;
    }

    /**
     * Gets 已发布时间
     * @return string
     */
    public function getPublishedTime()
    {
        return $this->__get(self::PUBLISHED_TIME);
    }

    /**
     * Sets 已发布时间
     * @param string $value
     * @return WeiboCampaign
     */
    public function setPublishedTime($value)
    {
        $this->__set(self::PUBLISHED_TIME, $value);

        return $this;
    }

    /**
     * Gets 已结束时间
     * @return string
     */
    public function getClosedTime()
    {
        return $this->__get(self::CLOSED_TIME);
    }

    /**
     * Sets 已结束时间
     * @param string $value
     * @return WeiboCampaign
     */
    public function setClosedTime($value)
    {
        $this->__set(self::CLOSED_TIME, $value);

        return $this;
    }

    /**
     * Gets 已删除时间
     * @return string
     */
    public function getDeletedTime()
    {
        return $this->__get(self::DELETED_TIME);
    }

    /**
     * Sets 已删除时间
     * @param string $value
     * @return WeiboCampaign
     */
    public function setDeletedTime($value)
    {
        $this->__set(self::DELETED_TIME, $value);

        return $this;
    }

    /**
     * Gets 状态历史
     * @return string
     */
    public function getStatusLog()
    {
        return $this->__get(self::STATUS_LOG);
    }

    /**
     * Sets 状态历史
     * @param string $value
     * @return WeiboCampaign
     */
    public function setStatusLog($value)
    {
        $this->__set(self::STATUS_LOG, $value);

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

    protected function _afterPublished(array $INPUT = null)
    {
        App::getInstance()->log()->verbose('WeiboCampaign::_afterPublished', 'diag');    
        $orders = WeiboOrder::fetchRows('*', ['campaign' => $this->pk()]);
        
        foreach ($orders as $order)
        {
            WeiboOrder::doPublish($order);
        }
        
        \WBT\Business\SystemBusiness::postTimerCall(
            $this,
            $this->end_time,
            'WeiboCampaign::doClose',
            [$this->pk()]
        );
        
    }

    protected function _afterDeleted(array $INPUT = null)
    {
        App::getInstance()->log()->verbose('WeiboCampaign::_afterDeleted', 'diag');    
        $orders = WeiboOrder::fetchRows('*', ['campaign' => $this->pk()]);
        
        foreach ($orders as $order)
        {
            WeiboOrder::doCancel($order);
        }
        
        \WBT\Business\SystemBusiness::cancelTimerCall($this);
        
    }
}
?>