<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Consultation;
use App\Events\OPD\AppointmentBooked;

class TestAppointmentWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'webhook:test-appointment {id? : The Consultation ID to test with.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manually triggers an appointment booked event to test webhooks.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        
        $consultation = $id 
            ? Consultation::find($id) 
            : Consultation::latest()->first();

        if (!$consultation) {
            $this->error("No consultation found.");
            return 1;
        }

        $this->info("Triggering AppointmentBooked event for Consultation #{$consultation->id} (Token: {$consultation->token_number})...");

        event(new AppointmentBooked($consultation));

        $this->info("Event dispatched. Check webhook_logs or outbox for results.");
        
        return 0;
    }
}
