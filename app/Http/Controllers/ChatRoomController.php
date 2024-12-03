<?php 

namespace App\Http\Controllers;

use Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\ChatRoom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Messages;
use App\Notifications\MessageNotification;
use App\Events\MessageSent;
class ChatRoomController extends Controller
{

    public function markActive(Request $request)
    {
        $userId = auth()->id();
        $roomId = $request->input('room_id');
        
        if (!$roomId) {
            Log::warning('No room_id provided in the request', ['user_id' => $userId]);
            return response()->json(['error' => 'room_id is required'], 400);
        }
    
        // Log user activity being marked as active
        Log::info('Marking user as active in chatroom', [
            'user_id' => $userId,
            'room_id' => $roomId,
            'timestamp' => now()->toDateTimeString()
        ]);
    
        // Mark the user as active in the chatroom
        Cache::put("user_active_in_chatroom_{$roomId}_{$userId}", true, now()->addMinutes(5));
    
        // Log cache success
        Log::info('User marked as active in cache', [
            'cache_key' => "user_active_in_chatroom_{$roomId}_{$userId}",
            'expires_at' => now()->addMinutes(5)->toDateTimeString()
        ]);
    
        return response()->json(['success' => true]);
    }
    // Show Chatroom
    public function show($id)
    {
        $chatRoom = ChatRoom::with('students')->findOrFail($id);

        // Ensure the user can access this room
        if (auth()->user()->isStudent() && !$chatRoom->students->contains(auth()->user()->id)) {
            abort(403);
        }

        if (auth()->user()->isSupervisor() && $chatRoom->supervisor_id != auth()->user()->supervisor->id) {
            abort(403);
        }

        return view('chatroom.show', compact('chatRoom'));
    }

    // Delete the chat room and all associated messages
    public function destroy($id)
    {
        // Find the chat room by its ID
        $chatRoom = ChatRoom::findOrFail($id);
        Log::info('Destroy method reached with ID: ' . $id);
        Log::info('ChatRoom found: ' . $chatRoom->id);

        // Ensure the supervisor owns the room
        if ($chatRoom->supervisor_id === optional(auth()->user()->supervisor)->supervisor_id) {
            Log::info('Supervisor authorized to delete ChatRoom: ' . $id);
            $chatRoom->delete(); // Delete the chat room
            return response()->json(['success' => 'Room deleted successfully.']);
        }

        Log::warning('User not authorized to delete ChatRoom.');
        return response()->json(['error' => 'You are not authorized to delete this room.'], 403);
    }

