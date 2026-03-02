<?php

namespace App\Jobs;

use App\Mail\NoteAdded;
use App\Models\Note;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;

    /**
     * Create a new job instance.
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Mail::to('fardin360360@gmail.com')
        //     ->send(new NoteAdded($this->note));
        foreach ($this->request['forms'] as $noteData) {

            $note = Note::create([
                'title' => $noteData['title'],
                'note' => $noteData['note'],
            ]);
        }
        // Log::info('Job request data:', $this->request);
    }
}