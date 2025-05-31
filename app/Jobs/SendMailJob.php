<?php

namespace App\Jobs;

use App\Mail\AdConfimationMail;
use App\Mail\AdRejectionMail;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendMailJob implements ShouldQueue
{
    use Queueable;

    protected User $user;
    protected string $emailType;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $emailType = "email-confirmation")
    {
        $this->user = $user;
        $this->emailType = $emailType;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->emailType == "email-confimation")
            Mail::to($this->user)->send(new AdConfimationMail());
        if ($this->emailType == "email-rejection")
            Mail::to($this->user)->send(new AdRejectionMail());
    }
}
