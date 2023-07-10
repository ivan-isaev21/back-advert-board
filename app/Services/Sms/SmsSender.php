<?php

namespace App\Services\Sms;

interface SmsSender
{    
    /**
     * Method send
     *
     * @param $phoneNumber 
     * @param $text 
     *
     * @return void
     */
    public function send($phoneNumber, $text): void;
}