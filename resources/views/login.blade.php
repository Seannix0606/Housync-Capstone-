<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Housesync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-wrapper {
            display: flex;
            width: 100%;
            max-width: 1200px;
            min-height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Form Section */
        .form-section {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .form-container {
            width: 100%;
            max-width: 400px;
        }

        .brand {
            margin-bottom: 30px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            border-radius: 8px;
        }

        .title {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        /* Form Styling */
        .auth-form {
            margin-bottom: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            color: #2c3e50;
            background: #f8f9fa;
            transition: all 0.2s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4ecdc4;
            background: white;
            box-shadow: 0 0 0 3px rgba(78, 205, 196, 0.1);
        }

        .form-group input::placeholder {
            color: #bdc3c7;
        }

        /* Form Options */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #4ecdc4;
        }

        .remember-me label {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 0;
            cursor: pointer;
        }

        .forgot-password {
            font-size: 14px;
            color: #4ecdc4;
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password:hover {
            text-decoration: underline;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 14px 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        /* Auth Switch */
        .auth-switch {
            text-align: center;
            font-size: 14px;
            color: #7f8c8d;
        }

        .auth-switch a {
            color: #4ecdc4;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-switch a:hover {
            text-decoration: underline;
        }

        /* Illustration Section */
        .illustration-section {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            min-height: 600px;
        }

        .illustration-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Isometric Platforms */
        .iso-platform {
            position: absolute;
            perspective: 1000px;
            transform-style: preserve-3d;
        }

        .platform-1 {
            top: 15%;
            left: 20%;
            transform: rotateX(60deg) rotateY(-45deg);
        }

        .platform-2 {
            top: 25%;
            right: 25%;
            transform: rotateX(60deg) rotateY(45deg);
        }

        .platform-3 {
            bottom: 30%;
            left: 15%;
            transform: rotateX(60deg) rotateY(-30deg);
        }

        .platform-4 {
            bottom: 20%;
            right: 20%;
            transform: rotateX(60deg) rotateY(30deg);
        }

        .platform-5 {
            top: 50%;
            left: 50%;
            transform: translateX(-50%) translateY(-50%) rotateX(60deg) rotateY(0deg);
        }

        /* Platform Base */
        .iso-platform::before {
            content: '';
            position: absolute;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transform: translateZ(0);
        }

        /* Characters */
        .person {
            position: absolute;
            width: 20px;
            height: 40px;
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            transform: translateZ(10px);
        }

        .person-1 {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            top: -20px;
            left: 10px;
        }

        .person-2 {
            background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
            top: -20px;
            right: 10px;
        }

        .person-3 {
            background: linear-gradient(135deg, #45b7d1 0%, #96c93d 100%);
            top: -20px;
            left: 30px;
        }

        .person-4 {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            top: -20px;
            right: 30px;
        }

        .person-5 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: -20px;
            left: 30px;
        }

        /* Charts and Devices */
        .chart, .device {
            position: absolute;
            transform: translateZ(5px);
            border-radius: 4px;
        }

        .chart-1 {
            width: 30px;
            height: 20px;
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            top: 10px;
            right: 10px;
        }

        .chart-2 {
            width: 25px;
            height: 25px;
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            top: 15px;
            left: 15px;
        }

        .chart-3 {
            width: 35px;
            height: 15px;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            top: 20px;
            left: 10px;
        }

        .chart-4 {
            width: 20px;
            height: 30px;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            top: 5px;
            right: 15px;
        }

        .device-1 {
            width: 40px;
            height: 25px;
            background: linear-gradient(135deg, #2193b0 0%, #6dd5ed 100%);
            top: 20px;
            right: 5px;
        }

        /* Floating Cubes */
        .floating-elements {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .cube {
            position: absolute;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            opacity: 0.7;
            animation: float 6s ease-in-out infinite;
        }

        .cube-1 {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .cube-2 {
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
            top: 20%;
            right: 15%;
            animation-delay: 1s;
        }

        .cube-3 {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            bottom: 25%;
            left: 25%;
            animation-delay: 2s;
        }

        .cube-4 {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
            bottom: 15%;
            right: 10%;
            animation-delay: 3s;
        }

        .cube-5 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: 60%;
            left: 70%;
            animation-delay: 4s;
        }

        .cube-6 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            top: 40%;
            right: 40%;
            animation-delay: 5s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(180deg);
            }
        }

        /* Properties landing */
        .properties-landing { position: relative; z-index: 2; padding: 32px; color: #fff; height: 100%; }
        .cta-hero { position:absolute; left:50%; top:60%; transform: translate(-50%, -50%); text-align:center; }
        .cta-hero h2 { font-size: 36px; font-weight: 800; letter-spacing: .2px; margin-bottom: 8px; }
        .cta-hero p { opacity:.95; margin-bottom: 20px; font-size: 14px; }
        .cta-primary { display:inline-block; padding: 12px 18px; border-radius: 999px; color:#fff; text-decoration:none; border:1px solid rgba(255,255,255,.35); background: rgba(255,255,255,.18); backdrop-filter: blur(6px); box-shadow: 0 8px 24px rgba(0,0,0,.15); }
        .cta-primary:hover { background: rgba(255,255,255,.25); }

        /* Responsive Design */
        @media (max-width: 768px) {
            .auth-wrapper {
                flex-direction: column;
                max-width: 100%;
            }
            
            .form-section {
                padding: 40px 20px;
            }
            
            .illustration-section {
                min-height: 300px;
            }
            
            .form-container {
                max-width: 100%;
            }
        }
    </style>

</head>
<body>
    <div class="container">
        <div class="auth-wrapper">
            <!-- Left side - Form -->
            <div class="form-section">
                <div class="form-container">
                    <div class="brand">
                        <div class="brand-icon"></div>
                    </div>
                    
                    <h1 class="title">HouSync</h1>
                    <p class="subtitle">Login</p>
                    
                    
                    @if($errors->any())
                        <div class="alert alert-error">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="auth-form" method="POST" action="{{ route('login.post') }}">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email*</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password*</label>
                            <input type="password" id="password" name="password" placeholder="minimum 8 characters" required>
                        </div>
                        
                        <div class="form-options">
                            <div class="remember-me">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Remember me</label>
                            </div>
                            <a href="#" class="forgot-password">Forgot password?</a>
                        </div>
                        
                        <button type="submit" class="submit-btn">Login</button>
                    </form>
                    
                    <p class="auth-switch" style="margin-bottom:8px;">Are you a property owner? <a href="{{ route('landlord.register') }}">Register as Landlord</a></p>
                    <p class="auth-switch">Looking to rent? <a href="{{ route('register') }}">Register as Tenant</a></p>
                </div>
            </div>
            
            <!-- Right side - CTA Landing -->
            <div class="illustration-section">
                <div class="properties-landing">
                    <div class="cta-hero">
                        <h2>Find your next home.</h2>
                        <p>Browse verified properties with real-time availability.</p>
                        <a href="{{ route('explore') }}" class="cta-primary">Explore listings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Login form is now handled by Laravel backend
    </script>
    
    <style>
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .alert ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>
    
    
</body>
</html> 