<?php
namespace Sys;

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    use Options;

    private PHPMailer $mail;
    private $default_charset = 'utf-8';
    private $is_smtp = true;
    private $is_imap = false;
    private $smtp;
    private $smtp_port;
    private $smtp_auth = true;
    private $smtp_secure = 'tls';
    private $pop3;
    private $pop3_box;
    private $imap;
    private $imapbox;
    private $mailboxes = [];
    private $is_html = true;

    public function __construct($address = null, $name = '')
    {
        $env = env();

        $this->setOptions($env->array('mail'));

        if (!$address) {
            $address = $this->mailboxes[0]['address'];
        }

        if (!$name) {
            $key = array_search($address, array_column($this->mailboxes, 'address'));
            $name = $this->mailboxes[$key]['name'] ?? '';
        }

        $this->mail = new PHPMailer($env->env >= TESTING);
        $this->mail->CharSet = $this->default_charset;

        if($this->is_smtp)
        {
            $this->mail->isSMTP();
            $this->mail->Host = $this->smtp;
            $this->mail->Port = $this->smtp_port;

            if ($this->smtp_auth) {
                $this->mail->SMTPAuth = true;
                $this->mail->Username = $address;
                $this->mail->Password = $this->mailboxes[0]['password'];
                $this->mail->SMTPSecure = $this->smtp_secure;
            }
            
        }

        $this->mail->isHTML($this->is_html);
        $this->mail->setFrom($address, $name);
    }

    public function getStatus($mailbox = null, $password = null)
    {
        if ($this->is_imap === true) {
            if (!$mailbox) {
                $mailbox = $address = $this->mailboxes[0]['address'];
            }

            if (!$password) {
                $key = array_search($mailbox, array_column($this->mailboxes, 'address'));
                $password = $this->mailboxes[$key]['password'] ?? '';
            }

            $imap = imap_open($this->imapbox, $mailbox, $password);
            $status = imap_status($imap, $this->imapbox, SA_ALL);
            imap_close($imap);
        } else {
            $status = (object)['flags' => 0, 'messages' => 0, 'recent' => 0, 'unseen' => 0];
        }

        return $status;
    }

    public function mailer()
    {
        return $this->mail;
    }

    public function isHTML($html = true)
    {
        $this->is_html = $html;
        $this->mail->isHTML($this->is_html);
        return $this;
    }

    public function to($address, $name = '')
    {
        $this->mail->addAddress($address, $name);
        return $this;
    }

    public function replyTo($address, $name = '')
    {
        $this->mail->addReplyTo($address, $name = '');
        return $this;
    }

    public function cc($address, $name = '')
    {
        $this->mail->addCC($address, $name = '');
        return $this;
    }

    public function bcc($address, $name = '')
    {
        $this->mail->addBCC($address, $name = '');
        return $this;
    }

    public function attach($path, $name = '')
    {
        $this->mail->addAttachment($path, $name);
        return $this;
    }

    public function subject($text)
    {
        $this->mail->Subject = $text;
        return $this;
    }

    public function body($body)
    {
        $this->mail->Body = $body;
        return $this;
    }

    public function altBody($text)
    {
        $this->mail->AltBody = $text;
        return $this;
    }

    public function send()
    {
        $this->mail->send();
    }
}
