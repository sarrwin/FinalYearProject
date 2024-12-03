<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function create()
    {
        $feedbacks = Feedback::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        return view('feedback.create', compact('feedbacks'));
    }

    public function store(Request $request)
{
    $request->validate([
        'subject' => 'required|string|max:255',
        'message' => 'required|string|min:10',
        'screenshot' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $screenshotPath = null;

    if ($request->hasFile('screenshot')) {
        $screenshotPath = $request->file('screenshot')->store('feedback_screenshots', 'public');
    }

    Feedback::create([
        'user_id' => Auth::id(),
        'subject' => $request->subject,
        'message' => $request->message,
        'screenshot' => $screenshotPath,
        'status' => 'pending',
    ]);

    return redirect()->back()->with('success', 'Thank you for your feedback! We will look into it.');
}

    public function index()
    {
        $feedbacks = Feedback::orderBy('created_at', 'desc')->get();

        return view('feedback.index', compact('feedbacks'));
    }

    public function resolve($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update(['status' => 'resolved']);

        return redirect()->back()->with('success', 'Feedback marked as resolved.');
    }
}
