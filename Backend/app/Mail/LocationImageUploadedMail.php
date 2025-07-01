<?php

namespace App\Mail;

use App\Models\LocationImage;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LocationImageUploadedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $image;

    public $user;

    public $location;

    public function __construct(LocationImage $image, User $user)
    {
        $this->image = $image;
        $this->user = $user;
        $this->location = $image->location;
    }

    public function build()
    {
        return $this->from($this->user->email, $this->user->name)
            ->subject('New Location Image Uploaded')
            ->view('emails.location_image_uploaded');
    }
}
