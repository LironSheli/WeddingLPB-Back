<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PreviewReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $previewUrl,
        public string $coupleName
    ) {}

    public function build()
    {
        return $this->subject('🎉 Your preview is ready')
                    ->view('emails.preview-ready')
                    ->with([
                        'previewUrl' => $this->previewUrl,
                        'coupleName' => $this->coupleName,
                    ]);
    }
}

