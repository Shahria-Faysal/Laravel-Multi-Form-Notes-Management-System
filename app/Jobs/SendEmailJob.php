<?php

namespace App\Jobs;

use App\Models\Note;
use App\Models\User;
use App\Notifications\NewNote;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


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
                'note' => $noteData['note'],
                'user_id' => $this->user->id,
            ]);

            Log::info('Notification triggered for user '.$this->user->id);

            $this->user->notify(new NewNote($note));

            // ✅ Notify per note, inside the loop
            //  Notification::route('mail', $this->user->email)
            //             ->notify(new NewNote($note));

            // $this->user->notify(new NewNote($note->id, $note->title, $note->note, $note->user_id));
            // $this->user->notify(new NewNote($note->id, $noteData['title'], $noteData['note'], $this->user->id));

            // Log::info('About to notify user ' . $this->user->id . ' for note ' . $note->id);

            // $this->user->notify(new NewNote($note));

            // Log::info('Notification sent');
        }
    }
}