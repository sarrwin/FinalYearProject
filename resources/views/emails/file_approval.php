<!DOCTYPE html>
<html>
<head>
    <title>File Approval Notification</title>
</head>
<body>
    <p>Dear {{ $details['student_name'] ?? 'Student' }},</p>

    <p>Your file submission for "<strong>{{ $details['file_type'] }}</strong>" has been <strong>{{ ucfirst($details['approval_status']) }}</strong>.</p>

    @if (!empty($details['comment']))
        <p><strong>Supervisor's Comment:</strong> {{ $details['comment'] }}</p>
    @endif

    <p>Thank you.</p>
</body>
</html>
