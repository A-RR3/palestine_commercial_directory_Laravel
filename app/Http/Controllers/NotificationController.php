<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;
use App\Services\FirebaseCloudMessaging;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    protected $fcm;
    protected $firebaseCloudMessaging;

    public function __construct(FirebaseCloudMessaging $fcm, FirebaseCloudMessaging $firebaseCloudMessaging)
    {
        $this->fcm = $fcm;
        $this->firebaseCloudMessaging = $firebaseCloudMessaging;
    }

    public function sendNotification(Request $request)
    {
        return $this->firebaseCloudMessaging->sendNotification($request);
    }

    public function getAllUserTokens(Request $request)
    {
        $devices = DeviceToken::Where('d_user_id', $request->u_id);
        return $devices;
    }



    public function generateAccessToken()
    {
        // Check if the token is already cached
        if (Cache::has('fcm_access_token')) {
            return response()->json([
                'fcm_access_token' => Cache::get('fcm_access_token'),
                'from cache' => true
            ]);
        }
        // configuring and initializing a Google Client, used for authenticating and interacting with Google services such as Firebase.
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


    public function saveDeviceToken(Request $request)
    {
        $token = DeviceToken::Where([
            ['d_user_id', $request->d_user_id],
            ['device_token', $request->device_token],
        ])->get();


        $request->validate([
            'd_user_id' => 'required|exists:users,u_id',
            'device_token' => 'required|string|unique:device_tokens,device_token',
            'device_type' => 'nullable|string'
        ]);

        // Create a new device token record for the user
        DeviceToken::create([
            'd_user_id' => $request->d_user_id,
            'device_token' => $request->device_token,
            'device_type' => $request->device_type,
        ]);

        return response()->json(['message' => 'Device token saved successfully.']);
    }


    public function removeDeviceToken(Request $request)
    {
        $request->validate([
            'd_user_id' => 'required|exists:users,u_id',
            'device_token' => 'required|string'
        ]);

        $userId = $request->d_user_id;
        $deviceToken = $request->device_token;

        // Delete device token for the specified user and tokens
        DeviceToken::Where(
            [
                ['d_user_id',  $userId],
                ['device_token', $deviceToken]
            ]
        )
            ->delete();

        return response()->json(['message' => 'Device token deleted successfully.']);
    }

    public function updateDeviceToken(Request $request)

    {
        $userId = $request->user_id;
        $newToken = $request->new_token;
        $oldToken = $request->old_token;

        $token = DeviceToken::where(
            [
                ['d_user_id',  $userId],
                ['device_token', $oldToken]
            ]
        )->get();

        //update token
        $token->device_token = $newToken;
        $token->save();

        return response()->json(['message' => 'Device tokens updated successfully.']);
    }
}
