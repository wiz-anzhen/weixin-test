<?php

require_once   realpath(__DIR__) . '/../lib/Bluefin/bluefin.php';

use Bluefin\App;


try
{
    $sqlFile = ROOT . '/lance/data/test_spm.sql';
    _SQL('mp',$sqlFile);

    echo "init test data ok.\n";
}
catch (\Exception $e)
{
    echo "exception: ". $e->getMessage();
}


