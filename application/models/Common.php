<?php

class Application_Model_Common
{

    // send email
    public static function sendEmail($to, $subject, $bodyText, $cc=null, $bcc=null,
                                     $viewFileName=null, $options=null, $tags=null, $filename=null, $filedata=null, $script_path=null)
    {
        try {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/application.ini', 'production');
            $from = $config->resources->mail->defaultFrom->email;
            $fromName = $config->resources->mail->defaultFrom->name;

            $emails = array();
            $validator = new Zend_Validate_EmailAddress();
            // from
            if (!$validator->isValid($from))
                throw new Exception("wrong originator address");
            // to
            foreach ((array)$to as $email) {
                if ($validator->isValid($email))
                    $emails[] = $email;
            }
            if (!count($emails))
                throw new Exception("no correct destination address");
            // subject
            if (!$subject)
                throw new Exception("no subject");
            // init
            $mail = new Zend_Mail('utf-8');
            $mail->setFrom($from, $fromName)
                ->addTo($emails)
                ->setSubject($subject);
            // body
            if ($bodyText) {
                $mail->setBodyText($bodyText);
            } elseif($viewFileName) {
                $html = new Zend_View();
                if (!$script_path) {
                    $html->setScriptPath(APPLICATION_PATH . '/email_templates/');
                } else {
                    $html->setScriptPath(APPLICATION_PATH . $script_path);
                }
                if ($options)
                    $html->assign('options', $options);
                $bodyText = $html->render($viewFileName);
                $mail->setBodyHtml($bodyText);
            } else
                throw new Exception("no body text or html");
            // cc and bcc
            if ($validator->isValid($cc))
                $mail->addCc($cc);
            if ($validator->isValid($bcc))
                $mail->addBcc($bcc);
            // attachments
            if ($filename && $filedata) {
                $at = $mail->createAttachment($filedata);
                $at->type        = 'application/octet-stream';
                $at->disposition = Zend_Mime::DISPOSITION_INLINE;
                $at->encoding    = Zend_Mime::ENCODING_BASE64;
                $at->filename    = $filename;
            }

            if ($tags != null) {
                $mail->addHeader('X-MC-Tags', $tags);
            }

            //send e-mail
            $mail->send();

            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

}

