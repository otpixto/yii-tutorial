<?php

namespace App\Jobs;

use App\Classes\GzhiHandler;
use App\Models\GzhiApiProvider;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GzhiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ticket;
    protected $gzhiProvider;

	public $tries = 1;

    public function __construct(Ticket $ticket, GzhiApiProvider $gzhiProvider)
    {
        $this->ticket = $ticket;

        $this->gzhiProvider = $gzhiProvider;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        (new GzhiHandler())->handleGzhiTicket($this->ticket, $this->gzhiProvider);
    }
}
