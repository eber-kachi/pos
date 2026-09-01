<?php

namespace Modules\ErrorAlerts\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\ErrorAlerts\Support\ErrorContext;

class ErrorAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ErrorContext $context, public int $repeats = 0)
    {
    }

    public function build()
    {
        $tag = (string) config('erroralerts.tag', config('app.name'));

        return $this->subject($this->context->subject($tag))
            ->view('erroralerts::emails.error', [
                'error' => $this->context->toArray(),
                'repeats' => $this->repeats,
                'tag' => $tag,
                'appName' => config('app.name'),
            ]);
    }
}
