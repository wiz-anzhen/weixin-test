<?php

require_once 'MpUserServiceBase.php';

use MP\Model\Mp\TopDirectory;
use MP\Model\Mp\Directory;
use WBT\Business\Weixin\DirectoryBusiness;
use WBT\Business\ConfigBusiness;
use WBT\Business\Weixin\HouseMemberBusiness;

class DirectoryService extends MpUserServiceBase
{
    public function updateTop()
    {
        $id = $this->_app->request()->getQueryParam( TopDirectory::TOP_DIRECTORY_ID );
        $communityId = $this->_app->request()->getQueryParam( TopDirectory::COMMUNITY_ID );
        $data        = $this->_app->request()->getArray( [ TopDirectory::TITLE,TopDirectory::POWER_TYPE, TopDirectory::TOP_DIR_NO ,TopDirectory::URL_TYPE,TopDirectory::DIRECTORY_BACKGROUND_IMG,TopDirectory::DIRECTORY_TOP_IMG,TopDirectory::DIRECTORY_TOP_IMG_SECOND,TopDirectory::DIRECTORY_TOP_IMG_THIRD] );

        return DirectoryBusiness::updateTop( $id,$communityId,$data );
    }

    public function insertTop()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ TopDirectory::TOP_DIR_NO,TopDirectory::POWER_TYPE,TopDirectory::TITLE,TopDirectory::COMMUNITY_ID,TopDirectory::MP_USER_ID ,TopDirectory::URL_TYPE,TopDirectory::DIRECTORY_BACKGROUND_IMG,TopDirectory::DIRECTORY_TOP_IMG,TopDirectory::DIRECTORY_TOP_IMG_SECOND,TopDirectory::DIRECTORY_TOP_IMG_THIRD] );

        return DirectoryBusiness::insertTop( $data );
    }

    public function copyTop()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ TopDirectory::COMMUNITY_ID,TopDirectory::MP_USER_ID ,TopDirectory::TOP_DIRECTORY_ID] );

        return DirectoryBusiness::copyTop( $data );
    }

    public function removeTop()
    {
        $id = $this->_app->request()->get( TopDirectory::TOP_DIRECTORY_ID );
        $communityId = $this->_app->request()->getQueryParam( TopDirectory::COMMUNITY_ID );

        return DirectoryBusiness::deleteTop( $id,$communityId );
    }

    public function insert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray(
            [   Directory::MP_USER_ID,
                Directory::POWER_TYPE,
                Directory::TOP_DIRECTORY_ID,
                Directory::COMMUNITY_ID,
                Directory::ICON ,
                Directory::TITLE,
                Directory::SORT_NO,
                Directory::COMMON_TYPE ,
                Directory::COMMON_CONTENT,
                Directory::HEAD_DESC,
                Directory::TAIL_DESC,
                Directory::GROUP_END,
                Directory::SHOW_SMALL_FLOW ,
                Directory::SMALL_FLOW_TYPE,
                Directory::SMALL_FLOW_CONTENT,
             ] );
        return DirectoryBusiness::insert( $data );
    }


    public function update()
    {
        $directoryId = $this->_app->request()->getQueryParam( Directory::DIRECTORY_ID );
        $communityId = $this->_app->request()->getQueryParam( Directory::COMMUNITY_ID );
        $data        = $this->_app->request()->getArray(
            [   Directory::ICON ,
                Directory::POWER_TYPE,
                Directory::TITLE,
                Directory::SORT_NO,
                Directory::COMMON_TYPE ,
                Directory::COMMON_CONTENT,
                Directory::HEAD_DESC,
                str_replace("\n", '<br/>', Directory::TAIL_DESC),
                Directory::GROUP_END,
                Directory::SHOW_SMALL_FLOW ,
                Directory::SMALL_FLOW_TYPE,
                Directory::SMALL_FLOW_CONTENT,
             ] );
        log_debug('data==================',$data);
        return DirectoryBusiness::update( $directoryId,$communityId,$data );
    }

    public function smallFlowSet()
    {
        $directoryId = $this->_app->request()->getQueryParam( Directory::DIRECTORY_ID );
        $communityId = $this->_app->request()->getQueryParam( Directory::COMMUNITY_ID );
        $data        = $this->_app->request()->get( Directory::SMALL_FLOW_NO );

        return DirectoryBusiness::smallFlowSet( $directoryId,$communityId,$data );

    }

    public function remove()
    {
        $directoryId = $this->_app->request()->get( Directory::DIRECTORY_ID );
        $communityId = $this->_app->request()->getQueryParam( TopDirectory::COMMUNITY_ID );

        return DirectoryBusiness::delete( $directoryId,$communityId);
    }
}