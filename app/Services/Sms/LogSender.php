<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class LogSender implements SmsSender
{
    public function send($number, $text): void
    {
        $logData =  'to + ' . trim($number, ' + ') . ' text ' . $text;

        Log::channel('sms-sender')->debug($logData);
    }
}
