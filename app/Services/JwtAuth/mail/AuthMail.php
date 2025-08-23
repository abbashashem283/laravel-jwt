<?php

namespace App\Services\JwtAuth\mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\View;
use InvalidArgumentException;

class AuthMail extends Mailable
{
    use Queueable, SerializesModels;

    private $viewName;
    private $data;

    public function __construct($viewName, $data)
    {
        if (!View::exists($viewName)) 
            throw new InvalidArgumentException("View [{$viewName}] not found.");

        if(empty($data["subject"]))
            throw new InvalidArgumentException("Email data invalid");

        $this->viewName = $viewName;
        $this->data = $data ;   
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data["subject"]
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->viewName,
            with: $this->data["props"] ?? null 
        );
    }
}
