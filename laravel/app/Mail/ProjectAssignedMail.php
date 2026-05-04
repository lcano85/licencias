<?php

namespace App\Mail;

use App\Models\Projects;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProjectAssignedMail extends Mailable {
    
    use Queueable, SerializesModels;

    public $project;

    public function __construct(Projects $project) {
        $this->project = $project;
    }

    public function build() {
        return $this->subject('New Project Assigned: ' . $this->project->project_title)->markdown('emails.project_assigned');
    }
}