<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Registration Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <p>Hello {{ $attendee->name }},</p>

    <p>
        You are on the list for <strong>{{ $eventData['title'] }}</strong>.
    </p>

    <p>
        {{ $eventData['venue_name'] }}<br>
        {{ $eventData['location']['label'] }}<br>
        {{ $eventData['schedule']['starts_at'] }}
    </p>

    <p>
        Registration type: <strong>{{ ucfirst($attendee->attendance_status) }}</strong>
    </p>

    <p>
        We will send a reminder 3 days before and again 24 hours before the event.
    </p>
</body>
</html>
