<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailLog;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected EmailLog $emailLog
    ) {}

    public function handle(): void
    {
        try {
            Mail::raw($this->emailLog->body, function ($message) {
                $message->to($this->emailLog->to_email)
                    ->subject($this->emailLog->subject);
            });

            $this->emailLog->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->emailLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
