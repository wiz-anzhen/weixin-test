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

class OAuthCode extends Model
{
    const _CREATED_AT = '_created_at';
    const CODE = 'code';
    const REDIRECT_URI = 'redirect_uri';
    const EXPIRES = 'expires';
    const SCOPE = 'scope';
    const CLIENT = 'client';
    const USER = 'user';

    const WITH_CLIENT = 'oauth_code.client:oauth_client.oauth_client_id';
    const WITH_USER = 'oauth_code.user:user.user_id';


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
                'oauth_code',
                'code',
                [
                    '_created_at' => ['name' => _META_('weibotui.oauth_code._created_at'), 'type' => 'timestamp', 'required' => true, 'default' => new DbExpr('NOW()'), 'roc' => true, 'rou' => true],
                    'code' => ['name' => _META_('weibotui.oauth_code.code'), 'type' => 'text', 'length' => 40, 'required' => true],
                    'redirect_uri' => ['name' => _META_('weibotui.oauth_code.redirect_uri'), 'type' => 'url', 'required' => true],
                    'expires' => ['name' => _META_('weibotui.oauth_code.expires'), 'type' => 'datetime', 'required' => true],
                    'scope' => ['name' => _META_('weibotui.oauth_code.scope'), 'type' => 'text', 'max' => 1000],
                    'client' => ['name' => _META_('weibotui.oauth_code.client'), 'type' => 'int', 'length' => 10, 'min' => 10000, 'required' => true],
                    'user' => ['name' => _META_('weibotui.oauth_code.user'), 'type' => 'int', 'length' => 10, 'min' => 100000],
                ],
                [
                    'create_timestamp' => '_created_at',
                ],
                [
                    'client' => self::WITH_CLIENT,
                    'user' => self::WITH_USER,
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
     * @return OAuthCode
     */
    public function setCreatedAt($value)
    {
        $this->__set(self::_CREATED_AT, $value);

        return $this;
    }

    /**
     * Gets 授权码
     * @return string
     */
    public function getCode()
    {
        return $this->__get(self::CODE);
    }

    /**
     * Sets 授权码
     * @param string $value
     * @return OAuthCode
     */
    public function setCode($value)
    {
        $this->__set(self::CODE, $value);

        return $this;
    }

    /**
     * Gets 回调地址
     * @return string
     */
    public function getRedirectUri()
    {
        return $this->__get(self::REDIRECT_URI);
    }

    /**
     * Sets 回调地址
     * @param string $value
     * @return OAuthCode
     */
    public function setRedirectUri($value)
    {
        $this->__set(self::REDIRECT_URI, $value);

        return $this;
    }

    /**
     * Gets 有效期
     * @return string
     */
    public function getExpires()
    {
        return $this->__get(self::EXPIRES);
    }

    /**
     * Sets 有效期
     * @param string $value
     * @return OAuthCode
     */
    public function setExpires($value)
    {
        $this->__set(self::EXPIRES, $value);

        return $this;
    }

    /**
     * Gets 授权范围
     * @return string
     */
    public function getScope()
    {
        return $this->__get(self::SCOPE);
    }

    /**
     * Sets 授权范围
     * @param string $value
     * @return OAuthCode
     */
    public function setScope($value)
    {
        $this->__set(self::SCOPE, $value);

        return $this;
    }

    /**
     * Gets oauth客户
     * @return int
     */
    public function getClient()
    {
        return $this->__get(self::CLIENT);
    }

    /**
     * Sets oauth客户
     * @param int $value
     * @return OAuthCode
     */
    public function setClient($value)
    {
        $this->__set(self::CLIENT, $value);

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
     * @return OAuthCode
     */
    public function setUser($value)
    {
        $this->__set(self::USER, $value);

        return $this;
    }

    /**
     * @param bool $new
     * @return \WBT\Model\Weibotui\OAuthClient
     */
    public function getClient_($new = false)
    {
        if ($new)
        {
            return new \WBT\Model\Weibotui\OAuthClient();
        }

        if (isset($this->_links['client']))
        {
            return $this->_links['client'];
        }

        return ($this->_links['client'] = new \WBT\Model\Weibotui\OAuthClient($this->getClient()));
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
}
?>