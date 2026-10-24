<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .type-label { color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        .title { font-size: 22px; margin: 5px 0; }
        .date { color: #666; font-size: 12px; }
        .body { font-size: 14px; line-height: 1.8; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <div>{{ get_option('school_name') }}</div>
        <div class="type-label">{{ $notice->type }}</div>
        <div class="title">{{ $notice->title }}</div>
        <div class="date">{{ optional($notice->date)->format('d M, Y') }}</div>
    </div>
    <div class="body">
        {!! $notice->renderedBody() !!}
    </div>
</body>
</html>
