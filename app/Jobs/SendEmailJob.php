<?php

namespace App\Jobs;

use App\Mail\NoteAdded;
use App\Models\Note;
use App\Models\User;
use App\Notifications\NewNote;
use App\Notifications\PushNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;


class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $user;

    /**
     * Create a new job instance.
     */
    public function __construct($request, User $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Mail::to('fardin360360@gmail.com')
        //     ->send(new NoteAdded($this->note));
        if (empty($this->request['forms'])) {
            return; // nothing to process
        }


        foreach ($this->request['forms'] as $noteData) {
            $note = Note::create([
                'title' => $noteData['title'],
                'note'  => $noteData['note'],
                'user_id' => $this->user->id,
            ]);

            // ✅ Notify per note, inside the loop
             Notification::route('mail', $this->user->email)
                        ->notify(new NewNote($note));

            $this->user->notify(new NewNote($note));
        }
    }
}