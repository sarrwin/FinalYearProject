@extends('layouts.app')

@section('content')
<div class="container my-5">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    {{-- Tab Navigation --}}
    <ul class="nav nav-tabs" id="supervisorDashboardTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="announcement-tab" data-bs-toggle="tab" href="#announcements" role="tab" aria-controls="announcements" aria-selected="true">
                <i class="bi bi-megaphone"></i> General Announcements
            </a>
        </li>
        @foreach($projectRooms as $index => $room)
            <li class="nav-item">
                <a class="nav-link" id="room-tab-{{ $room->id }}" data-bs-toggle="tab" href="#room-{{ $room->id }}" role="tab" aria-controls="room-{{ $room->id }}" aria-selected="false">
                    <i class="bi bi-chat-dots"></i> {{ $room->title }}
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Tab Content --}}
    <div class="tab-content mt-4" id="supervisorDashboardTabContent">
        {{-- General Announcements Tab --}}
        <div class="tab-pane fade show active" id="announcements" role="tabpanel" aria-labelledby="announcement-tab">
            <div class="card shadow-sm border-0">
                <div class="card-body bg-[#D5C4F3]" >
                    <h3 class="card-title"><i class="bi bi-broadcast"></i> General Announcement Room</h3>
                    
                    {{-- Existing Announcements --}}
                    <div class="mb-4">
                        @if($announcementRoom)
                            <h5>Recent Announcements:</h5>
                            <ul class="list-group list-group-flush">
    @foreach($announcements as $announcement)
        <li class="list-group-item d-flex align-items-center justify-content-between">
            {{-- View Mode --}}
            <div class="view-mode" id="view-mode-{{ $announcement->id }}">
                <div class="d-flex align-items-center">
                    {{-- Profile Picture --}}
                    <a href="{{ route('supervisor.profile.edit', $announcement->user->id) }}" class="me-3">
                        <img src="{{ $announcement->user->profile_picture ? asset('uploads/' . $announcement->user->profile_picture) : asset('profile-placeholder.png') }}" 
                             alt="{{ $announcement->user->name }}" 
                             class="rounded-circle" 
                             style="width: 40px; height: 40px; object-fit: cover;">
                    </a>
                    <div>
                        <strong>
                            <a href="{{ route('supervisor.profile.edit', $announcement->user->id) }}" class="text-decoration-none">
                                {{ $announcement->user->name }}
                            </a>
                        </strong>: 
                        <span id="announcement-text-{{ $announcement->id }}">{{ $announcement->content }} <i class="fa fa-pencil text-primary" onclick="toggleEdit({{ $announcement->id }})"></i>  <form method="POST" action="{{ route('supervisor.announcement.delete', $announcement->id) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form></span>
                        <br>
                        <small class="text-muted">{{ $announcement->created_at->diffForHumans() }}</small>
                    </div>
                </div>
               
            </div>

            {{-- Edit Mode --}}
            <div class="edit-mode d-none" id="edit-mode-{{ $announcement->id }}">
                <form method="POST" action="{{ route('supervisor.announcement.edit', $announcement->id) }}" class="d-flex align-items-center w-100">
                    @csrf
                    <div class="flex-grow-1 me-3">
                        <input type="text" name="announcement" class="form-control" value="{{ $announcement->content }}" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-success me-2"><i class="fa fa-check"></i></button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleEdit({{ $announcement->id }})">
                        <i class="fa-solid fa-xmark"></i>
                    
                </form>
            </div>
                             
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No general announcements posted yet.</p>
                        @endif
                    </div>

                    {{-- Form to Post New Announcement --}}
                    <form method="POST" action="{{ route('supervisor.announcement.post') }}">
    @csrf
    <div class="form-group mb-3">
        <label for="announcement" class="form-label">Post a New Announcement:</label>
        <textarea name="announcement" class="form-control" rows="3" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Post Announcement</button>
