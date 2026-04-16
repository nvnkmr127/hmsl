<?php

namespace App\Listeners;

use App\Events\OPD\AppointmentBooked;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendAppointmentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(AppointmentBooked $event): void
    {
        $consultation = $event->consultation;
        $patient = $consultation->patient;

        if (!$patient || !$patient->phone) {
            return;
        }

        Log::info("Sending appointment confirmation to {$patient->phone} for Token #{$consultation->token_number}");

        // Integration with SMS/WhatsApp/Email Gateway would go here
        // Example:
        // Http::post('gateway-url', [
        //     'to' => $patient->phone,
        //     'message' => "Hi {$patient->first_name}, your appointment with Dr. {$consultation->doctor->full_name} is confirmed. Token: #{$consultation->token_number}."
        // ]);
    }
}
