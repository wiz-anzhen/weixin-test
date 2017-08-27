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

class MpRule extends Model
{
    const MP_RULE_ID = 'mp_rule_id';
    const _CREATED_AT = '_created_at';
    const _UPDATED_AT = '_updated_at';
    const MP_USER_ID = 'mp_user_id';
    const NAME = 'name';
    const KEYWORD = 'keyword';
    const CONTENT = 'content';
    const CONTENT_TYPE = 'content_type';



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
                'mp_rule',
                'mp_rule_id',
                [
                    'mp_rule_id' => ['name' => _META_('mp.mp_rule.mp_rule_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    '_created_at' => ['name' => _META_('mp.mp_rule._created_at'), 'type' => 'timestamp', 'required' => true, 'default' => new DbExpr('NOW()'), 'roc' => true, 'rou' => true],
                    '_updated_at' => ['name' => _META_('mp.mp_rule._updated_at'), 'type' => 'timestamp', 'required' => true, 'db_insert' => true, 'roc' => true, 'rou' => true],
                    'mp_user_id' => ['name' => _META_('mp.mp_rule.mp_user_id'), 'type' => 'int', 'length' => 10, 'db_insert' => true, 'required' => true],
                    'name' => ['name' => _META_('mp.mp_rule.name'), 'type' => 'text', 'length' => 64, 'required' => true],
                    'keyword' => ['name' => _META_('mp.mp_rule.keyword'), 'type' => 'text', 'required' => true],
                    'content' => ['name' => _META_('mp.mp_rule.content'), 'type' => 'text', 'required' => true],
                    'content_type' => ['name' => _META_('mp.mp_rule.content_type'), 'type' => 'text', 'max' => 32, 'required' => true, 'db_insert' => true, 'enum' => new WeixinMessageType()],
                ],
                [
                    'auto_increment_id' => 'mp_rule_id',
                    'create_timestamp' => '_created_at',
                    'update_timestamp' => '_updated_at',
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
    public function getMpRuleID()
    {
        return $this->__get(self::MP_RULE_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return MpRule
     */
    public function setMpRuleID($value)
    {
        $this->__set(self::MP_RULE_ID, $value);

        return $this;
    }

    /**
     * Gets createdat
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->__get(self::_CREATED_AT);
    }

    /**
     * Sets createdat
     * @param string $value
     * @return MpRule
     */
    public function setCreatedAt($value)
    {
        $this->__set(self::_CREATED_AT, $value);

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
     * @return MpRule
     */
    public function setUpdatedAt($value)
    {
        $this->__set(self::_UPDATED_AT, $value);

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
     * @return MpRule
     */
    public function setMpUserID($value)
    {
        $this->__set(self::MP_USER_ID, $value);

        return $this;
    }

    /**
     * Gets 规则名称
     * @return string
     */
    public function getName()
    {
        return $this->__get(self::NAME);
    }

    /**
     * Sets 规则名称
     * @param string $value
     * @return MpRule
     */
    public function setName($value)
    {
        $this->__set(self::NAME, $value);

        return $this;
    }

    /**
     * Gets 规则关键词
     * @return string
     */
    public function getKeyword()
    {
        return $this->__get(self::KEYWORD);
    }

    /**
     * Sets 规则关键词
     * @param string $value
     * @return MpRule
     */
    public function setKeyword($value)
    {
        $this->__set(self::KEYWORD, $value);

        return $this;
    }

    /**
     * Gets 规则内容
     * @return string
     */
    public function getContent()
    {
        return $this->__get(self::CONTENT);
    }

    /**
     * Sets 规则内容
     * @param string $value
     * @return MpRule
     */
    public function setContent($value)
    {
        $this->__set(self::CONTENT, $value);

        return $this;
    }

    /**
     * Gets 微信消息类型
     * @return string
     */
    public function getContentType()
    {
        return $this->__get(self::CONTENT_TYPE);
    }

    /**
     * Gets 微信消息类型 display name
     * @return string
     */
    public function getContentType_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('content_type');
        return $option['enum']::getDisplayName($this->__get(self::CONTENT_TYPE));
    }

    /**
     * Sets 微信消息类型
     * @param string $value
     * @return MpRule
     */
    public function setContentType($value)
    {
        $this->__set(self::CONTENT_TYPE, $value);

        return $this;
    }
}
?>