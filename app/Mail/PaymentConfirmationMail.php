<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $pageUrl,
        public string $coupleName
    ) {}

    public function build()
    {
        return $this->subject('🎉 Payment successful')
                    ->view('emails.payment-confirmation')
                    ->with([
                        'pageUrl' => $this->pageUrl,
                        'coupleName' => $this->coupleName,
                    ]);
    }
}

