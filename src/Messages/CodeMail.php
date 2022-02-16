<?php

namespace Lijinhua\Email\Messages;

use Illuminate\Mail\Mailable;

class CodeMail extends Mailable
{
    public function __construct(public string $code, public int $validMinutes)
    {
    }

    public function build()
    {
        $view = config('email.views.code');
        return $this
            ->subject('邮箱验证码')
            ->view($view);
    }
}