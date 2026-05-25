<?php
// Check registration deadline
$_registrationClosed = false;
$_submissionClosed   = false;
try {
    require_once __DIR__ . '/db.php';
    $dStmt = $pdo->query("SELECT setting_key, setting_value FROM competition_settings WHERE setting_key IN ('registration_deadline','submission_deadline')");
    foreach ($dStmt->fetchAll(PDO::FETCH_KEY_PAIR) as $k => $v) {
        if ($k === 'registration_deadline' && strtotime($v) < time()) $_registrationClosed = true;
        if ($k === 'submission_deadline'   && strtotime($v) < time()) $_submissionClosed   = true;
    }
} catch (Throwable $e) { /* table may not exist yet */ }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Greater ArtCompetition2025 - Registration</title>
  <meta name="description" content="Register for the Greater Art Competition 2025 - Photography, Paint, and Short Video categories available">
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }

    body, html {
      height: 100%;
      scroll-behavior: smooth;
    }

    .container {
      display: flex;
      min-height: 100vh;
    }

    .image-side {
      flex: 1;
      background-image: linear-gradient(rgba(0, 123, 255, 0.1), rgba(0, 123, 255, 0.1)), url('renewableEnergy.jpg');
      background-size: cover;
      background-position: center;
      position: relative;
    }

    .image-side::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(45deg, rgba(0, 123, 255, 0.05), rgba(0, 86, 179, 0.05));
      pointer-events: none;
    }

    .login-side {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa;
      padding: 20px;
      position: relative;
    }

    .login-form {
      background: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.08);
      width: 100%;
      max-width: 480px;
      text-align: center;
      position: relative;
      transition: box-shadow 0.3s ease;
    }

    .login-form:hover {
      box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    }

    .login-form img.logo {
      width: 150px;
      margin-bottom: 15px;
      transition: transform 0.3s ease;
    }

    .login-form img.logo:hover {
      transform: scale(1.02);
    }

    .login-form h2 {
      margin-bottom: 30px;
      color: #2c3e50;
      font-weight: 600;
      font-size: 24px;
    }

    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }

    .login-form label {
      display: block;
      margin: 0 0 8px;
      font-weight: 600;
      color: #495057;
      font-size: 14px;
    }

    .required {
      color: #dc3545;
      margin-left: 2px;
    }

    .input-row {
      display: flex;
      gap: 15px;
      justify-content: space-between;
    }

    .input-row > .form-group {
      flex: 1;
      margin-bottom: 0;
    }

    .input-wrapper {
      position: relative;
    }

    .login-form input[type="text"],
    .login-form input[type="email"],
    .login-form input[type="date"],
    .login-form input[type="tel"],
    .login-form select {
      padding: 12px 16px;
      border: 2px solid #e9ecef;
      border-radius: 8px;
      font-size: 14px;
      width: 100%;
      transition: all 0.3s ease;
      background-color: #fff;
      outline: none;
    }

    .login-form input:focus,
    .login-form select:focus {
      border-color: #007BFF;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .login-form input:valid {
      border-color: #28a745;
    }

    .login-form input:invalid:not(:placeholder-shown) {
      border-color: #dc3545;
    }

    .login-form input[type="date"] {
      font-size: 14px;
      padding: 12px 16px;
      line-height: 1.2;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background: white url('data:image/svg+xml;utf8,<svg fill="%23007BFF" height="16" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>') no-repeat right 16px center;
      background-size: 16px 16px;
      cursor: pointer;
    }

    .login-form select {
      font-size: 14px;
      padding: 12px 16px;
      appearance: none;
      -webkit-appearance: none;
      -moz-appearance: none;
      background: white url('data:image/svg+xml;utf8,<svg fill="%23007BFF" height="16" viewBox="0 0 24 24" width="16" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>') no-repeat right 16px center;
      background-size: 16px 16px;
      cursor: pointer;
    }

    .login-form select option[value=""] {
      color: #6c757d;
    }

    .error-message {
      color: #dc3545;
      font-size: 12px;
      margin-top: 4px;
      display: none;
    }

    .success-message {
      color: #28a745;
      font-size: 12px;
      margin-top: 4px;
      display: none;
    }

    .login-form button {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, #007BFF, #0056b3);
      border: none;
      color: white;
      font-size: 16px;
      font-weight: 600;
      border-radius: 8px;
      cursor: pointer;
      margin-top: 25px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .login-form button:hover {
      background: linear-gradient(135deg, #0056b3, #004085);
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(0, 123, 255, 0.3);
    }

    .login-form button:active {
      transform: translateY(0);
    }

    .login-form button:disabled {
      background: #6c757d;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .spinner {
      width: 20px;
      height: 20px;
      border: 2px solid #ffffff3d;
      border-top-color: #fff;
      border-radius: 50%;
      animation: spin 1s ease-in-out infinite;
      display: none;
      margin-right: 8px;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    .login-form p {
      margin-top: 20px;
      color: #6c757d;
      font-size: 14px;
    }

    .login-form p a {
      color: #007BFF;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.3s ease;
    }

    .login-form p a:hover {
      color: #0056b3;
      text-decoration: underline;
    }

    .progress-bar {
      width: 100%;
      height: 4px;
      background-color: #e9ecef;
      border-radius: 2px;
      margin-bottom: 20px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #007BFF, #0056b3);
      width: 0%;
      transition: width 0.3s ease;
      border-radius: 2px;
    }

    .form-step {
      opacity: 1;
      transition: opacity 0.3s ease;
    }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0, 0, 0, 0);
      white-space: nowrap;
      border: 0;
    }

    *:focus {
      outline: 2px solid #007BFF;
      outline-offset: 2px;
    }

    input:focus, select:focus, button:focus {
      outline: none;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        min-height: 100vh;
      }

      .image-side {
        display: none;
      }

      .login-side {
        flex: none;
        min-height: 100vh;
        background-color: #f8f9fa;
        padding: 20px 15px;
        display: flex;
        justify-content: center;
        align-items: center;
      }

      .login-form {
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        width: 100%;
        max-width: 400px;
        padding: 30px 25px;
        border-radius: 12px;
        text-align: center;
      }

      .login-form img.logo {
        display: block;
        margin: 0 auto 15px auto;
        width: 140px;
      }

      .input-row {
        flex-direction: column;
        gap: 0;
      }

      .input-row > .form-group {
        width: 100%;
        margin-bottom: 20px;
      }

      .login-form h2 {
        font-size: 22px;
        margin-bottom: 25px;
      }

      .login-form input,
      .login-form select {
        padding: 12px 14px;
        font-size: 16px;
      }
    }

    @media (max-width: 480px) {
      .login-form {
        padding: 25px 20px;
        margin: 10px;
      }

      .login-form h2 {
        font-size: 20px;
      }
    }

    @media print {
      .image-side {
        display: none;
      }
      
      .login-side {
        background: white;
      }
      
      .login-form {
        box-shadow: none;
        border: 1px solid #ccc;
      }
    }

    @media (prefers-contrast: high) {
      .login-form input,
      .login-form select {
        border-width: 3px;
      }
      
      .login-form button {
        border: 2px solid transparent;
      }
    }

    @media (prefers-reduced-motion: reduce) {
      * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="image-side" role="img" aria-label="Art competition background image"></div>
    <div class="login-side">
      <?php if ($_registrationClosed): ?>
      <div class="login-form" style="text-align:center; padding:40px 30px;">
        <img src="Greater_full_logo.png" alt="Greater ArtCompetition2025 Logo" class="logo" style="max-height:80px; margin-bottom:20px;" />
        <div style="background:#fee2e2; border-left:4px solid #ef4444; border-radius:10px; padding:24px; color:#b91c1c; margin-bottom:20px;">
          <div style="font-size:40px; margin-bottom:10px;">🔒</div>
          <h3 style="font-size:20px; margin-bottom:10px;">Registration Closed</h3>
          <p style="font-size:15px; line-height:1.6;">The registration deadline for this competition has passed.
            No new registrations are being accepted.</p>
        </div>
        <p style="color:#6b7280; font-size:14px; margin-top:16px;">
          Already registered? <a href="submit.php" style="color:#667eea; font-weight:600;">Submit your artwork here →</a>
        </p>
        <p style="color:#6b7280; font-size:14px; margin-top:10px;">
          Questions? Contact <a href="mailto:info@greaterproject.eu" style="color:#667eea;">info@greaterproject.eu</a>
        </p>
      </div>
      <?php else: ?>
      <form class="login-form" method="post" action="register.php" novalidate id="registrationForm">
        <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-fill" id="progressFill"></div>
        </div>
        
        <img src="Greater_full_logo.png" alt="Greater ArtCompetition2025 Logo" class="logo" />
        <img src="erasmusplus.png" alt="Erasmus Plus Logo" class="logo" />
        
        <h2>Art Competition Registration</h2>

        <input type="hidden" name="csrf_token" id="csrfToken">
        
        <div class="form-group">
          <label for="fullName">Full Name <span class="required" aria-label="required">*</span></label>
          <div class="input-wrapper">
            <input 
              type="text" 
              id="fullName" 
              name="fullName" 
              placeholder="Enter your full name" 
              required 
              aria-describedby="fullNameError"
              autocomplete="name"
              minlength="2"
              maxlength="100"
            />
            <div class="error-message" id="fullNameError" role="alert"></div>
            <div class="success-message" id="fullNameSuccess" role="status"></div>
          </div>
        </div>

        <div class="input-row">
          <div class="form-group">
            <label for="birthDate">Date of Birth <span class="required" aria-label="required">*</span></label>
            <div class="input-wrapper">
              <input 
                type="date" 
                id="birthDate" 
                name="birthDate" 
                required 
                aria-describedby="birthDateError"
                min="1900-01-01"
              />
              <div class="error-message" id="birthDateError" role="alert"></div>
            </div>
          </div>
          <div class="form-group">
            <label for="nationality">Nationality <span class="required" aria-label="required">*</span></label>
            <div class="input-wrapper">
              <select 
                id="nationality" 
                name="nationality" 
                required 
                aria-describedby="nationalityError"
              >
                <option value="" disabled selected>Select your nationality</option>
                <option value="Rwanda" selected>Rwanda</option>
                <option value="Afghanistan">Afghanistan</option>
                <option value="Albania">Albania</option>
                <option value="Algeria">Algeria</option>
                <option value="Andorra">Andorra</option>
                <option value="Angola">Angola</option>
                <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                <option value="Argentina">Argentina</option>
                <option value="Armenia">Armenia</option>
                <option value="Australia">Australia</option>
                <option value="Austria">Austria</option>
                <option value="Azerbaijan">Azerbaijan</option>
                <option value="Bahamas">Bahamas</option>
                <option value="Bahrain">Bahrain</option>
                <option value="Bangladesh">Bangladesh</option>
                <option value="Barbados">Barbados</option>
                <option value="Belarus">Belarus</option>
                <option value="Belgium">Belgium</option>
                <option value="Belize">Belize</option>
                <option value="Benin">Benin</option>
                <option value="Bhutan">Bhutan</option>
                <option value="Bolivia">Bolivia</option>
                <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                <option value="Botswana">Botswana</option>
                <option value="Brazil">Brazil</option>
                <option value="Brunei">Brunei</option>
                <option value="Bulgaria">Bulgaria</option>
                <option value="Burkina Faso">Burkina Faso</option>
                <option value="Burundi">Burundi</option>
                <option value="Cabo Verde">Cabo Verde</option>
                <option value="Cambodia">Cambodia</option>
                <option value="Cameroon">Cameroon</option>
                <option value="Canada">Canada</option>
                <option value="Central African Republic">Central African Republic</option>
                <option value="Chad">Chad</option>
                <option value="Chile">Chile</option>
                <option value="China">China</option>
                <option value="Colombia">Colombia</option>
                <option value="Comoros">Comoros</option>
                <option value="Congo (Congo-Brazzaville)">Congo (Congo-Brazzaville)</option>
                <option value="Congo (Congo-Kinshasa)">Congo (Congo-Kinshasa)</option>
                <option value="Costa Rica">Costa Rica</option>
                <option value="Croatia">Croatia</option>
                <option value="Cuba">Cuba</option>
                <option value="Cyprus">Cyprus</option>
                <option value="Czech Republic">Czech Republic</option>
                <option value="Denmark">Denmark</option>
                <option value="Djibouti">Djibouti</option>
                <option value="Dominica">Dominica</option>
                <option value="Dominican Republic">Dominican Republic</option>
                <option value="Ecuador">Ecuador</option>
                <option value="Egypt">Egypt</option>
                <option value="El Salvador">El Salvador</option>
                <option value="Equatorial Guinea">Equatorial Guinea</option>
                <option value="Eritrea">Eritrea</option>
                <option value="Estonia">Estonia</option>
                <option value="Eswatini">Eswatini</option>
                <option value="Ethiopia">Ethiopia</option>
                <option value="Fiji">Fiji</option>
                <option value="Finland">Finland</option>
                <option value="France">France</option>
                <option value="Gabon">Gabon</option>
                <option value="Gambia">Gambia</option>
                <option value="Georgia">Georgia</option>
                <option value="Germany">Germany</option>
                <option value="Ghana">Ghana</option>
                <option value="Greece">Greece</option>
                <option value="Grenada">Grenada</option>
                <option value="Guatemala">Guatemala</option>
                <option value="Guinea">Guinea</option>
                <option value="Guinea-Bissau">Guinea-Bissau</option>
                <option value="Guyana">Guyana</option>
                <option value="Haiti">Haiti</option>
                <option value="Honduras">Honduras</option>
                <option value="Hungary">Hungary</option>
                <option value="Iceland">Iceland</option>
                <option value="India">India</option>
                <option value="Indonesia">Indonesia</option>
                <option value="Iran">Iran</option>
                <option value="Iraq">Iraq</option>
                <option value="Ireland">Ireland</option>
                <option value="Israel">Israel</option>
                <option value="Italy">Italy</option>
                <option value="Jamaica">Jamaica</option>
                <option value="Japan">Japan</option>
                <option value="Jordan">Jordan</option>
                <option value="Kazakhstan">Kazakhstan</option>
                <option value="Kenya">Kenya</option>
                <option value="Kiribati">Kiribati</option>
                <option value="Kuwait">Kuwait</option>
                <option value="Kyrgyzstan">Kyrgyzstan</option>
                <option value="Laos">Laos</option>
                <option value="Latvia">Latvia</option>
                <option value="Lebanon">Lebanon</option>
                <option value="Lesotho">Lesotho</option>
                <option value="Liberia">Liberia</option>
                <option value="Libya">Libya</option>
                <option value="Liechtenstein">Liechtenstein</option>
                <option value="Lithuania">Lithuania</option>
                <option value="Luxembourg">Luxembourg</option>
                <option value="Madagascar">Madagascar</option>
                <option value="Malawi">Malawi</option>
                <option value="Malaysia">Malaysia</option>
                <option value="Maldives">Maldives</option>
                <option value="Mali">Mali</option>
                <option value="Malta">Malta</option>
                <option value="Marshall Islands">Marshall Islands</option>
                <option value="Mauritania">Mauritania</option>
                <option value="Mauritius">Mauritius</option>
                <option value="Mexico">Mexico</option>
                <option value="Micronesia">Micronesia</option>
                <option value="Moldova">Moldova</option>
                <option value="Monaco">Monaco</option>
                <option value="Mongolia">Mongolia</option>
                <option value="Montenegro">Montenegro</option>
                <option value="Morocco">Morocco</option>
                <option value="Mozambique">Mozambique</option>
                <option value="Myanmar">Myanmar</option>
                <option value="Namibia">Namibia</option>
                <option value="Nauru">Nauru</option>
                <option value="Nepal">Nepal</option>
                <option value="Netherlands">Netherlands</option>
                <option value="New Zealand">New Zealand</option>
                <option value="Nicaragua">Nicaragua</option>
                <option value="Niger">Niger</option>
                <option value="Nigeria">Nigeria</option>
                <option value="North Korea">North Korea</option>
                <option value="North Macedonia">North Macedonia</option>
                <option value="Norway">Norway</option>
                <option value="Oman">Oman</option>
                <option value="Pakistan">Pakistan</option>
                <option value="Palau">Palau</option>
                <option value="Palestine">Palestine</option>
                <option value="Panama">Panama</option>
                <option value="Papua New Guinea">Papua New Guinea</option>
                <option value="Paraguay">Paraguay</option>
                <option value="Peru">Peru</option>
                <option value="Philippines">Philippines</option>
                <option value="Poland">Poland</option>
                <option value="Portugal">Portugal</option>
                <option value="Qatar">Qatar</option>
                <option value="Romania">Romania</option>
                <option value="Russia">Russia</option>
                <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                <option value="Saint Lucia">Saint Lucia</option>
                <option value="Saint Vincent and the Grenadines">Saint Vincent and the Grenadines</option>
                <option value="Samoa">Samoa</option>
                <option value="San Marino">San Marino</option>
                <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                <option value="Saudi Arabia">Saudi Arabia</option>
                <option value="Senegal">Senegal</option>
                <option value="Serbia">Serbia</option>
                <option value="Seychelles">Seychelles</option>
                <option value="Sierra Leone">Sierra Leone</option>
                <option value="Singapore">Singapore</option>
                <option value="Slovakia">Slovakia</option>
                <option value="Slovenia">Slovenia</option>
                <option value="Solomon Islands">Solomon Islands</option>
                <option value="Somalia">Somalia</option>
                <option value="South Africa">South Africa</option>
                <option value="South Korea">South Korea</option>
                <option value="South Sudan">South Sudan</option>
                <option value="Spain">Spain</option>
                <option value="Sri Lanka">Sri Lanka</option>
                <option value="Sudan">Sudan</option>
                <option value="Suriname">Suriname</option>
                <option value="Sweden">Sweden</option>
                <option value="Switzerland">Switzerland</option>
                <option value="Syria">Syria</option>
                <option value="Taiwan">Taiwan</option>
                <option value="Tajikistan">Tajikistan</option>
                <option value="Tanzania">Tanzania</option>
                <option value="Thailand">Thailand</option>
                <option value="Timor-Leste">Timor-Leste</option>
                <option value="Togo">Togo</option>
                <option value="Tonga">Tonga</option>
                <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                <option value="Tunisia">Tunisia</option>
                <option value="Turkey">Turkey</option>
                <option value="Turkmenistan">Turkmenistan</option>
                <option value="Tuvalu">Tuvalu</option>
                <option value="Uganda">Uganda</option>
                <option value="Ukraine">Ukraine</option>
                <option value="United Arab Emirates">United Arab Emirates</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="United States">United States</option>
                <option value="Uruguay">Uruguay</option>
                <option value="Uzbekistan">Uzbekistan</option>
                <option value="Vanuatu">Vanuatu</option>
                <option value="Vatican City">Vatican City</option>
                <option value="Venezuela">Venezuela</option>
                <option value="Vietnam">Vietnam</option>
                <option value="Yemen">Yemen</option>
                <option value="Zambia">Zambia</option>
                <option value="Zimbabwe">Zimbabwe</option>
              </select>
              <div class="error-message" id="nationalityError" role="alert"></div>
            </div>
          </div>
        </div>

        <div class="form-group">
          <label for="idNumber">ID / Passport Number <span class="required" aria-label="required">*</span></label>
          <div class="input-wrapper">
            <input 
              type="text" 
              id="idNumber" 
              name="idNumber" 
              placeholder="Your ID or Passport number" 
              required 
              aria-describedby="idNumberError"
              minlength="5"
              maxlength="20"
            />
            <div class="error-message" id="idNumberError" role="alert"></div>
          </div>
        </div>

        <div class="form-group">
          <label for="email">Email Address <span class="required" aria-label="required">*</span></label>
          <div class="input-wrapper">
            <input 
              type="email" 
              id="email" 
              name="email" 
              placeholder="example@mail.com" 
              required 
              aria-describedby="emailError"
              autocomplete="email"
            />
            <div class="error-message" id="emailError" role="alert"></div>
          </div>
        </div>

        <div class="form-group">
          <label for="phone">Phone Number <span class="required" aria-label="required">*</span></label>
          <div class="input-wrapper">
            <input 
              type="tel" 
              id="phone" 
              name="phone" 
              placeholder="+250 712345678" 
              required 
              aria-describedby="phoneError"
              autocomplete="tel"
              pattern="[\+]?[0-9\s\-\(\)]{10,15}"
            />
            <div class="error-message" id="phoneError" role="alert"></div>
          </div>
        </div>

        <div class="form-group">
          <label for="category">Competition Category <span class="required" aria-label="required">*</span></label>
          <div class="input-wrapper">
            <select id="category" name="category" required aria-describedby="categoryError">
              <option value="" disabled selected>Select a category</option>
              <option value="photography_paint">Photography & Paint</option>
              <option value="short_video">Short Video</option>
            </select>
            <div class="error-message" id="categoryError" role="alert"></div>
          </div>
        </div>

        <button type="submit" id="submitBtn" aria-describedby="submitStatus">
          <span class="spinner" id="spinner"></span>
          <span id="submitText">Register Now</span>
        </button>
        
        <div id="submitStatus" class="sr-only" role="status" aria-live="polite"></div>
        
        <p>
          Already registered? <a href="submit.php" aria-label="Go to submission page">Submit Your Work!</a>
        </p>
      </form>
      <?php endif; // end registration open check ?>
    </div>
  </div>

  <script>
    (function() {
      'use strict';
      
      const form = document.getElementById('registrationForm');
      const submitBtn = document.getElementById('submitBtn');
      const submitText = document.getElementById('submitText');
      const spinner = document.getElementById('spinner');
      const progressFill = document.getElementById('progressFill');
      const submitStatus = document.getElementById('submitStatus');
      
      // Get CSRF token from server
      fetchCSRFToken();
      
      // Form validation rules
      const validators = {
        fullName: {
          validate: (value) => {
            if (!value.trim()) return 'Full name is required';
            if (value.trim().length < 2) return 'Name must be at least 2 characters';
            if (!/^[a-zA-Z\s\-\.\']+$/.test(value)) return 'Name contains invalid characters';
            return null;
          }
        },
        birthDate: {
          validate: (value) => {
            if (!value) return 'Date of birth is required';
            const date = new Date(value);
            const today = new Date();
            const age = today.getFullYear() - date.getFullYear();
            if (age < 13) return 'Must be at least 13 years old';
            if (age > 120) return 'Please enter a valid birth date';
            return null;
          }
        },
        nationality: {
          validate: (value) => {
            if (!value.trim()) return 'Nationality is required';
            return null;
          }
        },
        idNumber: {
          validate: (value) => {
            if (!value.trim()) return 'ID/Passport number is required';
            if (value.trim().length < 5) return 'ID number must be at least 5 characters';
            return null;
          }
        },
        email: {
          validate: (value) => {
            if (!value.trim()) return 'Email is required';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) return 'Please enter a valid email address';
            return null;
          }
        },
        phone: {
          validate: (value) => {
            if (!value.trim()) return 'Phone number is required';
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,15}$/;
            if (!phoneRegex.test(value.replace(/\s/g, ''))) return 'Please enter a valid phone number';
            return null;
          }
        },
        category: {
          validate: (value) => {
            if (!value) return 'Please select a competition category';
            return null;
          }
        }
      };
      
      // Real-time validation
      Object.keys(validators).forEach(fieldName => {
        const field = document.getElementById(fieldName);
        const errorEl = document.getElementById(fieldName + 'Error');
        const successEl = document.getElementById(fieldName + 'Success');
        
        if (field && errorEl) {
          field.addEventListener('blur', () => validateField(fieldName));
          field.addEventListener('input', debounce(() => validateField(fieldName), 300));
        }
      });
      
      function validateField(fieldName) {
        const field = document.getElementById(fieldName);
        const errorEl = document.getElementById(fieldName + 'Error');
        const successEl = document.getElementById(fieldName + 'Success');
        const validator = validators[fieldName];
        
        if (!field || !validator) return true;
        
        const error = validator.validate(field.value);
        
        if (error) {
          showError(errorEl, error);
          hideSuccess(successEl);
          field.setAttribute('aria-invalid', 'true');
          return false;
        } else {
          hideError(errorEl);
          if (field.value.trim() && successEl) {
            showSuccess(successEl, 'Valid');
          }
          field.setAttribute('aria-invalid', 'false');
          return true;
        }
      }
      
      function showError(errorEl, message) {
        if (errorEl) {
          errorEl.textContent = message;
          errorEl.style.display = 'block';
        }
      }
      
      function hideError(errorEl) {
        if (errorEl) {
          errorEl.style.display = 'none';
        }
      }
      
      function showSuccess(successEl, message) {
        if (successEl) {
          successEl.textContent = message;
          successEl.style.display = 'block';
        }
      }
      
      function hideSuccess(successEl) {
        if (successEl) {
          successEl.style.display = 'none';
        }
      }
      
      // Progress bar update
      function updateProgress() {
        const fields = Object.keys(validators);
        const validFields = fields.filter(fieldName => {
          const field = document.getElementById(fieldName);
          return field && field.value.trim() && validateField(fieldName);
        });
        
        const progress = (validFields.length / fields.length) * 100;
        progressFill.style.width = progress + '%';
        progressFill.parentElement.setAttribute('aria-valuenow', Math.round(progress));
      }
      
      // Update progress on input
      Object.keys(validators).forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
          field.addEventListener('input', debounce(updateProgress, 100));
          field.addEventListener('change', updateProgress);
        }
      });
      
      // Form submission - FIXED VERSION
      form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate all fields
        let isValid = true;
        Object.keys(validators).forEach(fieldName => {
          if (!validateField(fieldName)) {
            isValid = false;
          }
        });
        
        if (!isValid) {
          announceToScreenReader('Please correct the errors in the form');
          return;
        }
        
        // Show loading state
        setSubmitLoading(true);
        
        try {
          const formData = new FormData(form);
          
          const response = await fetch('register.php', {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          });
          
          const responseText = await response.text();
          console.log('Raw response:', responseText);
          
          let result;
          try {
            result = JSON.parse(responseText);
          } catch (jsonError) {
            console.error('JSON parse error:', jsonError);
            throw new Error('Server returned invalid response: ' + responseText.substring(0, 200));
          }
          
          if (result.success) {
            announceToScreenReader('Registration submitted successfully');
            showSuccessMessage(result);
            form.dataset.submitted = 'true';
          } else {
            // Handle errors properly
            let errorMessage = result.message || 'Registration failed';
            
            // If there are specific errors, show them
            if (result.errors && Array.isArray(result.errors) && result.errors.length > 0) {
              errorMessage = result.errors.join('\n• ');
            }
            
            throw new Error(errorMessage);
          }
          
        } catch (error) {
          console.error('Form submission error:', error);
          announceToScreenReader('Registration failed. Please try again.');
          showErrorMessage(error.message || 'Registration failed. Please try again.');
        } finally {
          setSubmitLoading(false);
        }
      });
      
      function setSubmitLoading(loading) {
        submitBtn.disabled = loading;
        if (loading) {
          spinner.style.display = 'inline-block';
          submitText.textContent = 'Registering...';
        } else {
          spinner.style.display = 'none';
          submitText.textContent = 'Register Now';
        }
      }
      
      function showSuccessMessage(result) {
        const successDiv = document.createElement('div');
        successDiv.innerHTML = `
          <div style="background: #d4edda; color: #155724; padding: 20px; border-radius: 8px; margin-top: 20px; border: 1px solid #c3e6cb; line-height: 1.5;">
            <h3 style="margin: 0 0 10px 0; color: #155724;">🎉 Registration Successful!</h3>
            <p style="margin: 0 0 10px 0;"><strong>Your User Code:</strong> <code style="background: #c3e6cb; padding: 2px 6px; border-radius: 4px; font-weight: bold;">${result.userCode || 'GAC' + Math.floor(Math.random() * 10000)}</code></p>
            <p style="margin: 0 0 10px 0;">📧 Check your email for detailed instructions and keep your user code safe!</p>
            <p style="margin: 0; font-size: 14px; opacity: 0.8;">You'll need this code to submit your artwork and check your status.</p>
          </div>
        `;
        form.appendChild(successDiv);
        
        // Disable form after successful submission
        const inputs = form.querySelectorAll('input, select, button');
        inputs.forEach(input => input.disabled = true);
        
        setTimeout(() => {
          successDiv.remove();
        }, 15000);
      }
      
      function showErrorMessage(message) {
        const errorDiv = document.createElement('div');
        
        // Format multiple errors nicely
        const formattedMessage = message.includes('\n') 
          ? message.split('\n').map(err => err.trim()).filter(err => err).map(err => `• ${err}`).join('<br>')
          : message;
        
        errorDiv.innerHTML = `
          <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-top: 20px; border: 1px solid #f5c6cb; line-height: 1.4;">
            <strong>Error:</strong><br>
            ${formattedMessage}
          </div>
        `;
        form.appendChild(errorDiv);
        
        // Scroll to error message
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        setTimeout(() => {
          errorDiv.remove();
        }, 10000);
      }
      
      function announceToScreenReader(message) {
        submitStatus.textContent = message;
        setTimeout(() => {
          submitStatus.textContent = '';
        }, 5000);
      }
      
      function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
          const later = () => {
            clearTimeout(timeout);
            func(...args);
          };
          clearTimeout(timeout);
          timeout = setTimeout(later, wait);
        };
      }
      
      async function fetchCSRFToken() {
        try {
          const response = await fetch('register.php?csrf_token=1');
          if (response.ok) {
            const data = await response.json();
            document.getElementById('csrfToken').value = data.csrf_token;
          } else {
            document.getElementById('csrfToken').value = generateCSRFToken();
          }
        } catch (error) {
          document.getElementById('csrfToken').value = generateCSRFToken();
        }
      }
      
      function generateCSRFToken() {
        return Array.from(crypto.getRandomValues(new Uint8Array(32)))
          .map(b => b.toString(16).padStart(2, '0'))
          .join('');
      }
      
      // Auto-save form data
      const autoSaveFields = ['fullName', 'email', 'phone'];
      autoSaveFields.forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field) {
          const saved = sessionStorage.getItem('form_' + fieldName);
          if (saved) {
            field.value = saved;
          }
          
          field.addEventListener('input', debounce(() => {
            sessionStorage.setItem('form_' + fieldName, field.value);
          }, 500));
        }
      });
      
      // Clear saved data on successful submission
      window.addEventListener('beforeunload', () => {
        if (form.dataset.submitted === 'true') {
          autoSaveFields.forEach(fieldName => {
            sessionStorage.removeItem('form_' + fieldName);
          });
        }
      });
      
      // Keyboard navigation improvements
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'TEXTAREA') {
          const formElements = Array.from(form.elements);
          const currentIndex = formElements.indexOf(e.target);
          const nextElement = formElements[currentIndex + 1];
          
          if (nextElement && nextElement.type !== 'submit') {
            e.preventDefault();
            nextElement.focus();
          }
        }
      });
      
      // Initialize progress bar
      updateProgress();
      
    })();
  </script>
</body>
</html>