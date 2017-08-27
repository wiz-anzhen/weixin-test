<?php

require_once 'WBTSdk/WBTClient.php';
require_once 'WBTSdk/WBTApiException.php';

/**
 * Internal use only.
 */
class WBTPrivateClient extends WBTClient
{
    public function __construct($key, $secret)
    {
        parent::__construct($key, $secret);

        $this->_apiEntry = ENV == 'prod' ? 'http://www.weibotui.com/api/open/' : 'http://127.0.0.1/api/open/';
    }

    public function get_user_token()
    {
        $this->_beAuthorized();
        return $this->_accessToken['user_token'];
    }

    public function get_user_profile_by_ticket($ticket)
    {
        $post = [ 'ticket' => $ticket ];

        return $this->_api('user/get_user_profile', $post);
    }

    public function client_login($userId)
    {
        $post = [ 'user_id' => $userId ];

        return $this->_api('user/client_login', $post);
    }

    public function payment_get_status($type, $bill_id)
    {
        $post = [ 'type' => $type, 'bill_id'  => $bill_id ];

        return $this->_api('payment/get_status', $post);
    }

    protected function _get_client_token()
    {
        $userPass = base64_encode($this->_key . ':' . $this->_secret);

        $header = [
            "Authorization: Basic {$userPass}",
            "Content-Type: application/x-www-form-urlencoded"
        ];

        $result = $this->_send('oauth/client', $header, null, [ 'grant_type' => 'client_credentials', 'scope' => 'sso sna payment' ]);

        return json_decode($result, true);
    }

    protected function _beAuthorized()
    {
        if (empty($this->_accessToken) || time() > $this->_accessToken['expires'])
        {
            $this->accessToken($this->_get_client_token());
            $this->_accessToken['expires'] = $this->_accessToken['expires_in'] + time() - 60;
        }

        if (empty($this->_accessToken) || empty($this->_accessToken['access_token']))
        {
            throw new WBTApiException('Internal Server Error', 500);
        }
    }
}