<?php
namespace App\System;

use App\System\Email\EmailInvalidAdapterException;

class Email {

    /**
     * @var null|\Swift_Mailer
     */
    private $__mailer = null;

    public function __construct()
    {
        App::get()->getProfiler()->start("App::Email::Init");
        $config = App::get()->getConfig()->getConfigValues("mailer")["mailer"][App::get()->getEnvironment()];
        switch ($config["adapter"]) {
            case "sendmail":
                $transport = new \Swift_SendmailTransport();
                break;
            case "smtp":
                $transport = (new \Swift_SmtpTransport($config["host"],$config["port"],$config["ssl"]?$config["ssl"]:null))
                    ->setUsername($config["username"])
                    ->setPassword($config["password"]);
                break;
            default:
                throw new EmailInvalidAdapterException();
                break;
        }

        $this->__mailer = new \Swift_Mailer($transport);
        App::get()->getProfiler()->stop("App::Email::Init");
    }

    public function getMailer() {
        return $this->__mailer;
    }

    /**
     * @return \Swift_Message
     */
    public function getMessage() {
        return new \Swift_Message();
    }

    /**
     * @param \Swift_Message $message
     * @param string $template
     * @param array $variables
     * @return int
     */
    public function send(\Swift_Message $message, string $template, array $variables = []) {
        try {
            $message->setBody(App::get()->getView()->render("__email".DS.$template, $variables))->setContentType("text/html");
            return $this->getMailer()->send($message);
        } catch (\Exception $e) {
            return false;
        }
    }

}