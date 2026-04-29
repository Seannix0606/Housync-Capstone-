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

                    @if(session('error'))
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            An unexpected error occurred. Please try again.
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
                                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required placeholder="09171234567 (digits and separators OK)">
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
                            <h3><i class="fas fa-file-upload"></i> Required legal documents</h3>
                        </div>

                        <p class="doc-intro">Upload each document below. Accepted: JPG, JPEG, PNG, PDF. Max 5MB per file.</p>

                        <div class="requirements-table-wrap">
                            <table class="requirements-table" aria-label="Where to obtain each document">
                                <thead>
                                    <tr><th>Requirement</th><th>Where to get it</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Barangay Clearance</td><td>Barangay Hall</td></tr>
                                    <tr><td>Mayor's Permit</td><td>City / Municipal Hall</td></tr>
                                    <tr><td>Fire Safety Certificate</td><td>Bureau of Fire Protection</td></tr>
                                    <tr><td>Tax Registration</td><td>Bureau of Internal Revenue</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="form-group doc-upload-block">
                            <label for="doc_barangay_clearance">Barangay Clearance *</label>
                            <input type="file" id="doc_barangay_clearance" name="doc_barangay_clearance" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="form-group doc-upload-block">
                            <label for="doc_mayors_permit">Mayor's Permit *</label>
                            <input type="file" id="doc_mayors_permit" name="doc_mayors_permit" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="form-group doc-upload-block">
                            <label for="doc_fire_safety_certificate">Fire Safety Certificate *</label>
                            <input type="file" id="doc_fire_safety_certificate" name="doc_fire_safety_certificate" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="form-group doc-upload-block">
                            <label for="doc_tax_registration">Tax Registration (BIR) *</label>
                            <input type="file" id="doc_tax_registration" name="doc_tax_registration" required accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <div class="form-group co-option-block">
                            <input type="hidden" name="property_is_newly_built" id="property_is_newly_built" value="0">
                            <button type="button" id="toggle_newly_built_btn" class="co-toggle-btn" aria-pressed="false">
                                <i class="fas fa-plus-circle"></i>
                                <span id="toggle_newly_built_label">My property is newly built — add Certificate of Occupancy</span>
                            </button>
                            <p class="co-hint">Only turn this on if you need to submit a Certificate of Occupancy for a new building.</p>
                            <div id="co_upload_wrap" class="co-upload-wrap" style="display: none;">
                                <label for="doc_certificate_of_occupancy">Certificate of Occupancy *</label>
                                <input type="file" id="doc_certificate_of_occupancy" name="doc_certificate_of_occupancy" accept=".pdf,.jpg,.jpeg,.png">
                            </div>
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

        .doc-intro {
            font-size: 13px;
            color: #7f8c8d;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .requirements-table-wrap {
            margin-bottom: 20px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid #e1e8ed;
        }

        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .requirements-table th,
        .requirements-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }

        .requirements-table th {
            background: #f1f5f9;
            color: #2c3e50;
            font-weight: 600;
        }

        .requirements-table tr:last-child td {
            border-bottom: none;
        }

        .doc-upload-block input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            background: #f8f9fa;
            font-size: 14px;
        }

        .co-option-block {
            margin-top: 8px;
            padding-top: 16px;
            border-top: 1px dashed #e1e8ed;
        }

        .co-toggle-btn {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #3498db;
            border-radius: 12px;
            background: #fff;
            color: #2980b9;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
            font-family: 'Inter', sans-serif;
        }

        .co-toggle-btn:hover {
            background: #ebf5fb;
        }

        .co-toggle-btn.active {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: #fff;
            border-color: #2980b9;
        }

        .co-hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 8px;
            margin-bottom: 0;
            line-height: 1.4;
        }

        .co-upload-wrap {
            margin-top: 16px;
        }

        .co-upload-wrap input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            background: #f8f9fa;
            font-size: 14px;
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

        (function () {
            const hidden = document.getElementById('property_is_newly_built');
            const btn = document.getElementById('toggle_newly_built_btn');
            const label = document.getElementById('toggle_newly_built_label');
            const wrap = document.getElementById('co_upload_wrap');
            const fileInput = document.getElementById('doc_certificate_of_occupancy');

            btn.addEventListener('click', function () {
                const on = hidden.value === '1';
                if (on) {
                    hidden.value = '0';
                    btn.classList.remove('active');
                    btn.setAttribute('aria-pressed', 'false');
                    label.textContent = 'My property is newly built — add Certificate of Occupancy';
                    wrap.style.display = 'none';
                    fileInput.value = '';
                    fileInput.removeAttribute('required');
                } else {
                    hidden.value = '1';
                    btn.classList.add('active');
                    btn.setAttribute('aria-pressed', 'true');
                    label.textContent = 'Remove Certificate of Occupancy (not newly built)';
                    wrap.style.display = 'block';
                    fileInput.setAttribute('required', 'required');
                }
            });
        })();
    </script>
</body>
</html> 