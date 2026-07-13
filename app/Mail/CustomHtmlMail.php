<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomHtmlMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $mailSubject,
        public readonly string $htmlContent,
    ) {
    }

    public function build(): static
    {
        return $this
            ->subject($this->mailSubject)
            ->html($this->htmlContent);
    }
}
