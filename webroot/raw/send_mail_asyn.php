<?php
require_once realpath(__DIR__) . '/../../lib/PHPMailer/class.phpmailer.php';


process();


function send_mail($to, $cc, $subject, $htmlContent,$attachment, $from, $mailHost)
{
    $mail = new \PHPMailer();

    $body = $htmlContent;

    $mail->CharSet  = 'UTF-8';
    $mail->Hostname = $mailHost;
    $mail->SetFrom($from, $from);
    $mail->AddReplyTo($from, $from);

    $recipients   = explode(',', $to);
    $recipientsCc = explode(',', $cc);
    if (count($recipients) > 0)
    {
        foreach ($recipients as $recipient)
        {
            $recipient = trim($recipient);
            if(!empty($recipient))
            {
                $mail->AddAddress($recipient, $recipient);
            }
        }
    }

    if (count($recipientsCc) > 0)
    {
        foreach ($recipientsCc as $recipient)
        {
            $recipient = trim($recipient);
            if(!empty($recipient))
            {
                $mail->AddCC($recipient, $recipient);
            }
        }
    }
    if (!is_null($attachment) && file_exists($attachment))
    {
        $mail->AddAttachment($attachment);
    }
    $mail->Subject = $subject;
    $mail->MsgHTML($body);

    if (!$mail->Send())
    {
        return false;
    }
    else
    {
        return true;
    }
}

function process()
{
    $token = $_POST['token'];
    if ($token != 'ffadksfads2kfal45dwiewf5kad78v23xk')
    {
        exit;
    }

    $to   = $_POST['to'];

    $from = $_POST['from'];
    $maiHost = $_POST['mail_host'];

    $title = $_POST['title'];
    $html  = $_POST['html'];
    $cc    = $_POST['cc'];
    $attachment = $_POST['attachment'];

    fastcgi_finish_request();

    send_mail($to, $cc, $title, $html, $attachment,$from, $maiHost);
}
