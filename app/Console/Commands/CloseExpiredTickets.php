<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseExpiredTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'تحويل حالة البطاقات إلى expired للمؤسسات التي أغلقت أبوابها';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currentTime = Carbon::now()->format('H:i');
        $closedBusinessIds = Business::where('close_hour', '<=', $currentTime);
        
        if ($closedBusinessIds->isEmpty()) {
            return Command::SUCCESS;
        }

        $tickets = $closedBusinessIds->services->queues->tickets()
        ->where('status', 'pending')    
        ->pluck('id');

        foreach ($tickets as $ticketId) {
            $ticket = Ticket::find($ticketId);
            $ticket->status = 'expired';
            $ticket->save();
        }
       

    $this->info('تم تحديث البطاقات المنتهية بنجاح.');
    return Command::SUCCESS;

    }
}
