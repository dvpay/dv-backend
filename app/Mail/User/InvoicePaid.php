<?php

namespace App\Mail\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * InvoicePaid
 */
class InvoicePaid extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(
        public array $invoice,
        public array $transactions
    )
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.invoice.invoice')
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->with([
                'transactions' => $this->transactions,
                'invoice' => $this->invoice,
            ]);
    }
}
