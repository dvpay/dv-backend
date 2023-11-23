<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\WebhookJob;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Throwable;

class InvoiceWebhookSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "invoice:webhook:send {invoiceId}, {checkHandledWebhook=false} {sendSuccessWebhook=true}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend webhook.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Throwable
     */
    public function handle(): void
    {
        $time = time();

        $invoice = Invoice::findOrFail($this->argument('invoiceId'));
        $checkHandledWebhook = (bool)$this->argument('checkHandledWebhook');
        $sendSuccessWebhook = (bool)$this->argument('sendSuccessWebhook');

        WebhookJob::dispatchSync($invoice, $checkHandledWebhook, $sendSuccessWebhook);

        $this->info('The command was successful! ' . time() - $time . ' s.');
    }
}