    // Send Student Message via AJAX
    public function sendStudentMessage(Request $request, $id)
{
    Log::info('sendStudentMessage method called by student ID: ' . Auth::id());

    $request->validate([
        'message' => 'required|string|max:255',
    ]);

    $chatRoom = ChatRoom::findOrFail($id);
    Log::info('Chat room found for student', [
        'chatroom_id' => $chatRoom->id,
        'supervisor_id' => $chatRoom->supervisor_id,
    ]);

    if ($this->isStudentAuthorized($chatRoom)) {
        $message = Messages::create([
            'chatroom_id' => $chatRoom->id,
            'user_id' => Auth::id(),
            'content' => $request->input('message'),
        ]);

        Log::info('Message saved successfully', [
            'message_id' => $message->id,
            'student_id' => Auth::id(),
        ]);

        // Notify all students in the chat room
        foreach ($chatRoom->students as $student) {
            if ($student->id !== Auth::id()) {
                $isStudentActive = Cache::get("user_active_in_chatroom_{$chatRoom->id}_{$student->id}", false);

                if (!$isStudentActive) {
                    try {
                        Log::info('Student is inactive, sending notification', [
                            'participant_id' => $student->id,
                            'message_id' => $message->id,
                        ]);

                        $student->notify(new MessageNotification($message));

                        Log::info('Notification sent successfully to student', [
                            'recipient_id' => $student->id,
                            'message_id' => $message->id,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Error sending notification to student', [
                            'recipient_id' => $student->id,
                            'message_id' => $message->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    Log::info('Student is active, no notification sent', [
                        'student_id' => $student->id,
                    ]);
                }
            }
        }

        // Notify the supervisor (via `user_id`)
        $supervisorUser = $chatRoom->supervisor ? $chatRoom->supervisor->user : null;

        if ($supervisorUser) {
            $isSupervisorActive = Cache::get("user_active_in_chatroom_{$chatRoom->id}_{$supervisorUser->id}", false);

            if (!$isSupervisorActive) {
                try {
                    Log::info('Supervisor is inactive, sending notification', [
                        'supervisor_id' => $supervisorUser->id,
                    ]);

                    $supervisorUser->notify(new MessageNotification($message));

                    Log::info('Notification sent successfully to supervisor', [
                        'supervisor_id' => $supervisorUser->id,
                        'message_id' => $message->id,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error sending notification to supervisor', [
                        'supervisor_id' => $supervisorUser->id,
                        'message_id' => $message->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::info('Supervisor is active, no notification sent', [
                    'supervisor_id' => $supervisorUser->id,
                ]);
            }
        } else {
            Log::warning('No supervisor user found for chat room', ['chatroom_id' => $chatRoom->id]);
        }

        Log::info('Message sent successfully by student ID: ' . Auth::id() . ' in chat room ID: ' . $chatRoom->id);
        return response()->json(['message' => 'Message sent successfully', 'data' => $message]);
    }

    Log::warning('Student not authorized to send message in chat room ID: ' . $chatRoom->id);
    return response()->json(['error' => 'You are not authorized to send a message in this room.'], 403);
}


    
    

    private function isStudentAuthorized($chatRoom)
    {
        $user = Auth::user();

        // Check if the user is a student and belongs to the chat room
        if ($user->isStudent() && $chatRoom->students()->where('users.id', $user->id)->exists()) {
            Log::info('Student is authorized in chat room: ' . $chatRoom->id);
            return true;
        }

        Log::warning('Student not authorized: ' . $user->id);
        return false;
    }

    // Send Supervisor Message via AJAX
    public function sendSupervisorMessage(Request $request, $id)
    {
        Log::info('sendSupervisorMessage method called by supervisor ID: ' . Auth::id());
    
        // Validate the message input
        $request->validate([
            'message' => 'required|string|max:255',
        ]);
    
        // Find the chat room
        $chatRoom = ChatRoom::findOrFail($id);
        Log::info('Chat room found for supervisor: ' . $chatRoom->id);
    
        // Check if the supervisor owns the chat room
        if ($this->isSupervisorAuthorized($chatRoom)) {
            // Save the message in the specified chat room
            $message = Messages::create([
                'chatroom_id' => $chatRoom->id,
                'user_id' => Auth::id(),
                'content' => $request->input('message'),
            ]);

            foreach ($chatRoom->students as $student) {
                if ($student->id !== Auth::id()) {
                    $isStudentActive = Cache::get("user_active_in_chatroom_{$chatRoom->id}_{$student->id}", false);
    
                    if (!$isStudentActive) {
                        try {
                            Log::info('Student is inactive, sending notification', [
                                'participant_id' => $student->id,
                                'message_id' => $message->id,
                            ]);
    
                            $student->notify(new MessageNotification($message));
    
                            Log::info('Notification sent successfully to student', [
                                'recipient_id' => $student->id,
                                'message_id' => $message->id,
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Error sending notification to student', [
                                'recipient_id' => $student->id,
                                'message_id' => $message->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    } else {
                        Log::info('Student is active, no notification sent', [
                            'student_id' => $student->id,
                        ]);
                    }
                }
            }
    
            Log::info('Message sent successfully by supervisor ID: ' . Auth::id() . ' in chat room ID: ' . $chatRoom->id);
            return response()->json(['message' => 'Message sent successfully', 'data' => $message]);
        } else {
            Log::warning('Supervisor not authorized to send message in chat room ID: ' . $chatRoom->id);
            return response()->json(['error' => 'You are not authorized to send a message in this room.'], 403);
        }
    }

    private function isSupervisorAuthorized($chatRoom)
    {
        $user = Auth::user();

        if ($user->isSupervisor() && $chatRoom->supervisor_id === optional($user->supervisor)->supervisor_id) {
            Log::info('Supervisor is authorized in chat room: ' . $chatRoom->id);
            return true;
        }

        Log::warning('Supervisor not authorized: ' . $user->id);
        return false;
    }

    // Fetch messages for AJAX polling
    public function fetchMessages($roomId)
    {
        $chatRoom = ChatRoom::findOrFail($roomId);
    
        // Check if the user is authorized to view this chat room
        if (auth()->user()->isStudent() && !$chatRoom->students->contains(auth()->user()->id)) {
            abort(403, 'You do not have access to this chat room.');
        }
    
        if (auth()->user()->isSupervisor() && $chatRoom->supervisor_id !== auth()->id()) {
            abort(403, 'You do not have access to this chat room.');
        }
    
        // If authorized, fetch the messages
        $messages = Messages::with('user')->where('chatroom_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->get();
    
        return response()->json($messages);
    }



    public function editMessage(Request $request, $messageId)
    {
        $request->validate([
            'content' => 'required|string|max:255',
        ]);
    
        $message = Messages::findOrFail($messageId);
    
        // Ensure the user is authorized to edit the message
        if ($message->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $message->update(['content' => $request->input('content')]);
    
        return response()->json(['success' => 'Message updated successfully', 'data' => $message]);
    }

    public function deleteMessage($messageId)
    {
        $message = Messages::findOrFail($messageId);
    
        // Ensure the user is authorized to delete the message
        if ($message->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        $message->delete();
    
        return response()->json(['success' => 'Message deleted successfully']);
    }
}
