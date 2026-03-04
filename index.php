<?php 
require_once 'config/config.php'; 

// Fetch all available membership plans
$stmt = $pdo->query("SELECT * FROM memberships ORDER BY price ASC");
$membership_plans = $stmt->fetchAll();

$min_withdrawal = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'min_withdrawal'")->fetchColumn() ?: '10.00';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?> - Earn Money Watching Ads</title>
    <?php if(SITE_FAVICON): ?>
        <link rel="icon" type="image/x-icon" href="assets/img/<?php echo SITE_FAVICON; ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_popunder_code'] ?? ''; ?>
    <?php endif; ?>
    <style>
        /* Hero Slider Styles */
        .hero-slider {
            position: relative;
            overflow: hidden;
            width: 100%;
            height: 600px;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.05), transparent);
        }
        .hero-track {
            display: flex;
            height: 100%;
            transition: transform 0.7s cubic-bezier(0.645, 0.045, 0.355, 1);
        }
        .hero-slide {
            min-width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 2rem;
        }
        .hero-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 50px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .hero-dots {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 10;
        }
        .hero-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(99, 102, 241, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .hero-dot.active {
            background: var(--primary-color);
            width: 30px;
            border-radius: 10px;
        }
        @media (max-width: 992px) {
            .hero-slider { height: auto; padding: 60px 0; }
            .hero-content { flex-direction: column; text-align: center; }
            .hero-slide { padding: 40px 1rem; }
        }

        .feature-card {
            padding: 40px;
            border-radius: 1.5rem;
            background: white;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            text-align: center;
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            border-color: var(--primary-color);
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            margin-bottom: 20px;
        }
        .testimonial-slider {
            overflow: hidden;
            position: relative;
            padding: 20px 0;
        }
        .testimonial-track {
            display: flex;
            gap: 30px;
            transition: transform 0.5s ease-in-out;
        }
        .testimonial-card {
            min-width: calc(33.333% - 20px);
            padding: 30px;
            background: white;
            border-radius: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            border: 1px solid #f1f5f9;
            flex-shrink: 0;
        }
        @media (max-width: 992px) {
            .testimonial-card { min-width: calc(50% - 15px); }
        }
        @media (max-width: 640px) {
            .testimonial-card { min-width: 100%; }
        }
    </style>
</head>
<body class="light">
    <header class="navbar-fixed">
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <main>
        <!-- Sliding Hero Section -->
        <section class="hero-slider fade-in">
            <div class="hero-track" id="hero-track">
                <!-- Slide 1: Main Earning -->
                <div class="hero-slide">
                    <div class="hero-content">
                        <div style="flex: 1.2;">
                            <span style="background: rgba(99, 102, 241, 0.1); color: var(--primary-color); padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 700; margin-bottom: 20px; display: inline-block;">✨ The #1 Earning Platform</span>
                            <h1 style="font-size: 4rem; line-height: 1.1; margin-bottom: 25px; font-weight: 800; letter-spacing: -1px;">Turn Your Spare Time Into <span style="color: var(--primary-color); background: linear-gradient(120deg, var(--primary-color), #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Real Cash</span></h1>
                            <p style="font-size: 1.25rem; opacity: 0.8; margin-bottom: 35px; line-height: 1.6;">Join 50,000+ users earning daily by watching simple ads and completing surveys. Start your journey today with an instant welcome bonus.</p>
                            <div style="display: flex; gap: 20px; align-items: center;">
                                <a href="register.php" class="btn btn-primary" style="padding: 1.2rem 2.5rem; font-size: 1.1rem; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">Start Earning Now</a>
                                <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
                                <a href="<?php echo htmlspecialchars($settings['ad_smartlink_url'] ?? ''); ?>" target="_blank" class="btn" style="border: 2px solid var(--border-light); padding: 1.1rem 2.2rem; font-size: 1.1rem; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                                    <span>🚀</span> Bonus Earning
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="flex: 0.8;">
                            <img src="https://img.freepik.com/free-vector/digital-marketing-concept-illustration_114360-128.jpg" alt="Earning" style="width: 100%; border-radius: 2rem;">
                        </div>
                    </div>
                </div>

                <!-- Slide 2: Survey Focus -->
                <div class="hero-slide">
                    <div class="hero-content">
                        <div style="flex: 1.2;">
                            <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 700; margin-bottom: 20px; display: inline-block;">📋 Premium Surveys Active</span>
                            <h1 style="font-size: 4rem; line-height: 1.1; margin-bottom: 25px; font-weight: 800; letter-spacing: -1px;">Share Your Opinion <br>& <span style="color: #10b981;">Get Paid</span></h1>
                            <p style="font-size: 1.25rem; opacity: 0.8; margin-bottom: 35px; line-height: 1.6;">Our new survey system features 200+ high-paying tasks daily. Earn up to $1.50 per survey with instant verification and localized payouts.</p>
                            <div style="display: flex; gap: 20px; align-items: center;">
                                <a href="register.php" class="btn" style="background: #10b981; color: white; padding: 1.2rem 2.5rem; font-size: 1.1rem; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3); text-decoration: none;">Take a Survey</a>
                                <a href="#how-it-works" class="btn" style="border: 2px solid var(--border-light); padding: 1.1rem 2.2rem; font-size: 1.1rem; border-radius: 12px;">Learn More</a>
                            </div>
                        </div>
                        <div style="flex: 0.8;">
                            <img src="https://img.freepik.com/free-vector/forms-concept-illustration_114360-4917.jpg" alt="Surveys" style="width: 100%; border-radius: 2rem;">
                        </div>
                    </div>
                </div>

                <!-- Slide 3: Global Community -->
                <div class="hero-slide">
                    <div class="hero-content">
                        <div style="flex: 1.2;">
                            <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 8px 16px; border-radius: 20px; font-size: 0.9rem; font-weight: 700; margin-bottom: 20px; display: inline-block;">🌍 Global Payouts</span>
                            <h1 style="font-size: 4rem; line-height: 1.1; margin-bottom: 25px; font-weight: 800; letter-spacing: -1px;">Work From Anywhere <br>In The <span style="color: #f59e0b;">World</span></h1>
                            <p style="font-size: 1.25rem; opacity: 0.8; margin-bottom: 35px; line-height: 1.6;">Supporting 190+ countries with local payment methods. Whether you're in Bangladesh, USA, or India, we have a payout method for you.</p>
                            <div style="display: flex; gap: 20px; align-items: center;">
                                <a href="register.php" class="btn" style="background: #f59e0b; color: white; padding: 1.2rem 2.5rem; font-size: 1.1rem; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3); text-decoration: none;">Join Global Network</a>
                                <a href="leaderboard.php" class="btn" style="border: 2px solid var(--border-light); padding: 1.1rem 2.2rem; font-size: 1.1rem; border-radius: 12px;">View Top Earners</a>
                            </div>
                        </div>
                        <div style="flex: 0.8;">
                            <img src="https://img.freepik.com/free-vector/global-advertising-concept-illustration_114360-8742.jpg" alt="Global" style="width: 100%; border-radius: 2rem;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Slider Dots -->
            <div class="hero-dots">
                <div class="hero-dot active" onclick="goToHeroSlide(0)"></div>
                <div class="hero-dot" onclick="goToHeroSlide(1)"></div>
                <div class="hero-dot" onclick="goToHeroSlide(2)"></div>
            </div>
        </section>

        <script>
            // Hero Slider Logic
            const heroTrack = document.getElementById('hero-track');
            const heroDots = document.querySelectorAll('.hero-dot');
            let heroIndex = 0;
            const heroCount = 3;

            function goToHeroSlide(idx) {
                heroIndex = idx;
                updateHeroSlider();
            }

            function updateHeroSlider() {
                heroTrack.style.transform = `translateX(-${heroIndex * 100}%)`;
                heroDots.forEach((dot, i) => {
                    dot.classList.toggle('active', i === heroIndex);
                });
            }

            // Auto slide every 6 seconds
            setInterval(() => {
                heroIndex = (heroIndex + 1) % heroCount;
                updateHeroSlider();
            }, 6000);
        </script>

        <!-- Stats Section -->
        <section style="background: linear-gradient(135deg, var(--primary-color) 0%, #4f46e5 100%); padding: 80px 0; color: white;">
            <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; text-align: center;">
                <div class="fade-in">
                    <span style="font-size: 3rem; display: block; margin-bottom: 15px;">👥</span>
                    <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">52,481+</h3>
                    <p style="opacity: 0.9; font-size: 1.1rem; font-weight: 500;">Active Members</p>
                </div>
                <div class="fade-in" style="transition-delay: 0.1s;">
                    <span style="font-size: 3rem; display: block; margin-bottom: 15px;">💳</span>
                    <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">$148,290+</h3>
                    <p style="opacity: 0.9; font-size: 1.1rem; font-weight: 500;">Total Paid Out</p>
                </div>
                <div class="fade-in" style="transition-delay: 0.2s;">
                    <span style="font-size: 3rem; display: block; margin-bottom: 15px;">📺</span>
                    <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">1.2M+</h3>
                    <p style="opacity: 0.9; font-size: 1.1rem; font-weight: 500;">Tasks Completed</p>
                </div>
                <div class="fade-in" style="transition-delay: 0.3s;">
                    <span style="font-size: 3rem; display: block; margin-bottom: 15px;">🌍</span>
                    <h3 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 5px;">190+</h3>
                    <p style="opacity: 0.9; font-size: 1.1rem; font-weight: 500;">Countries Active</p>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" style="padding: 100px 0; background: #fff;">
            <div class="container">
                <div style="text-align: center; margin-bottom: 70px;">
                    <span style="color: var(--primary-color); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; font-size: 0.9rem;">Premium Features</span>
                    <h2 style="font-size: 3rem; font-weight: 800; margin-top: 10px; color: #1e293b;">Why Earn With Us?</h2>
                    <p style="color: #64748b; font-size: 1.2rem; margin-top: 15px;">The most reliable micro-earning ecosystem built for users worldwide.</p>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
                    <div class="feature-card">
                        <div style="font-size: 3rem; margin-bottom: 20px;">⚡</div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 15px;">Instant Verification</h3>
                        <p style="color: #64748b; line-height: 1.6;">No waiting days for approval. Once you finish an ad or survey, your reward is credited instantly to your wallet.</p>
                    </div>
                    <div class="feature-card">
                        <div style="font-size: 3rem; margin-bottom: 20px;">�️</div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 15px;">Secure Payments</h3>
                        <p style="color: #64748b; line-height: 1.6;">We use enterprise-grade encryption for all transactions. Your data and earnings are always safe with us.</p>
                    </div>
                    <div class="feature-card">
                        <div style="font-size: 3rem; margin-bottom: 20px;">💰</div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 15px;">High Payouts</h3>
                        <p style="color: #64748b; line-height: 1.6;">Our direct partnerships with advertisers allow us to offer some of the highest rewards in the industry.</p>
                    </div>
                    <div class="feature-card">
                        <div style="font-size: 3rem; margin-bottom: 20px;">📱</div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 15px;">Mobile Friendly</h3>
                        <p style="color: #64748b; line-height: 1.6;">Earn on the go. Our platform is fully optimized for smartphones, so you can earn anytime, anywhere.</p>
                    </div>
                    <div class="feature-card">
                        <div style="font-size: 3rem; margin-bottom: 20px;">🎁</div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 15px;">Daily Bonuses</h3>
                        <p style="color: #64748b; line-height: 1.6;">Log in every day to claim bonus rewards and participate in high-value promotional tasks.</p>
                    </div>
                    <div class="feature-card">
                        <div style="font-size: 3rem; margin-bottom: 20px;">🤝</div>
                        <h3 style="font-size: 1.5rem; margin-bottom: 15px;">Referral Program</h3>
                        <p style="color: #64748b; line-height: 1.6;">Invite your friends and earn a lifetime 10% commission on every task they complete.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section style="padding: 100px 0; background: #f8fafc;">
            <div class="container">
                <div style="text-align: center; margin-bottom: 60px;">
                    <h2 style="font-size: 2.5rem; font-weight: 800; color: #1e293b;">Trusted by Thousands</h2>
                    <p style="color: #64748b; margin-top: 10px;">See what our community has to say about their experience.</p>
                </div>
                <div class="testimonial-slider">
                    <div class="testimonial-track" id="testimonial-track">
                        <div class="testimonial-card">
                            <div style="color: #f59e0b; margin-bottom: 15px;">⭐⭐⭐⭐⭐</div>
                            <p style="color: #475569; font-style: italic; line-height: 1.7; margin-bottom: 20px;">"I was skeptical at first, but I've already withdrawn $50 via PayPal. The surveys are easy and the support is very helpful."</p>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #6366f1; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">R</div>
                                <div>
                                    <h4 style="font-size: 0.95rem; color: #1e293b;">Rahat Ahmed</h4>
                                    <p style="font-size: 0.8rem; color: #64748b;">Member since 2023</p>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div style="color: #f59e0b; margin-bottom: 15px;">⭐⭐⭐⭐⭐</div>
                            <p style="color: #475569; font-style: italic; line-height: 1.7; margin-bottom: 20px;">"The best platform for extra income. I use it during my commute and earn enough to cover my monthly subscriptions."</p>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #ec4899; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">S</div>
                                <div>
                                    <h4 style="font-size: 0.95rem; color: #1e293b;">Sarah J.</h4>
                                    <p style="font-size: 0.8rem; color: #64748b;">Premium Member</p>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div style="color: #f59e0b; margin-bottom: 15px;">⭐⭐⭐⭐⭐</div>
                            <p style="color: #475569; font-style: italic; line-height: 1.7; margin-bottom: 20px;">"Fast payouts and a clean interface. The new survey system with the direct links is very smooth. Highly recommended!"</p>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">M</div>
                                <div>
                                    <h4 style="font-size: 0.95rem; color: #1e293b;">Michael K.</h4>
                                    <p style="font-size: 0.8rem; color: #64748b;">Expert Level</p>
                                </div>
                            </div>
                        </div>
                        <!-- Extra testimonials for sliding -->
                        <div class="testimonial-card">
                            <div style="color: #f59e0b; margin-bottom: 15px;">⭐⭐⭐⭐⭐</div>
                            <p style="color: #475569; font-style: italic; line-height: 1.7; margin-bottom: 20px;">"Easy interface and high rewards compared to other sites. I love the new gold tasks!"</p>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #f59e0b; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">A</div>
                                <div>
                                    <h4 style="font-size: 0.95rem; color: #1e293b;">Amit S.</h4>
                                    <p style="font-size: 0.8rem; color: #64748b;">Silver Member</p>
                                </div>
                            </div>
                        </div>
                        <div class="testimonial-card">
                            <div style="color: #f59e0b; margin-bottom: 15px;">⭐⭐⭐⭐⭐</div>
                            <p style="color: #475569; font-style: italic; line-height: 1.7; margin-bottom: 20px;">"Payment received within 24 hours. Very professional and reliable support team."</p>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; background: #8b5cf6; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">J</div>
                                <div>
                                    <h4 style="font-size: 0.95rem; color: #1e293b;">Jessica L.</h4>
                                    <p style="font-size: 0.8rem; color: #64748b;">Pro Plan User</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script>
            // Testimonial Auto-Slider Logic
            const track = document.getElementById('testimonial-track');
            let index = 0;
            const cardsCount = track.children.length;
            
            function slideTestimonials() {
                const cardWidth = track.children[0].offsetWidth + 30; // card + gap
                index++;
                
                if (index > cardsCount - 3) { // Show 3 cards at a time on desktop
                    index = 0;
                }
                
                track.style.transform = `translateX(-${index * cardWidth}px)`;
            }
            
            // Adjust index threshold for mobile
            function checkResponsive() {
                if (window.innerWidth <= 640) return cardsCount - 1;
                if (window.innerWidth <= 992) return cardsCount - 2;
                return cardsCount - 3;
            }

            setInterval(() => {
                const maxIndex = checkResponsive();
                const cardWidth = track.children[0].offsetWidth + 30;
                
                index++;
                if (index > maxIndex) index = 0;
                
                track.style.transform = `translateX(-${index * cardWidth}px)`;
            }, 4000);
        </script>

        <!-- CTA Section -->
        <section style="padding: 100px 0; background: white;">
            <div class="container" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); border-radius: 3rem; padding: 80px 40px; text-align: center; color: white;">
                <h2 style="font-size: 3rem; font-weight: 800; margin-bottom: 20px;">Ready to Start Earning?</h2>
                <p style="font-size: 1.25rem; opacity: 0.9; margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto;">Join our global community today and get a $1.00 welcome bonus survey immediately after registration.</p>
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="register.php" class="btn" style="background: white; color: #4f46e5; padding: 1.2rem 3rem; font-weight: 800; font-size: 1.1rem; border-radius: 50px;">Create Free Account</a>
                    <a href="login.php" class="btn" style="background: rgba(255,255,255,0.1); color: white; padding: 1.2rem 3rem; font-weight: 700; font-size: 1.1rem; border-radius: 50px; border: 2px solid white;">Member Login</a>
                </div>
            </div>
        </section>

        <section id="how-it-works" class="container" style="padding: 100px 0;">
            <!-- Adsterra Native Banner -->
            <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
            <div style="margin-bottom: 40px; text-align: center;">
                <?php echo $settings['ad_native_code'] ?? ''; ?>
            </div>
            <?php endif; ?>
            <div style="text-align: center; margin-bottom: 60px;">
                <h2 style="font-size: 2.5rem;">How It Works</h2>
                <p style="opacity: 0.7;">Simple steps to start your earning journey</p>
                <!-- Adsterra 728x90 Banner -->
                <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
                <div style="margin-top: 30px; display: flex; justify-content: center;">
                    <?php echo $settings['ad_banner_728_90_code'] ?? ''; ?>
                </div>
                <?php endif; ?>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 30px;">
                <div class="card slide-up" style="text-align: center;">
                    <div style="background: rgba(99, 102, 241, 0.1); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <span style="font-size: 1.5rem;">👤</span>
                    </div>
                    <h3>1. Create Account</h3>
                    <p style="opacity: 0.8; margin-top: 10px;">Register for free and verify your email to get started with your personalized dashboard.</p>
                </div>
                <div class="card slide-up" style="text-align: center; transition-delay: 0.1s;">
                    <div style="background: rgba(16, 185, 129, 0.1); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <span style="font-size: 1.5rem;">📺</span>
                    </div>
                    <h3>2. Watch Ads</h3>
                    <p style="opacity: 0.8; margin-top: 10px;">Select from a wide variety of available ads and watch them for just a few seconds.</p>
                </div>
                <div class="card slide-up" style="text-align: center; transition-delay: 0.2s;">
                    <div style="background: rgba(245, 158, 11, 0.1); width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                        <span style="font-size: 1.5rem;">💰</span>
                    </div>
                    <h3>3. Earn Money</h3>
                    <p style="opacity: 0.8; margin-top: 10px;">Get paid instantly into your balance. Withdraw your earnings via your preferred payment method.</p>
                </div>
            </div>
        </section>

        <section id="pricing" class="container" style="padding: 100px 0; background: rgba(99, 102, 241, 0.03); border-radius: 2rem;">
            <!-- Adsterra 300x250 Banner -->
            <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
            <div style="margin-bottom: 40px; display: flex; justify-content: center;">
                <?php echo $settings['ad_banner_300_250_code'] ?? ''; ?>
            </div>
            <?php endif; ?>
            <div style="text-align: center; margin-bottom: 60px;">
                <h2 style="font-size: 2.5rem;">Membership Plans</h2>
                <p style="opacity: 0.7;">Choose a plan that fits your earning goals</p>
            </div>
            
            <!-- Payment Methods Info -->
            <div style="text-align: center; margin-bottom: 40px; padding: 20px; background: white; border-radius: 1rem; box-shadow: var(--shadow);">
                <h4 style="margin-bottom: 15px; opacity: 0.8;">We Accept Global Payment Methods</h4>
                <div style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; align-items: center;">
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/1200px-PayPal.svg.png" style="height: 25px; object-fit: contain;" alt="PayPal">
                        <span style="font-size: 0.75rem; font-weight: 600;">PayPal</span>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/1/12/Binance_logo.svg/1200px-Binance_logo.svg.png" style="height: 25px; object-fit: contain;" alt="Binance">
                        <span style="font-size: 0.75rem; font-weight: 600;">Binance (USDT)</span>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; justify-content: center;">
                <?php foreach($membership_plans as $plan): ?>
                <div class="card" style="position: relative; display: flex; flex-direction: column; justify-content: space-between;">
                    <?php if($plan['price'] > 10 && $plan['price'] < 100): ?>
                        <span style="position: absolute; top: -15px; right: 20px; background: var(--primary-color); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem;">POPULAR</span>
                    <?php elseif($plan['price'] >= 100): ?>
                        <span style="position: absolute; top: -15px; right: 20px; background: var(--secondary-color); color: white; padding: 5px 15px; border-radius: 20px; font-size: 0.8rem;">BEST VALUE</span>
                    <?php endif; ?>
                    
                    <div>
                        <h3 style="color: var(--primary-color);"><?php echo htmlspecialchars($plan['name']); ?> Plan</h3>
                        <h2 style="font-size: 2.5rem; margin: 20px 0;">$<?php echo number_format($plan['price'], 0); ?> <small style="font-size: 1rem; opacity: 0.6;">/<?php echo $plan['duration_days'] >= 365 ? 'lifetime' : 'month'; ?></small></h2>
                        <ul style="list-style: none; margin-bottom: 30px;">
                            <li style="margin-bottom: 10px;">✅ <?php echo $plan['daily_ads']; ?> Ads Daily</li>
                            <li style="margin-bottom: 10px;">✅ $<?php echo number_format($plan['ad_reward'], 2); ?> Per Ad</li>
                            <li style="margin-bottom: 10px;">✅ <?php echo $plan['price'] > 0 ? 'Priority' : 'Basic'; ?> Support</li>
                            <li style="margin-bottom: 10px;">✅ $<?php echo $min_withdrawal; ?> Min Withdraw</li>
                        </ul>
                    </div>
                    <a href="register.php" class="btn btn-primary" style="width: 100%;"><?php echo $plan['price'] > 0 ? 'Upgrade to ' . htmlspecialchars($plan['name']) : 'Join Free'; ?></a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <?php if(($settings['ads_enabled'] ?? '1') == '1'): ?>
        <?php echo $settings['ad_social_bar_code'] ?? ''; ?>
    <?php endif; ?>
</body>
</html>
