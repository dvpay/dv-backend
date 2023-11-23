<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\InvoiceStatus;
use App\Jobs\WebhookJob;
use App\Models\Invoice;
use Illuminate\Console\Command;

class InvoiceSendMissedWebhooks extends Command
{
    protected $signature = 'invoice:webhook:missed';

    public function handle()
    {
        $sql = <<<SQL
            select i.id from invoices i
                inner join transactions t on i.id = t.invoice_id
            left join webhook_send_histories wsh on i.id = wsh.invoice_id
                     where i.created_at > '2022-11-01 00:00:00' 
                        and wsh.id IS NULL
        SQL;

        $missed = \DB::select($sql);
        if (count($missed) === 0) {
            $this->info('not found missed invoices, stopping');
            return;
        }

        $this->info('found ' . count($missed) . ' missed invoices with transactions');


        $invoices = Invoice::query()->with('transactions')->whereIn('id', array_column($missed, 'id'))->get();

        foreach ($invoices as $invoice) {
            if ($invoice->status === InvoiceStatus::Expired) {
                $totalReceived = 0;
                foreach ($invoice->transactions as $tx) {
                    $totalReceived += $tx->amount_usd;
                }

                if ($totalReceived < $invoice->amount) {
                    $invoice->updateStatus(InvoiceStatus::PartiallyPaid);
                } else {
                    $status = InvoiceStatus::Paid;
                }

                $invoice->updateStatus($status);
                $info = sprintf(
                    'found invoice %s with status %s, new status %s, webhook should send',
                    $invoice->id,
                    InvoiceStatus::Expired->value, $status->value
                );
                $this->info($info);
            } else {
                WebhookJob::dispatchSync($invoice);
                $this->info('webhook for invoice ' . $invoice->id . ' is sent');
            }
        }

        $this->info('done');
    }
}
