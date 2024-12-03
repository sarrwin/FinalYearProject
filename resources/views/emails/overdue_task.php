<!DOCTYPE html>
<html>
<head>
    <title>Task Overdue Notification</title>
</head>
<body>
    <h1>Task Overdue Notification</h1>
    <p>Dear {{ $recipient->name }},</p>
    <p>The following task is overdue:</p>
    <p><strong>Task Title:</strong> {{ $task->title }}</p>
    <p><strong>Due Date:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}</p>
    <p>Please take necessary action as soon as possible.</p>
</body>
</html>
