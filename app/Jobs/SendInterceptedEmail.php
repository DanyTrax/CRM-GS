<?php

namespace App\Jobs;

use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInterceptedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $emailLog;

    /**
     * Create a new job instance.
     */
    public function __construct(EmailLog $emailLog)
    {
        $this->emailLog = $emailLog;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $to = explode(',', $this->emailLog->to);
            $cc = $this->emailLog->cc ? explode(',', $this->emailLog->cc) : [];
            $bcc = $this->emailLog->bcc ? explode(',', $this->emailLog->bcc) : [];

            Mail::send([], [], function ($message) use ($to, $cc, $bcc) {
                $message->to($to)
                    ->subject($this->emailLog->subject)
                    ->html($this->emailLog->body);

                if (!empty($cc)) {
                    $message->cc($cc);
                }

                if (!empty($bcc)) {
                    $message->bcc($bcc);
                }
            });

            $this->emailLog->markAsSent();
        } catch (\Exception $e) {
            $this->emailLog->markAsFailed($e->getMessage());
            throw $e;
        }
    }
}