</form>
                </div>
            </div>
        </div>


     







        {{-- Project Chat Rooms Tabs --}}
        @foreach($projectRooms as $room)
            <div class="tab-pane fade" id="room-{{ $room->id }}" role="tabpanel" aria-labelledby="room-tab-{{ $room->id }}">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title"><i class="bi bi-chat-left-dots"></i> {{ $room->title }}</h5>
                        
                        {{-- Chat Messages --}}
                        <div class="chat-messages border rounded p-3 mb-3" id="chat-messages-{{ $room->id }}" style="max-height: 250px; overflow-y: auto; background-color: #f9f9f9;">
                            <p class="text-muted">Loading messages...</p>
                        </div>

                        {{-- Form to Send a New Message --}}
                        <form class="send-message-form" data-room-id="{{ $room->id }}" method="POST">
                            @csrf
                            <div class="input-group">
                                <input type="text" name="message" class="form-control" placeholder="Type a message..." required>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send</button>
                            </div>
                        </form>

                        {{-- Delete Room Button --}}
                        <form action="{{ route('chatroom.delete', $room->id) }}" method="POST" class="mt-3">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Delete Room</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- JavaScript for Chat Features --}}
<script>
$(document).ready(function () {
    const POLLING_INTERVAL = 10000; // Polling interval for chat messages
    const ACTIVITY_INTERVAL = 30000; // Interval to notify backend about user activity
    let isEditing = false;

    /**
     * Notify the backend that the user is active in the chatroom.
     * @param {Number} roomId - The chatroom ID.
     */
    function markUserActive(roomId) {
        $.ajax({
            url: '/chatroom/active',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                room_id: roomId,
            },
            success: function () {
                console.log(`User activity recorded for room ${roomId}`);
            },
            error: function (xhr) {
                console.error(`Error marking user active in room ${roomId}:`, xhr.responseText);
            },
        });
    }

    /**
     * Periodically send activity status for the currently active tab.
     */
    setInterval(function () {
        const activeRoom = $('.tab-pane.active').attr('id'); // Get the active tab
        if (activeRoom) {
            const roomId = activeRoom.replace('room-', ''); // Extract the room ID
            markUserActive(roomId);
        }
    }, ACTIVITY_INTERVAL);

    /**
     * Load chat messages for the given room ID.
     * @param {Number} roomId - The chatroom ID.
     */

    
    // Function to load chat room messages via AJAX
    function loadRoomMessages(roomId) {
        if (isEditing) return;
    $.ajax({
        url: `/chatroom/messages/${roomId}`,
        method: 'GET',
        success: function(messages) {
            let chatBox = $(`#chat-messages-${roomId}`);
            chatBox.empty();

            messages.forEach(function(message) {
                let messageTime = new Date(message.created_at).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });

                let authId = "{{ Auth::id() }}"; // Pass the logged-in user's ID to JavaScript.
            let profileUrl = authId == message.user.id 
        ? "/supervisor/profile" 
        : `/students/profiless/${message.user.id}`;

    let profilePicture = message.user.profile_picture 
        ? `/uploads/${message.user.profile_picture}` 
        : '/profile-placeholder.png';
                // Determine the profile URL dynamically
                // let profileUrl;
                // if (currentUserId == message.user.id ) {
                //     // Supervisor clicks on their own picture
                //     profileUrl = "/supervisor/profile/";
                // } else {
                //     // Default to student profile view
                //     profileUrl = `/students/profile/${message.user.id}`;
                // }

                // let profilePicture = message.user.profile_picture 
                //     ? `/uploads/${message.user.profile_picture}` 
                //     : '/profile-placeholder.png';

                chatBox.append(`
                        <div id="message-${message.id}" class="d-flex align-items-center mb-3">
                            <a href="${profileUrl}" class="me-3">
                                <img src="${profilePicture}" 
                                     alt="${message.user.name}" 
                                     class="rounded-circle" 
                                     style="width: 40px; height: 40px; object-fit: cover;">
                            </a>
                            <div>
                                <strong>
                                    <a href="/students/profile/show/${message.user.id}" class="text-decoration-none">
                                        ${message.user.name}
                                    </a>
                                </strong>: 
                                <span id="message-content-${message.id}">${message.content}</span>
                                <input type="text" id="edit-input-${message.id}" class="form-control d-none" value="${message.content}" style="display: inline-block; width: 80%;">
                                <br>
                                <small class="text-muted">${messageTime}</small>

                                ${authId == message.user.id 
                                
                                    ? `<button class="btn btn-sm btn-secondary edit-message ms-2" data-message-id="${message.id}"><i class="fa fa-pencil text-primary"></i></button>
                                       <button class="btn btn-sm btn-primary save-message d-none ms-2" data-message-id="${message.id}">Save</button>
                                       <button class="btn btn-sm btn-danger delete-message ms-2" data-message-id="${message.id}"> <i class="fa fa-trash"></i></button>` 
                                    : ''}
                            </div>
                        </div>
                    `);
            });

            chatBox.scrollTop(chatBox[0].scrollHeight);
        },
        error: function(xhr) {
            console.error(`Error fetching messages for room ${roomId}:`, xhr.responseText);
        }
    });
}


    // Poll each chat room for new messages every 5 seconds
    setInterval(function() {
        @foreach($projectRooms as $room)
            loadRoomMessages({{ $room->id }});
        @endforeach
    }, POLLING_INTERVAL);


    $(document).on('submit', '.send-message-form', function(e) {
    e.preventDefault();

    let form = $(this);
    let roomId = form.data('room-id');
    let messageInput = form.find('input[name="message"]');
    let message = messageInput.val();

    if (!message.trim()) {
        alert('Message cannot be empty.');
        return;
    }

    $.ajax({
        url: `/chatroom/send-supervisor-message/${roomId}`,
        method: 'POST',
        data: {
            _token: "{{ csrf_token() }}",
            message: message
        },
        success: function(response) {
            messageInput.val(''); // Clear input field
            loadRoomMessages(roomId); // Reload messages for the room
        },
        error: function(xhr) {
            console.error(`Error sending message to room ${roomId}:`, xhr.responseText);
            alert('Failed to send the message. Please try again.');
        }
    });
});

 // Edit message
 $(document).on('click', '.edit-message', function() {
    isEditing = true; 
        let messageId = $(this).data('message-id');
        $(`#message-content-${messageId}`).addClass('d-none');
        $(`#edit-input-${messageId}`).removeClass('d-none').focus();
        $(this).addClass('d-none');
        $(`.save-message[data-message-id="${messageId}"]`).removeClass('d-none');
    });

    // Save edited message
    $(document).on('click', '.save-message', function() {
        let messageId = $(this).data('message-id');
        let newContent = $(`#edit-input-${messageId}`).val();

        $.ajax({
            url: `/chatroom/messagesEdit/${messageId}`,
            method: 'PATCH',
            data: {
                content: newContent,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $(`#message-content-${messageId}`).text(response.data.content).removeClass('d-none');
                $(`#edit-input-${messageId}`).addClass('d-none');
                $(`.edit-message[data-message-id="${messageId}"]`).removeClass('d-none');
                $(`.save-message[data-message-id="${messageId}"]`).addClass('d-none');
                isEditing = false;
            },
            error: function(xhr) {
                console.error('Error saving message:', xhr.responseText);
                isEditing = false;
            }
        });
    });


    $(document).on('click', '.delete-message', function() {
    if (!confirm("Are you sure you want to delete this message?")) return;

    let messageId = $(this).data('message-id');

    $.ajax({
        url: `/chatroom/messages/${messageId}`,
        method: 'DELETE',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $(`#message-${messageId}`).remove(); // Remove the message element from the DOM
            console.log(response.success);
        },
        error: function(xhr) {
            console.error('Error deleting message:', xhr.responseText);
        }
    });
});

});

function toggleEdit(id) {
    const viewMode = document.getElementById(`view-mode-${id}`);
    const editMode = document.getElementById(`edit-mode-${id}`);

    if (viewMode.classList.contains('d-none')) {
        viewMode.classList.remove('d-none');
        editMode.classList.add('d-none');
    } else {
        viewMode.classList.add('d-none');
        editMode.classList.remove('d-none');
    }
}
</script>
@endsection
