<?php

namespace App\Service;

use Twilio\Rest\Client;

class WhatsAppService
{
    public function send(string $message)
    {
        $phone = config('app.my_whatsapp_number');

        $twilioSid = config('services.twilio.sid');
        $twilioAuthToken = config('services.twilio.auth_token');
        $twilioWhatsappNumber = 'whatsapp:' . config('services.twilio.whatsapp_number');

        $to = 'whatsapp:' . $phone;
        $message = $message;
        $client = new Client($twilioSid, $twilioAuthToken);

        try {
            // Send the WhatsApp message using Twilio's API
            $message = $client->messages->create(
                $to,
                array(
                    'from' => $twilioWhatsappNumber,
                    'body' => $message
                )
            );
            return "Message sent successfully! SID: " . $message->sid;
        } catch (\Exception $e) {
            // Catch any errors and return the error message
            return "Error sending message: " . $e->getMessage();
        }
    }
}
