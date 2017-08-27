<?php

require_once 'MpUserServiceBase.php';

use MP\Model\Mp\CommunityPhoneBook;

use WBT\Business\Weixin\CommunityPhoneBookBusiness;

class CommunityPhoneBookService extends MpUserServiceBase
{
    public function update()
    {
        $id   = $this->_app->request()->getQueryParam( CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID );
        $data = $this->_app->request()->getArray( [ CommunityPhoneBook::NAME, CommunityPhoneBook::PHONE, ] );

        return CommunityPhoneBookBusiness::update( $id, $data );
    }

    public function insert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ CommunityPhoneBook::MP_USER_ID, CommunityPhoneBook::NAME, CommunityPhoneBook::PHONE, CommunityPhoneBook::COMMUNITY_ID] );

        return CommunityPhoneBookBusiness::insert( $data );
    }

    public function delete()
    {
        $id = $this->_app->request()->get( CommunityPhoneBook::COMMUNITY_PHONE_BOOK_ID );

        return CommunityPhoneBookBusiness::delete( $id );
    }

}