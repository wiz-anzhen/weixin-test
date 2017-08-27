<?php

require_once 'MpUserServiceBase.php';

use MP\Model\Mp\CustomerSpecialistGroup;

use WBT\Business\Weixin\CustomerSpecialistGroupBusiness;

class CustomerSpecialistGroupService extends MpUserServiceBase
{
    public function update()
    {
        $id   = $this->_app->request()->getQueryParam( CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID );
        $data = $this->_app->request()->getArray( [ CustomerSpecialistGroup::GROUP_NAME, CustomerSpecialistGroup::COMMENT,] );
        $dataWork = $this->_app->request()->getArray( [ "work_time_1","work_time_2", ] );
        $data[CustomerSpecialistGroup::WORK_TIME] = $dataWork["work_time_1"]."-".$dataWork["work_time_2"];
        return CustomerSpecialistGroupBusiness::update( $id, $data );
    }

    public function insert()
    {
        $request = $this->_app->request();
        $data    = $request->getArray( [ CustomerSpecialistGroup::MP_USER_ID, CustomerSpecialistGroup::GROUP_NAME, CustomerSpecialistGroup::COMMENT,CustomerSpecialistGroup::COMMUNITY_ID] );
        $dataWork = $this->_app->request()->getArray( [ "work_time_1","work_time_2",] );
        $data[CustomerSpecialistGroup::WORK_TIME] = $dataWork["work_time_1"]."-".$dataWork["work_time_2"];
        return CustomerSpecialistGroupBusiness::insert( $data );
    }

    public function delete()
    {
        $id = $this->_app->request()->get( CustomerSpecialistGroup::CUSTOMER_SPECIALIST_GROUP_ID );

        return CustomerSpecialistGroupBusiness::delete( $id );
    }

}