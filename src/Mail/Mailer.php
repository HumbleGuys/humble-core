<?php

namespace HumbleCore\Mail;

class Mailer
{
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
        if (config('mail.mailer') !== 'mailgun' || empty(config('mail.mailgun'))) {
            return;
        }

        add_action('phpmailer_init', function ($phpmailer) {
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
        if (config('mail.mailer') !== 'mailtrap' || ! env('MAILTRAP_USERNAME') || ! env('MAILTRAP_PASSWORD')) {
            return;
        }

        add_action('phpmailer_init', function ($phpmailer) {
            $phpmailer->isSMTP();
            $phpmailer->Host = 'smtp.mailtrap.io';
            $phpmailer->SMTPAuth = true;
            $phpmailer->Port = 2525;
            $phpmailer->Username = env('MAILTRAP_USERNAME');
            $phpmailer->Password = env('MAILTRAP_PASSWORD');
        });
    }

    public function to(string $to): Mailer
    {
        $this->to = $to;

        return $this;
    }

    public function subject(string $subject): Mailer
    {
        $this->subject = $subject;

        return $this;
    }

    public function replyTo(string $name, string $email): Mailer
    {
        $this->headers[] = "Reply-To: {$name} <{$email}>";

        return $this;
    }

    public function template(string $template, array $mailData): Mailer
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
