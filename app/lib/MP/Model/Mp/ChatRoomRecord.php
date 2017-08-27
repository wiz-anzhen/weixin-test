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

class ChatRoomRecord extends Model
{
    const CHAT_ROOM_RECORD_ID = 'chat_room_record_id';
    const WX_USER_ID = 'wx_user_id';
    const MP_USER_ID = 'mp_user_id';
    const WX_USER_NAME = 'wx_user_name';
    const COMMUNITY_ID = 'community_id';
    const VIP_NO = 'vip_no';
    const GROUP_NAME = 'group_name';
    const CS_NAME = 'cs_name';
    const CONTENT_VALUE = 'content_value';
    const CS_GROUP_ID = 'cs_group_id';
    const CS_ID = 'cs_id';
    const RECORD_TIME = 'record_time';
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
                'chat_room_record',
                'chat_room_record_id',
                [
                    'chat_room_record_id' => ['name' => _META_('mp.chat_room_record.chat_room_record_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    'wx_user_id' => ['name' => _META_('mp.chat_room_record.wx_user_id'), 'type' => 'text', 'length' => 64, 'required' => true],
                    'mp_user_id' => ['name' => _META_('mp.chat_room_record.mp_user_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'wx_user_name' => ['name' => _META_('mp.chat_room_record.wx_user_name'), 'type' => 'text', 'length' => 128],
                    'community_id' => ['name' => _META_('mp.chat_room_record.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'vip_no' => ['name' => _META_('mp.chat_room_record.vip_no'), 'type' => 'int', 'length' => 20, 'required' => true],
                    'group_name' => ['name' => _META_('mp.chat_room_record.group_name'), 'type' => 'text', 'length' => 128],
                    'cs_name' => ['name' => _META_('mp.chat_room_record.cs_name'), 'type' => 'text', 'length' => 128],
                    'content_value' => ['name' => _META_('mp.chat_room_record.content_value'), 'type' => 'text', 'required' => true],
                    'cs_group_id' => ['name' => _META_('mp.chat_room_record.cs_group_id'), 'type' => 'int', 'length' => 10],
                    'cs_id' => ['name' => _META_('mp.chat_room_record.cs_id'), 'type' => 'int', 'length' => 10],
                    'record_time' => ['name' => _META_('mp.chat_room_record.record_time'), 'type' => 'datetime', 'required' => true],
                    'content_type' => ['name' => _META_('mp.chat_room_record.content_type'), 'type' => 'text', 'max' => 32, 'required' => true, 'enum' => new ReocrdContentType(), 'db_insert' => true],
                ],
                [
                    'auto_increment_id' => 'chat_room_record_id',
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
    public function getChatRoomRecordID()
    {
        return $this->__get(self::CHAT_ROOM_RECORD_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return ChatRoomRecord
     */
    public function setChatRoomRecordID($value)
    {
        $this->__set(self::CHAT_ROOM_RECORD_ID, $value);

        return $this;
    }

    /**
     * Gets 用户id
     * @return string
     */
    public function getWxUserID()
    {
        return $this->__get(self::WX_USER_ID);
    }

    /**
     * Sets 用户id
     * @param string $value
     * @return ChatRoomRecord
     */
    public function setWxUserID($value)
    {
        $this->__set(self::WX_USER_ID, $value);

        return $this;
    }

    /**
     * Gets 
     * @return int
     */
    public function getMpUserID()
    {
        return $this->__get(self::MP_USER_ID);
    }

    /**
     * Sets 
     * @param int $value
     * @return ChatRoomRecord
     */
    public function setMpUserID($value)
    {
        $this->__set(self::MP_USER_ID, $value);

        return $this;
    }

    /**
     * Gets 用户名称
     * @return string
     */
    public function getWxUserName()
    {
        return $this->__get(self::WX_USER_NAME);
    }

    /**
     * Sets 用户名称
     * @param string $value
     * @return ChatRoomRecord
     */
    public function setWxUserName($value)
    {
        $this->__set(self::WX_USER_NAME, $value);

        return $this;
    }

    /**
     * Gets 用户所在社区id
     * @return int
     */
    public function getCommunityID()
    {
        return $this->__get(self::COMMUNITY_ID);
    }

    /**
     * Sets 用户所在社区id
     * @param int $value
     * @return ChatRoomRecord
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets 客服专员会员号id
     * @return int
     */
    public function getVipNo()
    {
        return $this->__get(self::VIP_NO);
    }

    /**
     * Sets 客服专员会员号id
     * @param int $value
     * @return ChatRoomRecord
     */
    public function setVipNo($value)
    {
        $this->__set(self::VIP_NO, $value);

        return $this;
    }

    /**
     * Gets 客服组名称
     * @return string
     */
    public function getGroupName()
    {
        return $this->__get(self::GROUP_NAME);
    }

    /**
     * Sets 客服组名称
     * @param string $value
     * @return ChatRoomRecord
     */
    public function setGroupName($value)
    {
        $this->__set(self::GROUP_NAME, $value);

        return $this;
    }

    /**
     * Gets 
     * @return string
     */
    public function getCsName()
    {
        return $this->__get(self::CS_NAME);
    }

    /**
     * Sets 
     * @param string $value
     * @return ChatRoomRecord
     */
    public function setCsName($value)
    {
        $this->__set(self::CS_NAME, $value);

        return $this;
    }

    /**
     * Gets 聊天记录
     * @return string
     */
    public function getContentValue()
    {
        return $this->__get(self::CONTENT_VALUE);
    }

    /**
     * Sets 聊天记录
     * @param string $value
     * @return ChatRoomRecord
     */
    public function setContentValue($value)
    {
        $this->__set(self::CONTENT_VALUE, $value);

        return $this;
    }

    /**
     * Gets 客服专员分组id
     * @return int
     */
    public function getCsGroupID()
    {
        return $this->__get(self::CS_GROUP_ID);
    }

    /**
     * Sets 客服专员分组id
     * @param int $value
     * @return ChatRoomRecord
     */
    public function setCsGroupID($value)
    {
        $this->__set(self::CS_GROUP_ID, $value);

        return $this;
    }

    /**
     * Gets 客服专员id
     * @return int
     */
    public function getCsID()
    {
        return $this->__get(self::CS_ID);
    }

    /**
     * Sets 客服专员id
     * @param int $value
     * @return ChatRoomRecord
     */
    public function setCsID($value)
    {
        $this->__set(self::CS_ID, $value);

        return $this;
    }

    /**
     * Gets 
     * @return string
     */
    public function getRecordTime()
    {
        return $this->__get(self::RECORD_TIME);
    }

    /**
     * Sets 
     * @param string $value
     * @return ChatRoomRecord
     */
    public function setRecordTime($value)
    {
        $this->__set(self::RECORD_TIME, $value);

        return $this;
    }

    /**
     * Gets 聊天内容类型
     * @return string
     */
    public function getContentType()
    {
        return $this->__get(self::CONTENT_TYPE);
    }

    /**
     * Gets 聊天内容类型 display name
     * @return string
     */
    public function getContentType_EnumValue()
    {
        $option = $this->metadata()->getFilterOption('content_type');
        return $option['enum']::getDisplayName($this->__get(self::CONTENT_TYPE));
    }

    /**
     * Sets 聊天内容类型
     * @param string $value
     * @return ChatRoomRecord
     */
    public function setContentType($value)
    {
        $this->__set(self::CONTENT_TYPE, $value);

        return $this;
    }
}
?>