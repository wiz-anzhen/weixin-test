<?php

namespace WBT\Business;
use WBT\Business\ConfigBusiness;

require_once 'PHPMailer/class.phpmailer.php';


/**
 * 发送email相关的业务
 */
class MailBusiness
{
    public static function sendMail($to, $subject, $htmlContent, $attachment = null)
    {
        $mail = new \PHPMailer();

        $body = $htmlContent;

        $from='no-reply@' . ConfigBusiness::getMailHost();

        $mail->CharSet = 'UTF-8';
        $mail->Hostname = ConfigBusiness::getMailHost();
        $mail->SetFrom($from, $from);
        $mail->AddReplyTo($from, $from);
        $mail->AddAddress($to, $to);
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        if (!is_null($attachment) && file_exists($attachment))
        {
            $mail->AddAttachment($attachment);
        }

        log_info("SEND_MAIL [to:$to][subject:$subject]");
        if (!$mail->Send())
        {
            log_error("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function sendMailToMultiRecipients( $recipients, $subject, $htmlContent, $attachment = null )
    {
        if (!is_array( $recipients )) $recipients = [ $recipients ];

        $failedCount = 0;
        foreach ($recipients as $recipient)
        {
            if (!MailBusiness::sendMail( $recipient, $subject, $htmlContent, $attachment ))
            {
                log_debug($recipient . "\n" . $subject . "\n" . $htmlContent);
                $failedCount++;
            }
        }

        return $failedCount;
    }
    // 异步发送邮件
    public static function sendMailAsyn($to,$cc, $subject, $htmlContent, $attachment = null)
    {
        if(empty($to) && empty($cc))
        {
            log_warn("empty to and cc");
            return;
        }

        $from='no-reply@' . ConfigBusiness::getMailHost();


        $host =  ConfigBusiness::getHost();

        if (is_array($to))
        {
            $to = implode(',', $to);
        }
        if(is_array($cc))
        {
            $cc = implode(",",$cc);
        }
        $postUrl = $host. '/raw/send_mail_asyn.php?=ffadksfads2kfal45dwiewf5kad78v23xk';
        _curl_post($postUrl,
            [ 'token' => 'ffadksfads2kfal45dwiewf5kad78v23xk',
              'to'    => $to,
              'from'  => $from,
              'mail_host' => ConfigBusiness::getMailHost(),
              'title' => $subject,
              'cc' => $cc,
               'attachment' => $attachment,
              'html'  => $htmlContent ]);

        log_info("SEND_MAIL [to:$to][cc:$cc][subject:$subject]");

    }

    static function sendMailWithReply($to,$reply,$subject, $htmlContent)
    {
        $mail             = new \PHPMailer();

        $from='no-reply@' . ConfigBusiness::getMailHost();


        //$body             = preg_replace('/[\]/','',$htmlContent);
        $body = $htmlContent;

        $mail->CharSet = 'UTF-8';
        $mail->Hostname = ConfigBusiness::getMailHost();
        $mail->SetFrom($from, $from);
        $mail->AddReplyTo($reply, $reply);
        $mail->AddAddress($to, $to);
        $mail->AddAddress($reply,$reply); // 同时给回复地址发送邮件, 供微邮箱使用
        $mail->Subject    = $subject;
        $mail->MsgHTML($body);

        if(! $mail->Send())
        {
            log_error("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
        else
        {
            return true;
        }
    }
}
