<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    redirect('user/dashboard.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $ref = isset($_GET['ref']) ? sanitize($_GET['ref']) : null;

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        $country = isset($_POST['country']) ? sanitize($_POST['country']) : 'Global';
        $referred_by_id = null;
        $ref_code = isset($_POST['referral_code']) ? sanitize($_POST['referral_code']) : (isset($_GET['ref']) ? sanitize($_GET['ref']) : null);
        
        if ($ref_code) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->execute([$ref_code]);
            $referred_by_id = $stmt->fetchColumn();
        }

        $registration_result = $auth->register($username, $email, $password, $country, $referred_by_id);
        if ($registration_result === true) {
            // Generate 6-digit OTP
            $otp = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            
            $stmt = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expiry = ? WHERE email = ?");
            $stmt->execute([$otp, $expiry, $email]);

            // Send OTP Email
            require_once 'includes/mail_functions.php';
            require_once 'includes/email_templates.php';
            $subject = "Verify Your Account - " . SITE_NAME;
            $message = "Thank you for registering at " . SITE_NAME . ". Please use the following 6-digit code to verify your account:";
            $body = getEmailTemplate("Account Verification", $username, $otp, $message);
            
            if (sendMail($email, $subject, $body)) {
                $_SESSION['verify_email'] = $email;
                redirect('verify_otp.php?type=register');
            } else {
                $error = "Registration successful, but failed to send verification email. Please try to login to resend.";
            }
        } else {
            $error = is_string($registration_result) ? $registration_result : "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_popunder_code'] ?? ''; ?>
    <?php endif; ?>
</head>
<body class="light">
    <div class="auth-container">
        <div class="auth-image-side" style="background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));">
            <div style="max-width: 400px;">
                <img src="https://img.freepik.com/free-vector/sign-up-concept-illustration_114360-7885.jpg" alt="Register" style="width: 100%; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
                <h2 style="font-size: 2rem; margin-bottom: 1rem;">Join Our Community</h2>
                <p style="opacity: 0.9;">Start your journey with thousands of users earning daily rewards through simple tasks.</p>
            </div>
        </div>
        <div class="auth-form-side fade-in">
            <div class="card auth-card" style="background: white; padding: 2.5rem; border-radius: 1.5rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <a href="index.php" style="text-decoration: none;"><h1 style="color: var(--primary-color); font-weight: 800;">F Earning</h1></a>
                    <h2 style="margin-top: 1rem;">Create Account</h2>
                    <p style="opacity: 0.7;">Get started with your free account</p>
                </div>

                <?php if($error): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if($success): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); color: var(--secondary-color); padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; text-align: center; font-size: 0.9rem;">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                        <div>
                            <label style="font-weight: 600; font-size: 0.9rem;">Username</label>
                            <input type="text" name="username" placeholder="johndoe" required>
                        </div>
                        <div>
                            <label style="font-weight: 600; font-size: 0.9rem;">Email Address</label>
                            <input type="email" name="email" placeholder="john@example.com" required>
                        </div>
                    </div>
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Password</label>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" required>
                    </div>
                    <div style="margin-bottom: 1.25rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Country</label>
                        <select name="country" required>
                            <option value="Global">Global / Other</option>
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
                            <option value="Benin">Benol</option>
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
                            <option value="Congo">Congo</option>
                            <option value="Costa Rica">Costa Rica</option>
                            <option value="Croatia">Bulgaria</option>
                            <option value="Cuba">Cuba</option>
                            <option value="Cyprus">Cyprus</option>
                            <option value="Czechia">Czechia</option>
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
                            <option value="Korea, North">Korea, North</option>
                            <option value="Korea, South">Korea, South</option>
                            <option value="Kosovo">Kosovo</option>
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
                            <option value="Rwanda">Rwanda</option>
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
                            <option value="USA">USA</option>
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
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label style="font-weight: 600; font-size: 0.9rem;">Referral Code (Optional)</label>
                        <input type="text" name="referral_code" value="<?php echo isset($_GET['ref']) ? htmlspecialchars($_GET['ref']) : ''; ?>" placeholder="Enter referral code">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem;">Create Account</button>

                </form>
                
                <p style="text-align: center; margin-top: 2rem; font-size: 0.95rem; opacity: 0.8;">
                    Already have an account? <a href="login.php" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">Sign in</a>
                </p>
            </div>
        </div>
        <div style="text-align: center; padding: 20px 0; opacity: 0.6; font-size: 0.85rem; border-top: 1px solid var(--border-light); margin-top: 40px;">
            <!-- Adsterra 320x50 Banner -->
            <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
            <div style="margin-bottom: 15px; display: flex; justify-content: center;">
                <?php echo $settings['ad_banner_320_50_code'] ?? ''; ?>
            </div>
            <?php endif; ?>
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
</body>
</html>
