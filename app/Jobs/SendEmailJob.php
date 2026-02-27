<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\NoteAdded;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $note;

    /**
     * Create a new job instance.
     */
    public function __construct($note)
    {
        $this->note = $note;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to('fardin360360@gmail.com')
            ->send(new NoteAdded($this->note));
    }
}