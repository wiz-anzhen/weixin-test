<?php

require_once   realpath(__DIR__) . '/../lib/Bluefin/bluefin.php';

use Bluefin\App;

use MP\Model\Mp\MpUser;
use MP\Model\Mp\UrgentNoticeReadRecord;

function test_batch_delete()
{
    $mpUser = new MpUser();
    $affected = $mpUser->delete([MpUser::VALID => 1]);
    echo "affected = $affected\n";

}

function test_insert_on_duplicate()
{
    /*$mpUser = new MpUser();
    $mpUser->setMpUserID(1)
        ->setMpName('name')
        ->setApiID('12314')
        ->setToken('token')
        ->insert();
*/

    $u = new  UrgentNoticeReadRecord();
    $u->setWxUserID('wwww')->setChannelArticleID(1234)->insert(true);


}

test_insert_on_duplicate();

//test_batch_delete();