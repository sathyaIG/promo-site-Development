<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\RegisterMail;
use Mail;

class RegistermailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $details;

    /**
     * Create a new job instance.
     */
    public function __construct($details)
    {
        $this->details = $details;
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->details['emails'] as $emailAddress) {
            $email = new RegisterMail(array_merge($this->details, ['email' => $emailAddress]));
            Mail::to($emailAddress)->send($email);
        }
        // $email = new RegisterMail($this->details);
        // Mail::to($this->details['email'])->send($email);
        
        //
    }
}
