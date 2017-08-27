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

class PushMessage extends Model
{
    const PUSH_MESSAGE_ID = 'push_message_id';
    const MP_USER_ID = 'mp_user_id';
    const COMMUNITY_ID = 'community_id';
    const TITLE = 'title';
    const CONTENT = 'content';
    const INFOID = 'infoid';
    const CREATE_TIME = 'create_time';
    const SEND_NO = 'send_no';
    const SEND_TIME = 'send_time';
    const SEND_AUTHOR = 'send_author';
    const SEND_TYPE = 'send_type';
    const SEND_STATUS = 'send_status';
    const SEND_RANGE = 'send_range';



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
                'push_message',
                'push_message_id',
                [
                    'push_message_id' => ['name' => _META_('mp.push_message.push_message_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'mp_user_id' => ['name' => _META_('mp.push_message.mp_user_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'community_id' => ['name' => _META_('mp.push_message.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'title' => ['name' => _META_('mp.push_message.title'), 'type' => 'text', 'length' => 64, 'required' => true],
                    'content' => ['name' => _META_('mp.push_message.content'), 'type' => 'text', 'length' => 64, 'required' => true],
                    'infoid' => ['name' => _META_('mp.push_message.infoid'), 'type' => 'text', 'length' => 64],
                    'create_time' => ['name' => _META_('mp.push_message.create_time'), 'type' => 'datetime', 'required' => true],
                    'send_no' => ['name' => _META_('mp.push_message.send_no'), 'type' => 'text'],
                    'send_time' => ['name' => _META_('mp.push_message.send_time'), 'type' => 'datetime'],
                    'send_author' => ['name' => _META_('mp.push_message.send_author'), 'type' => 'text', 'length' => 128],
                    'send_type' => ['name' => _META_('mp.push_message.send_type'), 'type' => 'text', 'length' => 32],
                    'send_status' => ['name' => _META_('mp.push_message.send_status'), 'type' => 'text', 'max' => 32, 'enum' => new UserNotifySendStatus(), 'db_insert' => true],
                    'send_range' => ['name' => _META_('mp.push_message.send_range'), 'type' => 'text', 'max' => 32, 'required' => true, 'enum' => new UserNotifySendRangeType(), 'db_insert' => true],
                ],
                [
                    'auto_increment_id' => 'push_message_id',
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
    public function getPushMessageID()
    {
        return $this->__get(self::PUSH_MESSAGE_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return PushMessage
     */
    public function setPushMessageID($value)
    {
        $this->__set(self::PUSH_MESSAGE_ID, $value);

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
     * @return PushMessage
     */
    public function setMpUserID($value)
    {
        $this->__set(self::MP_USER_ID, $value);

        return $this;
    }

    /**
     * Gets community.community_id
     * @return int
     */
    public function getCommunityID()
    {
        return $this->__get(self::COMMUNITY_ID);
    }

    /**
     * Sets community.community_id
     * @param int $value
     * @return PushMessage
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets 通知标题
     * @return string
     */
    public function getTitle()
    {
        return $this->__get(self::TITLE);
    }

    /**
     * Sets 通知标题
     * @param string $value
     * @return PushMessage
     */
    public function setTitle($value)
    {
        $this->__set(self::TITLE, $value);

        return $this;
    }

    /**
     * Gets 通知内容
     * @return string
     */
    public function getContent()
    {
        return $this->__get(self::CONTENT);
    }

    /**
     * Sets 通知内容
     * @param string $value
     * @return PushMessage
     */
    public function setContent($value)
    {
        $this->__set(self::CONTENT, $value);

        return $this;
    }

    /**
     * Gets 信息编号/来源
     * @return string
     */
    public function getInfoid()
    {
        return $this->__get(self::INFOID);
    }

    /**
     * Sets 信息编号/来源
     * @param string $value
     * @return PushMessage
     */
    public function setInfoid($value)
    {
        $this->__set(self::INFOID, $value);

        return $this;
    }

    /**
     * Gets 创建时间
     * @return string
     */
    public function getCreateTime()
    {
        return $this->__get(self::CREATE_TIME);
    }

    /**
     * Sets 创建时间
     * @param string $value
     * @return PushMessage
     */
    public function setCreateTime($value)
    {
        $this->__set(self::CREATE_TIME, $value);

        return $this;
    }

    /**
     * Gets 指定房间编号
     * @return string
     */
    public function getSendNo()
    {
        return $this->__get(self::SEND_NO);
    }

    /**
     * Sets 指定房间编号
     * @param string $value
     * @return PushMessage
     */
    public function setSendNo($value)
    {
        $this->__set(self::SEND_NO, $value);

        return $this;
    }

    /**
     * Gets 发布时间
     * @return string
     */
    public function getSendTime()
    {
        return $this->__get(self::SEND_TIME);
    }

    /**
     * Sets 发布时间
     * @param string $value
     * @return PushMessage
     */
    public function setSendTime($value)
    {
        $this->__set(self::SEND_TIME, $value);

        return $this;
    }

    /**
     * Gets 发布者
     * @return string
     */
    public function getSendAuthor()
    {
        return $this->__get(self::SEND_AUTHOR);
    }

    /**
     * Sets 发布者
     * @param string $value
     * @return PushMessage
     */
    public function setSendAuthor($value)
    {
        $this->__set(self::SEND_AUTHOR, $value);

        return $this;
    }

    /**
     * Gets 发布类型
     * @return string
     */
    public function getSendType()
    {
        return $this->__get(self::SEND_TYPE);
    }

    /**
     * Sets 发布类型
     * @param string $value
     * @return PushMessage
     */
    public function setSendType($value)
    {
        $this->__set(self::SEND_TYPE, $value);

        return $this;
    }

    /**
     * Gets 向用户发送模板消息通知发布状态
     * @return string
     */
    public function getSendStatus()
    {
        return $this->__get(self::SEND_STATUS);
    }

    /**
     * Gets 向用户发送模板消息通知发布状态 display name
     * @return string
     */
    public function getSendStatus_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('send_status');
        return $option['enum']::getDisplayName($this->__get(self::SEND_STATUS));
    }

    /**
     * Sets 向用户发送模板消息通知发布状态
     * @param string $value
     * @return PushMessage
     */
    public function setSendStatus($value)
    {
        $this->__set(self::SEND_STATUS, $value);

        return $this;
    }

    /**
     * Gets 向用户发送模板消息通知范围类型
     * @return string
     */
    public function getSendRange()
    {
        return $this->__get(self::SEND_RANGE);
    }

    /**
     * Gets 向用户发送模板消息通知范围类型 display name
     * @return string
     */
    public function getSendRange_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('send_range');
        return $option['enum']::getDisplayName($this->__get(self::SEND_RANGE));
    }

    /**
     * Sets 向用户发送模板消息通知范围类型
     * @param string $value
     * @return PushMessage
     */
    public function setSendRange($value)
    {
        $this->__set(self::SEND_RANGE, $value);

        return $this;
    }
}
?>