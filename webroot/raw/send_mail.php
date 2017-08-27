<?php
require_once '../../lib/Bluefin/bluefin.php';

use Bluefin\App;
use WBT\Business\Weixin\ReportBusiness;

process();

function process()
{
    log_debug('post data received');
    $token = $_GET['token'];
    if ($token != 'ffadksfads2kfal45dwiewf5kad78v23xk')
    {
        log_debug('token error');
        exit;
    }

    $recipients = explode(',', $_POST['recipients']);
    $title = $_POST['title'];
    $html = $_POST['html'];

    fastcgi_finish_request();

    ReportBusiness::sendMailToMultiRecipients($recipients, $title, $html);
}
