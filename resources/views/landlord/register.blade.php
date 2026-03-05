<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Landlord Registration - Housesync</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="landlord-registration-wrapper">
            <!-- Left side - Branding and Info -->
            <div class="info-section">
                <div class="info-container">
                    <div class="brand">
                        <div class="brand-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <h2>HouseSync</h2>
                    </div>
                    
                    <div class="info-content">
                        <h1>Join as a Property Manager</h1>
                        <p>Manage your properties efficiently with our comprehensive landlord platform</p>
                        
                        <div class="features">
                            <div class="feature">
                                <i class="fas fa-chart-line"></i>
                                <div>
                                    <h3>Property Analytics</h3>
                                    <p>Track occupancy rates and rental income</p>
                                </div>
                            </div>
                            <div class="feature">
                                <i class="fas fa-users"></i>
                                <div>
                                    <h3>Tenant Management</h3>
                                    <p>Manage tenant applications and communications</p>
                                </div>
                            </div>
                            <div class="feature">
                                <i class="fas fa-tools"></i>
                                <div>
                                    <h3>Maintenance Tracking</h3>
                                    <p>Handle maintenance requests efficiently</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right side - Registration Form -->
            <div class="form-section">
                <div class="form-container">
                    <div class="form-header">
                        <h2>Create Your Account</h2>
                        <p>Fill in your details to get started</p>
                    </div>
                    
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="landlord-form" method="POST" action="{{ route('landlord.register.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="form-section-title">
                            <h3><i class="fas fa-user"></i> Personal Information</h3>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Enter your full name">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="your@email.com">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="+1 (555) 123-4567">
                            </div>
                            <div class="form-group">
                                <label for="address">Address *</label>
                                <input type="text" id="address" name="address" value="{{ old('address') }}" required placeholder="Your business address">
                            </div>
                        </div>
                        
                        <div class="form-section-title">
                            <h3><i class="fas fa-briefcase"></i> Business Information</h3>
                        </div>
                        
                        <div class="form-group">
                            <label for="business_info">Business Details *</label>
                            <textarea id="business_info" name="business_info" rows="4" required placeholder="Tell us about your property management experience, company details, number of properties you manage, etc.">{{ old('business_info') }}</textarea>
                        </div>
                        
                        <div class="form-section-title">
                            <h3><i class="fas fa-lock"></i> Account Security</h3>
                        </div>
                        <div class="form-section-title">
                            <h3><i class="fas fa-file-upload"></i> Business Documents (Required)</h3>
                        </div>

                        <div class="form-group">
                            <label>Upload Required Documents</label>
                            <p style="font-size: 12px; color: #7f8c8d; margin-bottom: 8px;">Accepted: JPG, JPEG, PNG, PDF. Max 5MB each.</p>
                            <div id="doc-list">
                                <div class="doc-row" style="display:flex; gap:12px; margin-bottom:10px;">
                                    <select name="document_types[]" required style="flex:0 0 260px; padding: 10px; border: 2px solid #e1e8ed; border-radius: 10px; background:#f8f9fa;">
                                        <option value="">Select document type</option>
                                        <option value="business_permit">Business Permit</option>
                                        <option value="mayors_permit">Mayor's Permit</option>
                                        <option value="bir_certificate">BIR Certificate</option>
                                        <option value="barangay_clearance">Barangay Clearance</option>
                                        <option value="lease_contract_sample">Sample Lease Contract</option>
                                        <option value="valid_id">Valid ID</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <input type="file" name="documents[]" required accept=".pdf,.jpg,.jpeg,.png" style="flex:1; padding:10px; border:2px solid #e1e8ed; border-radius:10px; background:#f8f9fa;" />
                                </div>
                            </div>
                            <button type="button" onclick="addDocRow()" class="submit-btn" style="width:auto; margin-top:8px; padding:10px 14px;">
                                <i class="fas fa-plus"></i> Add another document
                            </button>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="password">Password *</label>
                                <div class="password-input">
                                    <input type="password" id="password" name="password" required placeholder="Create a strong password">
                                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password *</label>
                                <div class="password-input">
                                    <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Confirm your password">
                                    <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-options">
                            <div class="terms-agreement">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms">I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a></label>
                            </div>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-user-plus"></i>
                            Create Landlord Account
                        </button>
                    </form>
                    
                    <div class="auth-footer">
                        <p>Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .landlord-registration-wrapper {
            display: flex;
            width: 100%;
            max-width: 1400px;
            min-height: 800px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        /* Info Section */
        .info-section {
            flex: 1;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            padding: 60px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .info-container {
            max-width: 400px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
        }

        .brand-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .brand h2 {
            font-size: 28px;
            font-weight: 700;
        }

        .info-content h1 {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 16px;
            line-height: 1.2;
        }

        .info-content > p {
            font-size: 18px;
            opacity: 0.9;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .features {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .feature {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .feature i {
            font-size: 24px;
            margin-top: 4px;
            opacity: 0.9;
        }

        .feature h3 {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .feature p {
            font-size: 14px;
            opacity: 0.8;
            line-height: 1.5;
        }

        /* Form Section */
        .form-section {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
        }

        .form-container {
            width: 100%;
            max-width: 500px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .form-header h2 {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-header p {
            font-size: 16px;
            color: #7f8c8d;
        }

        /* Alert Styling */
        .alert {
            padding: 16px;
            margin-bottom: 24px;
            border-radius: 12px;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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

        .alert li {
            margin-bottom: 4px;
        }

        /* Form Styling */
        .landlord-form {
            margin-bottom: 30px;
        }

        .form-section-title {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f1f3f4;
        }

        .form-section-title h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-section-title i {
            color: #3498db;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            color: #2c3e50;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            background: white;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #95a5a6;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Password Input */
        .password-input {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #7f8c8d;
            cursor: pointer;
            font-size: 16px;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #3498db;
        }

        /* Form Options */
        .form-options {
            margin-bottom: 30px;
        }

        .terms-agreement {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .terms-agreement input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-top: 2px;
            accent-color: #3498db;
        }

        .terms-agreement label {
            font-size: 14px;
            color: #7f8c8d;
            line-height: 1.5;
            margin-bottom: 0;
            cursor: pointer;
        }

        .terms-agreement a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        .terms-agreement a:hover {
            text-decoration: underline;
        }

        /* Submit Button */
        .submit-btn {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        /* Auth Footer */
        .auth-footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e1e8ed;
        }

        .auth-footer p {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 8px;
        }

        .auth-footer a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .landlord-registration-wrapper {
                flex-direction: column;
                min-height: auto;
            }

            .info-section {
                padding: 40px 20px;
            }

            .form-section {
                padding: 40px 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .info-content h1 {
                font-size: 28px;
            }

            .form-header h2 {
                font-size: 24px;
            }
        }
    </style>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                toggle.classList.remove('fa-eye');
                toggle.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                toggle.classList.remove('fa-eye-slash');
                toggle.classList.add('fa-eye');
            }
        }

        function addDocRow() {
            const list = document.getElementById('doc-list');
            const row = document.createElement('div');
            row.className = 'doc-row';
            row.style.cssText = 'display:flex; gap:12px; margin-bottom:10px;';
            row.innerHTML = `
                <select name="document_types[]" required style="flex:0 0 260px; padding: 10px; border: 2px solid #e1e8ed; border-radius: 10px; background:#f8f9fa;">
                    <option value="">Select document type</option>
                    <option value="business_permit">Business Permit</option>
                    <option value="mayors_permit">Mayor's Permit</option>
                    <option value="bir_certificate">BIR Certificate</option>
                    <option value="barangay_clearance">Barangay Clearance</option>
                    <option value="lease_contract_sample">Sample Lease Contract</option>
                    <option value="valid_id">Valid ID</option>
                    <option value="other">Other</option>
                </select>
                <input type="file" name="documents[]" required accept=".pdf,.jpg,.jpeg,.png" style="flex:1; padding:10px; border:2px solid #e1e8ed; border-radius:10px; background:#f8f9fa;" />
            `;
            list.appendChild(row);
        }
    </script>
</body>
</html> 