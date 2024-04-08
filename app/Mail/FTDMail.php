<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FTDMail extends Mailable
{
    use Queueable, SerializesModels;

    public $input;
    public $images;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($input,$images)
    {
        $this->input = $input;
        $this->images = $images;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $address = $this->input['email_marketing'];
        $name = $this->input['name_marketing'];

        $email = $this->from("dealing@tpfx.co.id", $name)
            ->markdown('ftd.email')
            ->subject($this->input['ftd_type'] . ' ' . $this->input['name']);

        if ($this->images) {
            $images = $this->images;
            foreach ($images as $key => $image) {
                $filename = 'image'.($key+1).".jpg";
                $email->attach($image, [
                    'as' => $filename,
                    'mime' => 'image/jpg'
                ]);
            }
        }

        return $email;
    }
}
