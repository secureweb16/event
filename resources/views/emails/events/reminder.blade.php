<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <p>Hello {{ $attendee->name }},</p>

    <p>
        {{ $hoursUntilStart === 72 ? 'This is your 3-day reminder.' : 'This event starts in about 24 hours.' }}
    </p>

    <p>
        <strong>{{ $eventData['title'] }}</strong><br>
        {{ $eventData['venue_name'] }}<br>
        {{ $eventData['location']['label'] }}<br>
        {{ $eventData['schedule']['starts_at'] }}
    </p>

    <p>
        Your response: <strong>{{ ucfirst($attendee->attendance_status) }}</strong>
    </p>
</body>
</html>
