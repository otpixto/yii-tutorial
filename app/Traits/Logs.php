<?php

namespace App\Traits;

use App\Models\Log;

trait Logs
{
    public function addLog ( $text )
    {
        $log = Log::create( compact( 'text' ) );
        $log->save();
    }
}
