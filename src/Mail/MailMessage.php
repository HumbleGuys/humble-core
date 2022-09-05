<?php

namespace HumbleCore\Mail;

class MailMessage extends Mailer
{
    public $rows = [];

    public $attachments = [];

    public function greeting($greeting)
    {
        $this->rows[] = [
            'type' => 'greeting',
            'value' => $greeting,
        ];

        return $this;
    }

    public function line($line)
    {
        $this->rows[] = [
            'type' => 'line',
            'value' => $line,
        ];

        return $this;
    }

    public function lines($lines)
    {
        $this->rows[] = [
            'type' => 'lines',
            'lines' => $lines,
        ];

        return $this;
    }

    public function panel($panel)
    {
        $this->rows[] = [
            'type' => 'panel',
            'value' => $panel,
        ];

        return $this;
    }

    public function button($button)
    {
        $this->rows[] = [
            'type' => 'button',
            'value' => $button,
        ];

        return $this;
    }

    public function getBody(): string
    {
        return view('emails.message', ['rows' => $this->rows]);
    }
}
