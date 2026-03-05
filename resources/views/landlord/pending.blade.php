<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approval - Housesync</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="status-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <h1>Account Pending Approval</h1>
                <p>Your landlord registration is being reviewed</p>
            </div>
            
            <div class="auth-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="status-message">
                    <h3>Thank you for registering!</h3>
                    <p>Your landlord account is currently under review by our admin team. This process typically takes 1-2 business days.</p>
                    
                    <div class="status-details">
                        <h4>What happens next?</h4>
                        <ul>
                            <li>Our team will review your business information</li>
                            <li>We may contact you for additional verification</li>
                            <li>You'll receive an email notification once approved</li>
                            <li>After approval, you can access your landlord dashboard</li>
                        </ul>
                    </div>
                    
                    <div class="contact-info">
                        <h4>Need help?</h4>
                        <p>If you have any questions, please contact our support team:</p>
                        <p><i class="fas fa-envelope"></i> support@housesync.com</p>
                        <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    </div>
                </div>
                
                <div class="auth-actions">
                    <a href="{{ route('logout') }}" class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        .status-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 32px;
        }
        
        .status-icon.pending {
            background-color: #fef3c7;
            color: #f59e0b;
        }
        
        .status-message {
            text-align: left;
            margin: 20px 0;
        }
        
        .status-message h3 {
            color: #1f2937;
            margin-bottom: 10px;
        }
        
        .status-details, .contact-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 8px;
        }
        
        .status-details h4, .contact-info h4 {
            color: #374151;
            margin-bottom: 10px;
        }
        
        .status-details ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .status-details li {
            margin: 5px 0;
            color: #6b7280;
        }
        
        .contact-info p {
            margin: 5px 0;
            color: #6b7280;
        }
        
        .auth-actions {
            margin-top: 30px;
            text-align: center;
        }
        
        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: #ef4444;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        
        .logout-btn:hover {
            background-color: #dc2626;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</body>
</html> 