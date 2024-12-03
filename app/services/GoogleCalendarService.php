<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use InvalidArgumentException;
class GoogleCalendarService
{
    public $client;
    protected $service;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET',));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        
        // Adding the CALENDAR_EVENTS scope for managing events
        $this->client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        // dd([
        //     'GOOGLE_CLIENT_ID' => env('GOOGLE_CLIENT_ID'),
        //     'GOOGLE_CLIENT_SECRET' => env('GOOGLE_CLIENT_SECRET'),
        //     'GOOGLE_REDIRECT_URI' => env('GOOGLE_REDIRECT_URI')
        // ]);
        // Initialize the Google Calendar service
        $this->service = new Google_Service_Calendar($this->client);
    }

    public function getAuthUrl()
    {
        return $this->client->createAuthUrl();
 

        
    
    }

    public function fetchAccessTokenWithAuthCode($code)
    {
        return $this->client->fetchAccessTokenWithAuthCode($code);
    }

//     public function setAccessToken($token)
// {
//     // Log the raw token for debugging
//     \Log::info('Raw Access Token: ', ['token' => $token]);

//     // Check if the token is empty
//     if (empty($token)) {
//         \Log::error('Access token is empty or null.');
//         throw new InvalidArgumentException('Access token cannot be empty or null.');
//     }

//     // If the token is supposed to be a plain string, skip JSON decoding
//     // Instead, just log or process the token directly
//     $accessToken = $token['access_token'] ?? null; // If you want to extract from an array

//     // If you received a string instead
//     if (is_string($token)) {
//         $accessToken = $token; // Directly assign if it's a string
//     } elseif (is_array($token) && isset($token['access_token'])) {
//         $accessToken = $token['access_token']; // Extract from array
//     }

//     // Additional checks for the access token can be implemented here
//     if (empty($accessToken)) {
//         \Log::error('Extracted Access Token is empty.');
//         throw new InvalidArgumentException('Access token cannot be empty after extraction.');
//     }

//     // Continue with saving or further processing the valid access token
//     \Log::info('Valid Access Token: ', ['access_token' => $accessToken]);

//     // Further processing of the token can be done here
// }

public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }

    public function getRefreshToken()
    {
        $token = $this->getAccessToken();
        return $token['refresh_token'] ?? null;
    }

    public function refreshAccessToken($refreshToken)
    {
        if ($this->client->isAccessTokenExpired()) {
            $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            return $this->client->getAccessToken();
        }
        return $this->getAccessToken();
    }

    

}
