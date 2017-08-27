<?php
require_once '../../lib/Bluefin/bluefin.php';

use Bluefin\App;

test();

function test()
{
    testA();
}

function testA()
{
    echo "a\n";
    log_error_with_call_stack("test log");
}

//\WBT\Business\SmsBusiness::send('13699255409', 'test from cgb');