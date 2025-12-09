<?php

namespace App\Mail;

use App\Models\Subscription;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subscription;
    public $invoice;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription, Invoice $invoice = null)
    {
        $this->subscription = $subscription;
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Subscription Confirmation - StudentMove')
                    ->view('emails.subscription-confirmation')
                    ->with([
                        'subscription' => $this->subscription,
                        'invoice' => $this->invoice,
                    ]);
    }
}

