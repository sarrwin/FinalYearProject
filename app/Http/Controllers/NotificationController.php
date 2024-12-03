<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //
    public function markAsRead(Request $request)
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back();
    }
}
