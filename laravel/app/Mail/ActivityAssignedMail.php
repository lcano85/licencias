<?php

namespace App\Mail;

use App\Models\Activities;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;


class ActivityAssignedMail extends Mailable {
    use Queueable, SerializesModels;

    public $activity;

    public function __construct(Activities $activity) {
        $this->activity = $activity;
    }

    public function build() {
        return $this->subject('New Activity Assigned: ' . $this->activity->activity_name)->markdown('emails.activity_assigned');
    }
}
