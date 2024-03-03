<?php

namespace HumbleCore\Mail;

class MailMessage extends Mailer
{
    public $rows = [];

    public function heading($heading): self
    {
        $this->rows[] = [
            'type' => 'heading',
            'value' => $heading,
        ];

        return $this;
    }

    public function text($content): self
    {
        $this->rows[] = [
            'type' => 'content',
            'value' => $content,
        ];

        return $this;
    }

    public function button($label, $url): self
    {
        $this->rows[] = [
            'type' => 'button',
            'label' => $label,
            'url' => $url,
        ];

        return $this;
    }

    public function getBody(): string
    {
        return view('emails.message', ['rows' => $this->rows]);
    }
}
