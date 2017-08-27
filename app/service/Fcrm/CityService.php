<?php

use Bluefin\Service;
use Bluefin\App;
use MP\Model\Mp\BillDay;
use MP\Model\Mp\BillDetail;
use MP\Model\Mp\Bill;

use MP\Model\Mp\Directory;
use MP\Model\Mp\HouseMember;
use WBT\Business\Weixin\BillBusiness;
use MP\Model\Mp\City;
use MP\Model\Mp\Area;

require_once 'MpUserServiceBase.php';

class CityService extends MpUserServiceBase{
    //获取城市
    public function getCity()
    {
        $parentId = $this->_app->request()->get('parentId');
        $city = City::fetchRows(['*'],[City::PROVINCE_ID=>$parentId]);
        return ($city);
    }
    public function getArea()
    {
        $parentId = $this->_app->request()->get('parentId');
        $area = Area::fetchRows(['*'],[Area::CITY_ID=>$parentId]);
        return ($area);
    }

}

