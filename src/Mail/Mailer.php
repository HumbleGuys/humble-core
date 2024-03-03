<?php

namespace HumbleCore\Mail;

use HumbleCore\Support\Facades\Action;
use Illuminate\Support\Traits\Conditionable;

class Mailer
{
    use Conditionable;

    protected $headers;

    protected $to;

    protected $subject;

    protected $body;

    protected $mailData;

    protected $attachments = [];

    public function __construct()
    {
        $this->setHeaders();
        $this->checkForMailgun();
        $this->checkForMailTrap();
    }

    private function setHeaders(): void
    {
        $settings = config('mail');

        $this->headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$settings['from']['name']} <{$settings['from']['address']}>",
        ];
    }

    private function checkForMailgun(): void
    {
        if (config('mail.mailer') !== 'mailgun') {
            return;
        }

        Action::add('phpmailer_init', function ($phpmailer) {
            $phpmailer->isSMTP();
            $phpmailer->Host = config('mail.mailgun.host');
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = config('mail.mailgun.port');
            $phpmailer->Username = config('mail.mailgun.username');
            $phpmailer->Password = config('mail.mailgun.password');
        });
    }

    private function checkForMailTrap(): void
    {
        if (app()->isProduction()) {
            return;
        }

        if (config('mail.mailer') !== 'mailtrap') {
            return;
        }

        Action::add('phpmailer_init', function ($phpmailer) {
            $phpmailer->isSMTP();
            $phpmailer->Host = config('mail.mailtrap.host');
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = config('mail.mailtrap.port');
            $phpmailer->Username = config('mail.mailtrap.username');
            $phpmailer->Password = config('mail.mailtrap.password');
        });
    }

    public function to(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function subject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function replyTo(string $name, string $email): self
    {
        $this->headers[] = "Reply-To: {$name} <{$email}>";

        return $this;
    }

    public function attach(string $path): self
    {
        $this->attachments[] = $path;

        return $this;
    }

    public function template(string $template, array $mailData): self
    {
        $this->mailData = $mailData;
        $this->body = view($template, $mailData);

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function send(): bool
    {
        return wp_mail($this->to, $this->subject, $this->getBody(), $this->headers, $this->attachments);
    }
}
