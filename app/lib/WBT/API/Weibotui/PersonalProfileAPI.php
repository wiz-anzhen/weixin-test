<?php
//Don't edit this file which is generated by Bluefin Lance.
namespace WBT\API\Weibotui;

use Bluefin\App;
use Bluefin\Common;
use Bluefin\Service;
use Bluefin\Data\Model;
use Bluefin\Data\Database;

use WBT\Model\Weibotui\PersonalProfile;

class PersonalProfileAPI extends Service
{
    public function update($personalProfileID)
    {
        $personalProfile = new PersonalProfile($personalProfileID);
        _NON_EMPTY($personalProfile);

        $aclStatus = PersonalProfile::checkActionPermission(Model::OP_UPDATE, $personalProfile->data());
        if ($aclStatus !== Model::ACL_ACCEPTED)
        {
            throw new \Bluefin\Exception\RequestException(null, $aclStatus);
        }

        return $personalProfile->apply($this->_app->request()->getPostParams())->update();
    }

}
?>