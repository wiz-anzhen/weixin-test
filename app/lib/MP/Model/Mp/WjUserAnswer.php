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

class WjUserAnswer extends Model
{
    const WJ_USER_ANSWER_ID = 'wj_user_answer_id';
    const _CREATED_AT = '_created_at';
    const MP_USER_ID = 'mp_user_id';
    const COMMUNITY_ID = 'community_id';
    const WX_USER_ID = 'wx_user_id';
    const NAME = 'name';
    const GENDER = 'gender';
    const TEL = 'tel';
    const BIRTH = 'birth';
    const EMAIL = 'email';
    const WJ_QUESTIONNAIRE_ID = 'wj_questionnaire_id';
    const ANSWER = 'answer';



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
                'wj_user_answer',
                'wj_user_answer_id',
                [
                    'wj_user_answer_id' => ['name' => _META_('mp.wj_user_answer.wj_user_answer_id'), 'type' => 'int', 'length' => 10, 'min' => 1, 'required' => true, 'db_insert' => true, 'rou' => true],
                    '_created_at' => ['name' => _META_('mp.wj_user_answer._created_at'), 'type' => 'timestamp', 'required' => true, 'default' => new DbExpr('NOW()'), 'roc' => true, 'rou' => true],
                    'mp_user_id' => ['name' => _META_('mp.wj_user_answer.mp_user_id'), 'type' => 'int', 'length' => 10],
                    'community_id' => ['name' => _META_('mp.wj_user_answer.community_id'), 'type' => 'int', 'length' => 10, 'required' => true],
                    'wx_user_id' => ['name' => _META_('mp.wj_user_answer.wx_user_id'), 'type' => 'text', 'length' => 64],
                    'name' => ['name' => _META_('mp.wj_user_answer.name'), 'type' => 'text', 'length' => 64],
                    'gender' => ['name' => _META_('mp.wj_user_answer.gender'), 'type' => 'text', 'length' => 64],
                    'tel' => ['name' => _META_('mp.wj_user_answer.tel'), 'type' => 'text', 'length' => 64],
                    'birth' => ['name' => _META_('mp.wj_user_answer.birth'), 'type' => 'text', 'length' => 64],
                    'email' => ['name' => _META_('mp.wj_user_answer.email'), 'type' => 'text', 'length' => 64],
                    'wj_questionnaire_id' => ['name' => _META_('mp.wj_user_answer.wj_questionnaire_id'), 'type' => 'int', 'length' => 10],
                    'answer' => ['name' => _META_('mp.wj_user_answer.answer'), 'type' => 'text'],
                ],
                [
                    'auto_increment_id' => 'wj_user_answer_id',
                    'create_timestamp' => '_created_at',
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
    public function getWjUserAnswerID()
    {
        return $this->__get(self::WJ_USER_ANSWER_ID);
    }

    /**
     * Sets id
     * @param int $value
     * @return WjUserAnswer
     */
    public function setWjUserAnswerID($value)
    {
        $this->__set(self::WJ_USER_ANSWER_ID, $value);

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
     * @return WjUserAnswer
     */
    public function setCreatedAt($value)
    {
        $this->__set(self::_CREATED_AT, $value);

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
     * @return WjUserAnswer
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
     * @return WjUserAnswer
     */
    public function setCommunityID($value)
    {
        $this->__set(self::COMMUNITY_ID, $value);

        return $this;
    }

    /**
     * Gets wx_user.wx_user_id
     * @return string
     */
    public function getWxUserID()
    {
        return $this->__get(self::WX_USER_ID);
    }

    /**
     * Sets wx_user.wx_user_id
     * @param string $value
     * @return WjUserAnswer
     */
    public function setWxUserID($value)
    {
        $this->__set(self::WX_USER_ID, $value);

        return $this;
    }

    /**
     * Gets 答题人姓名
     * @return string
     */
    public function getName()
    {
        return $this->__get(self::NAME);
    }

    /**
     * Sets 答题人姓名
     * @param string $value
     * @return WjUserAnswer
     */
    public function setName($value)
    {
        $this->__set(self::NAME, $value);

        return $this;
    }

    /**
     * Gets 性别
     * @return string
     */
    public function getGender()
    {
        return $this->__get(self::GENDER);
    }

    /**
     * Sets 性别
     * @param string $value
     * @return WjUserAnswer
     */
    public function setGender($value)
    {
        $this->__set(self::GENDER, $value);

        return $this;
    }

    /**
     * Gets 手机
     * @return string
     */
    public function getTel()
    {
        return $this->__get(self::TEL);
    }

    /**
     * Sets 手机
     * @param string $value
     * @return WjUserAnswer
     */
    public function setTel($value)
    {
        $this->__set(self::TEL, $value);

        return $this;
    }

    /**
     * Gets 出生日期
     * @return string
     */
    public function getBirth()
    {
        return $this->__get(self::BIRTH);
    }

    /**
     * Sets 出生日期
     * @param string $value
     * @return WjUserAnswer
     */
    public function setBirth($value)
    {
        $this->__set(self::BIRTH, $value);

        return $this;
    }

    /**
     * Gets 电子邮件
     * @return string
     */
    public function getEmail()
    {
        return $this->__get(self::EMAIL);
    }

    /**
     * Sets 电子邮件
     * @param string $value
     * @return WjUserAnswer
     */
    public function setEmail($value)
    {
        $this->__set(self::EMAIL, $value);

        return $this;
    }

    /**
     * Gets wj_questionnaire.wj_questionnaire_id
     * @return int
     */
    public function getWjQuestionnaireID()
    {
        return $this->__get(self::WJ_QUESTIONNAIRE_ID);
    }

    /**
     * Sets wj_questionnaire.wj_questionnaire_id
     * @param int $value
     * @return WjUserAnswer
     */
    public function setWjQuestionnaireID($value)
    {
        $this->__set(self::WJ_QUESTIONNAIRE_ID, $value);

        return $this;
    }

    /**
     * Gets 问答题直接保存，选择题逗号分隔
     * @return string
     */
    public function getAnswer()
    {
        return $this->__get(self::ANSWER);
    }

    /**
     * Sets 问答题直接保存，选择题逗号分隔
     * @param string $value
     * @return WjUserAnswer
     */
    public function setAnswer($value)
    {
        $this->__set(self::ANSWER, $value);

        return $this;
    }
}
?>