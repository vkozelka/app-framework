<?php
namespace App\System;

use App\System\Email\Exception\EmailInvalidAdapterException;
use Exception;
use Swift_Mailer;
use Swift_Message;
use Swift_SendmailTransport;
use Swift_SmtpTransport;

class Email {

    private ?Swift_Mailer $mailer = null;

    /**
     * Email constructor.
     * @throws Config\Exception\ConfigFileNotFoundException
     * @throws EmailInvalidAdapterException
     */
    public function __construct()
    {
        App::get()->profiler->start("App::Email::Init");
        $config = App::get()->config->getConfigValues("mailer")["mailer"][App::di()->environment];
        switch ($config["adapter"]) {
            case "sendmail":
                $transport = new Swift_SendmailTransport();
                break;
            case "smtp":
                $transport = (new Swift_SmtpTransport($config["host"],$config["port"],$config["ssl"]?$config["ssl"]:null))
                    ->setUsername($config["username"])
                    ->setPassword($config["password"]);
                break;
            default:
                throw new EmailInvalidAdapterException();
        }

        $this->mailer = new Swift_Mailer($transport);
        App::get()->profiler->stop("App::Email::Init");
    }

    public function getMailer(): ?Swift_Mailer {
        return $this->mailer;
    }

    /**
     * @return Swift_Message
     */
    public function getMessage(): Swift_Message {
        return new Swift_Message();
    }

    /**
     * @param Swift_Message $message
     * @param string $template
     * @param array $variables
     * @return int
     */
    public function send(Swift_Message $message, string $template, array $variables = []) {
        try {
            $message->setBody(App::get()->view->render("__email".DS.$template, $variables))->setContentType("text/html");
            return $this->getMailer()->send($message);
        } catch (Exception $e) {
            return false;
        }
    }

}