<?php

namespace Common\Helper;

use Bluefin\App;
use Bluefin\Controller;
use Bluefin\Convention;

use Common\Data\Event;
use MP\Model\Mp\WxUser;

class BaseController extends Controller
{
    protected $_isSigned;
    protected $_urlSignature;

    protected function _init()
    {
        $this->_validateUrlSignature();
        $staticFileVersionConfig = _C('config.static_file_version');

        $this->_view->set('third_lib_version', $staticFileVersionConfig['third_lib']);
        $this->_view->set('our_lib_version', $staticFileVersionConfig['our_lib']);
    }

    protected function getSimpleUsername($username)
    {
        $pos = strpos($username,'@');
        if($pos)
        {
            return substr($username,0,$pos);
        }

        return $username;
    }

    public function preDispatch()
    {
        parent::preDispatch();

        $this->_view->set('title', _APP_(implode('.', $this->_gateway->getLocationStack())));

        $event = $this->_request->getQueryParam('_event', null, true);

        if (isset($event)) {
            $params = $this->_request->getQueryParam('_param', null, true);
            if (isset($params)) {
                $params = json_decode(base64_decode($params), true);
            }
            $eventMessage = Event::getMessage($event, $params);

            $this->_view->set('_eventMessage', $eventMessage);
            $this->_view->set('_eventAlertClass', Event::getLevelAlertClass($event));
        }

        if (isset($this->_requestSource) && substr($this->_requestSource, 0, 3) == 'B64') {
            $original = base64_decode(substr($this->_requestSource, 3), true);
            if (false === $original) {
                throw new \Bluefin\Exception\InvalidRequestException();
            } else {
                $this->_app->log()->debug("Source url: " . $original, \Bluefin\Log::CHANNEL_DIAG);
                $this->_requestSource = $original;
            }
        }
    }

    public function postDispatch()
    {
        parent::postDispatch();

        if (isset($this->_requestSource)) {
            $this->_view->set('from', $this->_requestSource);
        }
    }

    protected function _showEventMessage($code, $source = Event::SRC_COMMON, $level = Event::LEVEL_ERROR, array $params = null)
    {
        $eventCode = Event::make($level, $source, $code);

        $this->changeView('WBT/Error.message.html');

        isset($title) || ($title = _DICT_('error'));

        $this->_view->set('title', $title);
        $this->_view->set('message', Event::getMessage($eventCode, $params));

        throw new \Bluefin\Exception\SkipException();
    }

    protected function _redirectWithEvent($event, array $eventParams = null, $toUrl = null, array $otherParams = null)
    {
        isset($toUrl) || ($toUrl = $this->_requestSource);

        if (!isset($toUrl)) {
            throw new \Bluefin\Exception\InvalidOperationException('Nowhere to redirect!');
        }

        isset($otherParams) || ($otherParams = []);

        $otherParams[Convention::KEYWORD_REQUEST_EVENT] = $event;

        if (isset($eventParams)) {
            $otherParams[Convention::KEYWORD_REQUEST_PARAMS] = base64_encode(json_encode_cn($eventParams));
        }

        if (is_abs_url($toUrl))
        {
            $toUrl = build_uri($toUrl, $otherParams, null);
        }
        else
        {
            $toUrl = $this->_gateway->url($toUrl, $otherParams, null);
        }

        $this->_gateway->redirect($toUrl);
    }

    protected function _checkRequiredInput($name, $value)
    {
        if (!isset($value) || $value == '')
        {
            $this->_setEventMessage(Event::E_MISSING_ARGUMENT, Event::SRC_COMMON, Event::LEVEL_ERROR, ['%name%' => $name]);
            return false;
        }

        return true;
    }

    protected function _setEventMessage($code, $source = Event::SRC_COMMON, $level = Event::LEVEL_ERROR, array $params = null)
    {
        $eventCode = Event::make($level, $source, $code);
        $this->_view->set('_eventMessage', Event::getMessage($eventCode, $params));
        $this->_view->set('_eventAlertClass', Event::getLevelAlertClass($eventCode));
    }

    protected function _validateUrlSignature()
    {
        $asig = $this->_request->getQueryParam('_asig', null, true);
        if (isset($asig)) {
            if (!check_url_sig($this->_request->getFullRequestUri(), '_asig', $asig, $this->_urlSignature)) {
                throw new \Bluefin\Exception\InvalidRequestException();
            }

            $uri = $this->_request->getRequestUri();
            $uri = build_uri($uri, ['_asig' => null]);
            $this->_request->rebuildRequestUri($uri);
            $this->_isSigned = true;
        } else {
            $rsig = $this->_request->getQueryParam('_rsig', null, true);
            if (isset($rsig)) {
                if (!check_url_sig($this->_request->getRequestUri(), '_rsig', $rsig, $this->_urlSignature)) {
                    throw new \Bluefin\Exception\InvalidRequestException();
                }

                $uri = $this->_request->getRequestUri();
                $uri = build_uri($uri, ['_rsig' => null]);
                $this->_request->rebuildRequestUri($uri);
                $this->_isSigned = true;
            } else {
                $this->_isSigned = false;
            }
        }
    }

    protected function _signUrl($url, array $queryParams = null, $withSourceUrl = false)
    {
        if ($withSourceUrl && isset($this->_requestSource)) {
            $queryParams[Convention::KEYWORD_REQUEST_FROM] = $this->_requestSource;
        }

        if (!is_abs_url($url)) {
            $url = $this->_gateway->path($url);
        }

        return build_uri($url, $queryParams, null, $this->_urlSignature);
    }

    protected function _requireSignedRequest()
    {
        if (!$this->_isSigned) {
            throw new \Bluefin\Exception\RequestException(null, \Bluefin\Common::HTTP_FORBIDDEN);
        }
    }


    // 可以在构造函数中调用
    protected function _redirectToErrorPage( $message) {
        log_debug("$message");
        $uri = sprintf('/error/index/?message=%s', utf8_encode($message));
        $this->_gateway->redirect($uri);
    }

}
