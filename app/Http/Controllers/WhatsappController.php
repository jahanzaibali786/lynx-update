<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;
use Illuminate\Http\Request;

class WhatsappController extends Controller
{
    // public function sendWhatsAppMessageWithPDF()
    // {
    //     // $twilioSid = 'HXb5b62575e6e4ff6129ad7c8efe1f983e';
    //     // $twilioAuthToken = 'd82b73c24ff233e6d77e565424a800db';
    //     $twilioSid = 'AC3e6f03e752b769af6b2a2dbd586f34cc';
    //     $twilioAuthToken = 'd82b73c24ff233e6d77e565424a800db';
    //     $twilioWhatsAppNumber = 'whatsapp:+19152137736';
    //     $recipientWhatsAppNumber = 'whatsapp:+923363999481'; 

    //     $client = new Client($twilioSid, $twilioAuthToken);

    //     try {
    //         $client->messages->create(
    //             $recipientWhatsAppNumber,
    //             [
    //                 'from' => $twilioWhatsAppNumber,
    //                 'body' => 'Jahanzaib Testing...'
    //             ]
    //         );

    //         return response()->json(['status' => 'Message sent successfully!']);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'Failed to send message', 'error' => $e->getMessage()]);
    //     }
    // }

    // public function sendWhatsAppMessageWithPDF()
    // {
    //     $sid = 'AC3e6f03e752b769af6b2a2dbd586f34cc';
    //     $token = 'd82b73c24ff233e6d77e565424a800db';
    //     $twilioWhatsAppNumber = 'whatsapp:+14155238886';
    //     $recipientWhatsAppNumber = 'whatsapp:+923452214990'; // Replace with recipient's number

    //     // Create Twilio client
    //     $client = new Client($sid, $token);

    //     try {
    //         // Send the message with content and variables
    //         $message = $client->messages->create(
    //             $recipientWhatsAppNumber, // to
    //             [
    //                 'from' => $twilioWhatsAppNumber, // from
    //                 'body' => 'Jahanzaib Testing...', // message body
    //                 'contentSid' => 'HXb5b62575e6e4ff6129ad7c8efe1f983e', // your content SID
    //                 'contentVariables' => '{"1":"12/1","2":"3pm"}' // custom content variables
    //             ]
    //         );

    //         // Return success message with message SID
    //         return response()->json([
    //             'status' => 'Message sent successfully!',
    //             'message_sid' => $message->sid
    //         ]);
    //     } catch (\Exception $e) {
    //         // Return error message
    //         return response()->json([
    //             'status' => 'Failed to send message',
    //             'error' => $e->getMessage()
    //         ]);
    //     }
    // }
    public function sendWhatsAppMessageWithPDF()
    {
        $sid = 'AC9e90302661b3e563b4e8dc7d806f9edb';
        $token = 'f50c0a608d8dc0cf7fb01fd5fba8bec1';
        $to = 'whatsapp:+923452214990';
        $from = 'whatsapp:+14155238886';
        $contentSid = 'HXb5b62575e6e4ff6129ad7c8efe1f983e';
        $contentVariables = json_encode(["1" => "12/1", "2" => "3pm"]);

        $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";

        // Prepare the POST data
        $data = [
            'To' => $to,
            'From' => $from,
            'ContentSid' => $contentSid,
            'ContentVariables' => $contentVariables
        ];

        // Initialize curl
        $ch = curl_init($url);

        // Set curl options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Execute the request
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            return response()->json([
                'status' => 'Failed to send message',
                'error' => $error,
            ], 500);
        }
        curl_close($ch);
        $responseData = json_decode($response, true);
        if (isset($responseData['error_code'])) {
            return response()->json([
                'status' => 'Failed to send message',
                'error' => $responseData['message'],
            ], 500);
        }

        // Successful response
        return response()->json([
            'status' => 'Message sent successfully!',
            'sid' => $responseData['sid'] ?? 'N/A',
        ]);
    }

}
