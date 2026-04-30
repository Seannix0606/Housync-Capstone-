<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - HouSync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            width: 100%;
            max-width: 760px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.12);
            padding: 36px;
        }
        .header {
            text-align: center;
            margin-bottom: 28px;
        }
        .header h1 {
            font-size: 30px;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        .header p {
            color: #6b7280;
            font-size: 15px;
        }
        .choices {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .choice {
            display: block;
            text-decoration: none;
            border: 1px solid #dbe2ea;
            border-radius: 14px;
            padding: 20px;
            color: #1f2937;
            transition: all .2s ease;
            background: #fafcff;
        }
        .choice:hover {
            border-color: #4ecdc4;
            box-shadow: 0 8px 24px rgba(78, 205, 196, 0.15);
            transform: translateY(-2px);
        }
        .choice h2 {
            font-size: 20px;
            margin-bottom: 8px;
        }
        .choice p {
            color: #6b7280;
            line-height: 1.5;
        }
        .footer {
            margin-top: 24px;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        .footer a {
            color: #4ecdc4;
            text-decoration: none;
            font-weight: 600;
        }
        .footer a:hover { text-decoration: underline; }
        @media (max-width: 768px) {
            .card { padding: 24px; }
            .choices { grid-template-columns: 1fr; }
            .header h1 { font-size: 24px; }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Create your account</h1>
            <p>Choose the type of registration that fits you.</p>
        </div>

        <div class="choices">
            <a href="{{ route('register.tenant') }}" class="choice">
                <h2>I’m looking to rent</h2>
                <p>Create a tenant account and apply for available units.</p>
            </a>
            <a href="{{ route('landlord.register') }}" class="choice">
                <h2>I want my property to be rented</h2>
                <p>Register as a landlord and list your property for tenants.</p>
            </a>
        </div>

        <div class="footer">
            Already have an account? <a href="{{ route('login') }}">Sign in</a>
        </div>
    </div>
</body>
</html>
