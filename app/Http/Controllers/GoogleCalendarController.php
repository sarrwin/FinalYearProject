<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleCalendarService;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Log;
use App\Models\User;

class GoogleCalendarController extends Controller
{
    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        $this->googleCalendarService = $googleCalendarService;
    }

    /**
     * Redirects the user to Google for OAuth consent.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToGoogle()
    {
        $user = Auth::user();
    
        // Ensure user is authenticated before proceeding
        if (!$user instanceof User) {
            return redirect()->route('login')->with('error', 'User not authenticated. Please log in.');
        }
    
        // Clear tokens if they need to be reset
        if ($this->tokensNeedReset($user)) {
            $user->google_token = null;
            $user->google_refresh_token = null;
            $user->save();
        }
    
        return redirect()->away($this->googleCalendarService->getAuthUrl());
    }
    

    /**
     * Handles the Google OAuth callback and stores tokens.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
{
    if (!$request->has('code')) {
        return redirect()->route('dashboard')->with('error', 'Authorization code not provided.');
    }

    try {
        // Fetch access token with the provided authorization code
        $accessToken = $this->googleCalendarService->fetchAccessTokenWithAuthCode($request->code);
        \Log::info('Access Token Response:', ['accessToken' => $accessToken]);
    
        $user = Auth::user();

        // Validate the access token response and check if data is nested in an "accessToken" key
        $accessTokenData = $accessToken['accessToken'] ?? $accessToken;
        if (!isset($accessTokenData['access_token'])) {
            throw new InvalidArgumentException('Access token is not available.');
        }
    
        // Store access token and refresh token (if provided)
        $user->google_token = $accessTokenData['access_token'];
        if (isset($accessTokenData['refresh_token'])) {
            $user->google_refresh_token = $accessTokenData['refresh_token'];
        }
        
        // Log token values before saving for debugging purposes
        \Log::info('Saving Google Tokens:', [
            'google_token' => $user->google_token,
            'google_refresh_token' => $user->google_refresh_token ?? 'No refresh token provided'
        ]);
    
        // Save the user's tokens
        $user->save();
    
        // Redirect the user based on their role
        $redirectRoute = match (true) {
            $user->isSupervisor() => 'slots.index',
            $user->isStudent() => 'students.appointments.index',
            default => 'dashboard',
        };
    
        return redirect()->route($redirectRoute)->with('success', 'Google Calendar connected successfully.');
    } catch (InvalidArgumentException $e) {
        return redirect()->route('dashboard')->with('error', 'Invalid token: ' . $e->getMessage());
    } catch (\Exception $e) {
        \Log::error('Failed to connect to Google Calendar: ' . $e->getMessage());
        return redirect()->route('dashboard')->with('error', 'Failed to connect to Google Calendar. Please try again.');
    }
}


    /**
     * Saves Google tokens to the user's record.
     *
     * @param \App\Models\User $user
     * @param array $accessToken
     * @return void
     */
    protected function saveTokens(User $user, array $accessToken)
    {
        if (!isset($accessToken['access_token'])) {
            throw new InvalidArgumentException('Access token is not available.');
        }
    
        // Store access token and refresh token (if provided)
        $user->google_token = $accessToken['access_token'];
        if (isset($accessToken['refresh_token'])) {
            $user->google_refresh_token = $accessToken['refresh_token'];
        }
        
        // Log token values for debugging
        Log::info('Saving Google Tokens:', [
            'google_token' => $user->google_token,
            'google_refresh_token' => $user->google_refresh_token ?? 'No refresh token provided'
        ]);
    
        // Save the user's tokens
        $user->save();
    }

    /**
     * Checks if tokens need to be reset.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    protected function tokensNeedReset($user)
    {
        return !$user->google_token || !$user->google_refresh_token;
    }
}
