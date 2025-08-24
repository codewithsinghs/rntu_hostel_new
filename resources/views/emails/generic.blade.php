<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject ?? 'Notification' }}</title>
</head>
<body>
    <h2>{{ $subject ?? 'Notification' }}</h2>
    <p>{{ $body ?? 'No content provided.' }}</p>
</body>
</html>
