<?php

namespace HumbleCore\Mail;

class MailMessage extends Mailer
{
    public $rows = [];

    public function heading($heading): static
    {
        $this->rows[] = [
            'type' => 'heading',
            'value' => $heading,
        ];

        return $this;
    }

    public function text($content): static
    {
        $this->rows[] = [
            'type' => 'content',
            'value' => $content,
        ];

        return $this;
    }

    public function button($label, $url): static
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
