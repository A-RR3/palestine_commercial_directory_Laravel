<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class FirebaseCloudMessaging
{
    protected $client;
    protected $projectId;
    protected $serviceAccountPath;

    public function __construct()
    {
        $this->client = new Client();
        $this->projectId = env('FIREBASE_PROJECT_ID');
        $this->serviceAccountPath = storage_path('app\todo-firebase-a1916-firebase-adminsdk-2yt75-d919e6a925.json');
    }

    public function sendNotification(Request $request)
    // $token, $title, $body, $data = []
    {
        try {
            $url = "https://fcm.googleapis.com/v1/projects/$this->projectId/messages:send";
            $message = $request->input('message');
            $token = $message['token'] ?? null;
            $title = $message['notification']['title'] ?? null;
            $body = $message['notification']['body'] ?? null;
            $data = $message['data'] ?? [];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->generateAccessToken(),
                'Content-Type' => 'application/json',
                'Host' => 'fcm.googleapis.com'
            ])->post($url, [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data

                ],
            ]);

            if (!$response->ok()) {
                Log::error('FCM HTTP v1 notification failed', [
                    'response' => $response->getBody()->getContents(),
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Notification failed to send',
                    'response' => $response->json()['error']['message'],
                ]);
            }
            return response()->json([
                'status' => false,
                'message' => 'Notification sent successfully',
                'response'=> $response->json()
            ], $response->getStatusCode());
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    // public function getAccessToken()
    // {
    //     $serviceAccount = json_decode(file_get_contents($this->serviceAccountPath), true);

    //     $jwt = $this->createJwt($serviceAccount);

    //     $response = $this->client->post('https://www.googleapis.com/oauth2/v4/token', [
    //         'form_params' => [
    //             'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
    //             'assertion' => $jwt,
    //         ],
    //     ]);

    //     $data = json_decode($response->getBody()->getContents(), true);

    //     return $data['access_token'];
    // }
    public function generateAccessToken()
    {
        // Check if the token is already cached
        if (Cache::has('fcm_access_token')) {
            // return response()->json([
            //     'fcm_access_token'=>Cache::get('fcm_access_token'),
            //     'from cache']);
            return Cache::get('fcm_access_token');
        }

        $credentialsFilePath = 'C:\xampp\htdocs\laravel_project\storage\app\todo-firebase-a1916-firebase-adminsdk-2yt75-d919e6a925.json'; //replace this with your actual path and file name
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();

        if (isset($token)) {
            // Cache the token and set the expiration time
            Cache::put('fcm_access_token', $token['access_token'], $token['expires_in'] - 60); // Subtracting 60 seconds to account for any delays
            return $token['access_token'];
        } else {
            throw new \Exception('Could not fetch access token: ' . $token);
        }
    }

    // public function sendNotificationWithOptions(){
    //     sendNotification(
    //         "ePPMI2qMS_6PQ3EdHBzmMa:APA91bGNm53CamSdof3mUndpBsq-EOFzMtbdFp9mW5r5GWiN2ljwAIlZIqHq-F29tRu8G9wOA-qFnrVyxcVSKQ4cSBwIwG4iUudGu1R3EeNJiWAc9ksL7eQnMAVosBqBPW-Snit31iTE",
    //         "title",
    //         "body",
    //         ['post_id'=>3,"liker"=>3]
    //     );
    // } 
}
     

    // public function getAccessToken()
    // {     
    //     $jsonKey = json_decode(file_get_contents($this->serviceAccountPath), true);
        
    //     $oauth = new OAuth2([
    //         'client_id' => $jsonKey['client_id'],
    //         'client_email' => $jsonKey['client_email'],
    //         'private_key' => $jsonKey['private_key'],
    //         'token_uri' => 'https://oauth2.googleapis.com/token',
    //         'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    //         'audience' => 'https://oauth2.googleapis.com/token',
    //         'subject' => $jsonKey['client_email'],
    //     ]);

    //     $accessToken = $oauth->fetchAuthToken();
    //     if (isset($accessToken['access_token'])) {
    //         return $accessToken['access_token'];
    //     } else {
    //         throw new \Exception('Could not fetch access token: ' . json_encode($accessToken));
    //     }
    // }
    // public function getAccessToken()
    // {
    //     $oauth = new OAuth2([
    //         'audience' => 'https://oauth2.googleapis.com/token',
    //         'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
    //         'signing_key' => file_get_contents($this->serviceAccountPath),
    //         'signing_algorithm' => 'RS256',
    //     ]);

    //     $accessToken = $oauth->fetchAuthToken();
    //     return $accessToken['access_token'];
    // }
    // protected function createJwt($serviceAccount)
    // {
    //     $now = time();
    //     $jwtHeader = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
    //     $jwtClaim = json_encode([
    //         'iss' => $serviceAccount['client_email'],
    //         'scope' => 'https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/firebase.messaging',
    //         'aud' => 'https://www.googleapis.com/oauth2/v4/token',
    //         'iat' => $now,
    //         'exp' => $now + 3600,
    //     ]);

    //     $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($jwtHeader));
    //     $base64UrlClaim = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($jwtClaim));

    //     $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlClaim, $serviceAccount['private_key'], true);
    //     $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

    //     return $base64UrlHeader . "." . $base64UrlClaim . "." . $base64UrlSignature;
    // }
