<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HouseSync')</title>
    <style>
        body { margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f4f6f8; color: #1e293b; }
        .email-wrapper { max-width: 600px; margin: 0 auto; padding: 20px; }
        .email-header { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); border-radius: 12px 12px 0 0; padding: 24px 32px; text-align: center; }
        .email-header h1 { color: #ffffff; font-size: 24px; margin: 0; font-weight: 700; letter-spacing: 0.5px; }
        .email-body { background: #ffffff; padding: 32px; border-left: 1px solid #e5e7eb; border-right: 1px solid #e5e7eb; }
        .email-footer { background: #f9fafb; border-radius: 0 0 12px 12px; padding: 20px 32px; text-align: center; font-size: 13px; color: #6b7280; border: 1px solid #e5e7eb; border-top: none; }
        .email-footer a { color: #f97316; text-decoration: none; }
        h2 { font-size: 20px; color: #1e293b; margin-top: 0; }
        p { font-size: 15px; line-height: 1.6; color: #374151; }
        .btn { display: inline-block; background: #f97316; color: #ffffff !important; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 600; font-size: 15px; margin: 16px 0; }
        .info-box { background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0; }
        .info-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 14px; }
        .info-label { color: #6b7280; }
        .info-value { color: #1e293b; font-weight: 600; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 9999px; font-size: 12px; font-weight: 600; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
    </style>
</head>
<body>
<div class="email-wrapper">
    <div class="email-header">
        <h1>HouseSync</h1>
    </div>
    <div class="email-body">
        @yield('body')
    </div>
    <div class="email-footer">
        <p style="margin:0">This email was sent by <a href="{{ config('app.url') }}">HouseSync</a>.</p>
        <p style="margin:4px 0 0">{{ config('app.name', 'HouseSync') }} - Property Management System</p>
    </div>
</div>
</body>
</html>
