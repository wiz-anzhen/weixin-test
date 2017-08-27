<?php
require_once   realpath(__DIR__) . '/../lib/Bluefin/bluefin.php';

use Bluefin\App;

use MP\Model\Mp\MpUser;

$con = new \Bluefin\Data\DbCondition("valid = 1 or valid=0");

$condition = [MpUser::MP_USER_ID => 21817, $con];

$row = MpUser::fetchRows('*', $condition);

log_debug("row",$row);

