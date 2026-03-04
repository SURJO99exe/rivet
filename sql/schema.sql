CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    country VARCHAR(100) DEFAULT 'Global',
    password VARCHAR(255) NOT NULL,
    balance DECIMAL(15, 4) DEFAULT 0.0000,
    total_earned DECIMAL(15, 4) DEFAULT 0.0000,
    referral_code VARCHAR(20) UNIQUE,
    referred_by INT DEFAULT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_verified TINYINT(1) DEFAULT 0,
    status ENUM('active', 'blocked') DEFAULT 'active',
    membership_id INT DEFAULT 1,
    membership_expires_at TIMESTAMP NULL,
    verification_token VARCHAR(100),
    otp_code VARCHAR(10),
    otp_expiry DATETIME,
    reset_token VARCHAR(100),
    reset_token_expiry DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    daily_ads INT NOT NULL,
    daily_surveys INT NOT NULL DEFAULT 5,
    ad_reward DECIMAL(10, 4) NOT NULL,
    duration_days INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    video_url VARCHAR(255) NOT NULL,
    thumbnail VARCHAR(255) DEFAULT NULL,
    duration INT NOT NULL,
    reward DECIMAL(10, 4) NOT NULL,
    daily_limit INT DEFAULT 1,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ad_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ad_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reward_earned DECIMAL(10, 4) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ad_id) REFERENCES ads(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS withdrawals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 4) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_details TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS referral_commissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    referred_user_id INT NOT NULL,
    amount DECIMAL(10, 4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    reward DECIMAL(10, 4) NOT NULL,
    questions_json TEXT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS survey_completions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    survey_id INT NOT NULL,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reward_earned DECIMAL(10, 4) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    amount DECIMAL(15, 4) NOT NULL,
    type ENUM('earning', 'withdrawal', 'upgrade', 'referral') NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('site_name', 'F Earning Platform'),
('min_withdrawal', '10.00'),
('referral_commission', '10'),
('daily_ad_limit', '10'),
('site_logo', 'logo_1772631902_Untitled design.jpg'),
('site_favicon', 'fav_1772631902_Untitled design.jpg'),
('contact_email', 'support@f-earning.com'),
('smtp_host', 'smtp.gmail.com'),
('smtp_user', 'abroad.globalsell.site@gmail.com'),
('smtp_pass', 'uoui pfvi qxdj jgiw'),
('smtp_port', '587'),
('payment_methods_bangladesh', 'Visa,MasterCard,PayPal,Binance (USDT)'),
('payment_methods_usa', 'PayPal,Visa,MasterCard,CashApp,Zelle,Venmo'),
('payment_methods_india', 'PhonePe,Paytm,Google Pay,UPI,Bank Transfer'),
('payment_methods_pakistan', 'JazzCash,EasyPaisa,Bank Transfer'),
('payment_methods_nigeria', 'Opay,Palmpay,Flutterwave,Bank Transfer'),
('payment_methods_philippines', 'GCash,Maya,Coins.ph,Bank Transfer'),
('payment_methods_united kingdom', 'Bank Transfer,PayPal,Revolut'),
('payment_methods_canada', 'Interac e-Transfer,PayPal,Bank Transfer'),
('payment_methods_brazil', 'Pix,Boleto,PayPal'),
('payment_methods_indonesia', 'GoPay,OVO,DANA,Bank Transfer'),
('payment_methods_vietnam', 'Momo,ZaloPay,Bank Transfer'),
('payment_methods_russia', 'Payeer,AdvCash,Crypto'),
('payment_methods_germany', 'SEPA Bank Transfer,PayPal,Sofort'),
('payment_methods_france', 'Bank Transfer,PayPal,Lydia'),
('payment_methods_global', 'PayPal,Binance (USDT),Visa,MasterCard,Payeer,Perfect Money,Skrill,Neteller'),
('social_facebook', ''),
('social_twitter', ''),
('social_telegram', ''),
('adsterra_api_key', 'e200afd647b2f421354c26a7f95bd972'),
('ads_enabled', '1'),
('ad_popunder_code', '<script src=\"https://pl28841577.effectivegatecpm.com/84/e0/1c/84e01cf268ab48a5873ceeff192728f1.js\"></script>'),
('ad_social_bar_code', '<script src=\"https://pl28841591.effectivegatecpm.com/f7/86/88/f78688a1d1b5f7dfc12912e9ebd056eb.js\"></script>'),
('ad_native_code', '<script async=\"async\" data-cfasync=\"false\" src=\"https://pl28841584.effectivegatecpm.com/95f5ec433ea528d86e1f2554570bbe26/invoke.js\"></script><div id=\"container-95f5ec433ea528d86e1f2554570bbe26\"></div>'),
('ad_banner_468_60_code', '<script type=\"text/javascript\">atOptions = {\'key\' : \'662aebeac3f865448ab466ad6565634a\',\'format\' : \'iframe\',\'height\' : 60,\'width\' : 468,\'params\" : {}};</script><script type=\"text/javascript\" src=\"https://www.highperformanceformat.com/662aebeac3f865448ab466ad6565634a/invoke.js\"></script>'),
('ad_banner_728_90_code', '<script type=\"text/javascript\">atOptions = {\'key\' : \'f8db0fc983b631344cf0232bcfa50827\',\'format\' : \'iframe\',\'height\' : 90,\'width\' : 728,\'params\' : {}};</script><script type=\"text/javascript\" src=\"https://www.highperformanceformat.com/f8db0fc983b631344cf0232bcfa50827/invoke.js\"></script>'),
('ad_banner_300_250_code', '<script type=\"text/javascript\">atOptions = {\'key\' : \'a94a855e04ea571e77f97b88bd0d3371\',\'format\' : \'iframe\',\'height\' : 250,\'width\' : 300,\'params\' : {}};</script><script type=\"text/javascript\" src=\"https://www.highperformanceformat.com/a94a855e04ea571e77f97b88bd0d3371/invoke.js\"></script>'),
('ad_banner_160_300_code', '<script type=\"text/javascript\">atOptions = {\'key\' : \'8a54ca64110569a547ba7f584f33132d\',\'format\' : \'iframe\',\'height\' : 300,\'width\' : 160,\'params\' : {}};</script><script type=\"text/javascript\" src=\"https://www.highperformanceformat.com/8a54ca64110569a547ba7f584f33132d/invoke.js\"></script>'),
('ad_banner_160_600_code', '<script type=\"text/javascript\">atOptions = {\'key\' : \'1fc529756d2d4818277ce64280fb73f9\',\'format\' : \'iframe\',\'height\' : 600,\'width\' : 160,\'params\' : {}};</script><script type=\"text/javascript\" src=\"https://www.highperformanceformat.com/1fc529756d2d4818277ce64280fb73f9/invoke.js\"></script>'),
('ad_banner_320_50_code', '<script type=\"text/javascript\">atOptions = {\'key\' : \'6a05cc3f871975cbe274867ba7424efa\',\'format\' : \'iframe\',\'height\' : 50,\'width\' : 320,\'params\' : {}};</script><script type=\"text/javascript\" src=\"https://www.highperformanceformat.com/6a05cc3f871975cbe274867ba7424efa/invoke.js\"></script>'),
('ad_smartlink_url', 'https://www.effectivegatecpm.com/i5wy2tfi?key=abacd0c278ba5dfee619ea692d4c305f');

INSERT IGNORE INTO memberships (id, name, price, daily_ads, daily_surveys, ad_reward, duration_days) VALUES
(1, 'Free', 0.00, 10, 5, 0.0100, 3650),
(2, 'Starter', 10.00, 20, 10, 0.0200, 30),
(3, 'Pro', 50.00, 50, 25, 0.0500, 30),
(4, 'Ultimate', 100.00, 100, 50, 0.1000, 30);

INSERT INTO users (username, email, password, referral_code, is_admin, status) VALUES
('admin', 'admin@f-earning.com', '$2y$10$f9FrrPAj293mrhWCrwcq5OmWyDL8s0riQAQsMlK90unyDaPNssSN2', 'ADMIN_REF', 1, 'active')
ON DUPLICATE KEY UPDATE password = '$2y$10$f9FrrPAj293mrhWCrwcq5OmWyDL8s0riQAQsMlK90unyDaPNssSN2', is_admin = 1, status = 'active';