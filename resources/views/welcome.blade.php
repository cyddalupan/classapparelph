<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CLASS Apparel PH - Premium T-Shirt Printing & Custom Apparel</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            /* Professional Business Design System */
            :root {
                --primary: #2563eb;
                --primary-dark: #1d4ed8;
                --secondary: #7c3aed;
                --accent: #f59e0b;
                --dark: #1e293b;
                --light: #f8fafc;
                --gray: #64748b;
                --success: #10b981;
                --border-radius: 12px;
                --shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
                --shadow-lg: 0 20px 40px rgba(0, 0, 0, 0.12);
                --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            * { 
                margin: 0; 
                padding: 0; 
                box-sizing: border-box; 
            }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                color: var(--dark);
                line-height: 1.6;
                min-height: 100vh;
            }

            .container {
                max-width: 1280px;
                margin: 0 auto;
                padding: 0 2rem;
            }

            /* Header */
            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1.5rem 0;
                position: sticky;
                top: 0;
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                z-index: 1000;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }

            .logo {
                font-size: 1.75rem;
                font-weight: 800;
                color: var(--primary);
                text-decoration: none;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }

            .logo i {
                color: var(--accent);
            }

            .nav {
                display: flex;
                gap: 1.5rem;
                align-items: center;
            }

            .nav a {
                color: var(--gray);
                text-decoration: none;
                font-weight: 500;
                padding: 0.5rem 1rem;
                border-radius: var(--border-radius);
                transition: var(--transition);
                position: relative;
            }

            .nav a:hover {
                color: var(--primary);
                background: rgba(37, 99, 235, 0.05);
            }

            .nav a.active {
                color: var(--primary);
                background: rgba(37, 99, 235, 0.1);
            }

            /* Hero Section */
            .hero {
                padding: 6rem 0 4rem;
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .hero::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -20%;
                width: 600px;
                height: 600px;
                background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
                z-index: -1;
            }

            .hero h1 {
                font-size: 3.5rem;
                font-weight: 800;
                line-height: 1.1;
                margin-bottom: 1.5rem;
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .hero p {
                font-size: 1.25rem;
                color: var(--gray);
                max-width: 700px;
                margin: 0 auto 2.5rem;
            }

            .hero-stats {
                display: flex;
                justify-content: center;
                gap: 3rem;
                margin-top: 3rem;
                flex-wrap: wrap;
            }

            .stat {
                text-align: center;
            }

            .stat-number {
                font-size: 2.5rem;
                font-weight: 700;
                color: var(--primary);
                display: block;
            }

            .stat-label {
                color: var(--gray);
                font-size: 0.9rem;
                text-transform: uppercase;
                letter-spacing: 1px;
            }

            /* CTA Buttons */
            .cta-buttons {
                display: flex;
                gap: 1rem;
                justify-content: center;
                margin-top: 2rem;
            }

            .btn {
                padding: 1rem 2.5rem;
                border-radius: var(--border-radius);
                font-weight: 600;
                text-decoration: none;
                transition: var(--transition);
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                border: none;
                cursor: pointer;
                font-size: 1rem;
            }

            .btn-primary {
                background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
                color: white;
                box-shadow: var(--shadow);
            }

            .btn-primary:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-lg);
            }

            .btn-secondary {
                background: white;
                color: var(--primary);
                border: 2px solid var(--primary);
            }

            .btn-secondary:hover {
                background: var(--primary);
                color: white;
                transform: translateY(-3px);
            }

            /* Features Section */
            .section-title {
                text-align: center;
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
                color: var(--dark);
            }

            .section-subtitle {
                text-align: center;
                color: var(--gray);
                font-size: 1.125rem;
                max-width: 700px;
                margin: 0 auto 3rem;
            }

            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
                gap: 2rem;
                margin: 4rem 0;
            }

            .feature-card {
                background: white;
                padding: 2.5rem;
                border-radius: var(--border-radius);
                box-shadow: var(--shadow);
                transition: var(--transition);
                border: 1px solid rgba(0, 0, 0, 0.05);
                position: relative;
                overflow: hidden;
            }

            .feature-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
                background: linear-gradient(to bottom, var(--primary), var(--secondary));
            }

            .feature-card:hover {
                transform: translateY(-10px);
                box-shadow: var(--shadow-lg);
            }

            .feature-icon {
                font-size: 2.5rem;
                margin-bottom: 1.5rem;
                color: var(--primary);
            }

            .feature-card h3 {
                font-size: 1.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
                color: var(--dark);
            }

            .feature-card p {
                color: var(--gray);
                margin-bottom: 1.5rem;
            }

            .feature-link {
                color: var(--primary);
                text-decoration: none;
                font-weight: 600;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                transition: var(--transition);
            }

            .feature-link:hover {
                gap: 1rem;
            }

            /* Testimonials */
            .testimonials {
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                color: white;
                padding: 5rem 0;
                margin: 4rem 0;
                border-radius: var(--border-radius);
                position: relative;
                overflow: hidden;
            }

            .testimonials::before {
                content: '';
                position: absolute;
                top: -100px;
                right: -100px;
                width: 300px;
                height: 300px;
                background: rgba(255, 255, 255, 0.05);
                border-radius: 50%;
            }

            .testimonial-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
                margin-top: 3rem;
            }

            .testimonial-card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                padding: 2rem;
                border-radius: var(--border-radius);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }

            .testimonial-text {
                font-style: italic;
                margin-bottom: 1.5rem;
                line-height: 1.7;
            }

            .testimonial-author {
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .author-avatar {
                width: 50px;
                height: 50px;
                border-radius: 50%;
                background: white;
                color: var(--primary);
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
            }

            .author-info h4 {
                font-weight: 600;
                margin-bottom: 0.25rem;
            }

            .author-info p {
                opacity: 0.8;
                font-size: 0.9rem;
            }

            /* Final CTA */
            .final-cta {
                text-align: center;
                padding: 5rem 0;
                background: white;
                border-radius: var(--border-radius);
                margin: 4rem 0;
                box-shadow: var(--shadow);
            }

            .final-cta h2 {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 1rem;
                color: var(--dark);
            }

            .final-cta p {
                font-size: 1.125rem;
                color: var(--gray);
                max-width: 600px;
                margin: 0 auto 2.5rem;
            }

            /* Footer */
            .footer {
                background: var(--dark);
                color: white;
                padding: 4rem 0 2rem;
                margin-top: 4rem;
            }

            .footer-content {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 3rem;
                margin-bottom: 3rem;
            }

            .footer-logo {
                font-size: 1.5rem;
                font-weight: 700;
                color: white;
                margin-bottom: 1rem;
                display: block;
            }

            .footer-description {
                color: rgba(255, 255, 255, 0.7);
                line-height: 1.7;
            }

            .footer-heading {
                font-size: 1.125rem;
                font-weight: 600;
                margin-bottom: 1.5rem;
                color: white;
            }

            .footer-links {
                list-style: none;
            }

            .footer-links li {
                margin-bottom: 0.75rem;
            }

            .footer-links a {
                color: rgba(255, 255, 255, 0.7);
                text-decoration: none;
                transition: var(--transition);
            }

            .footer-links a:hover {
                color: white;
                padding-left: 0.5rem;
            }

            .footer-bottom {
                text-align: center;
                padding-top: 2rem;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                color: rgba(255, 255, 255, 0.5);
                font-size: 0.9rem;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .container {
                    padding: 0 1.5rem;
                }

                .hero h1 {
                    font-size: 2.5rem;
                }

                .hero p {
                    font-size: 1.125rem;
                }

                .section-title {
                    font-size: 2rem;
                }

                .features {
                    grid-template-columns: 1fr;
                }

                .cta-buttons {
                    flex-direction: column;
                    align-items: center;
                }

                .btn {
                    width: 100%;
                    justify-content: center;
                }

                .nav {
                    display: none;
                }

                .mobile-menu-btn {
                    display: block;
                    background: none;
                    border: none;
                    color: var(--primary);
                    font-size: 1.5rem;
                    cursor: pointer;
                }
            }

            @media (min-width: 769px) {
                .mobile-menu-btn {
                    display: none;
                }
            }
        </style>
    @endif
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <a href="/" class="logo">
                <i class="fas fa-tshirt"></i>
                CLASS Apparel PH
            </a>
            
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>

            <nav class="nav">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="active">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt"></i>
                            Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    @else
                        <a href="{{ route('login') }}">
                            <i class="fas fa-sign-in-alt"></i>
                            Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">
                                <i class="fas fa-user-plus"></i>
                                Register
                            </a>
                        @endif
                    @endauth
                @endif
                <a href="#features">
                    <i class="fas fa-star"></i>
                    Features
                </a>
                <a href="#testimonials">
                    <i class="fas fa-comment"></i>
                    Testimonials
                </a>
                <a href="#contact">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
            </nav>
        </header>

        <!-- Hero Section -->
        <main>
            <section class="hero">
                <h1>Transform Your Ideas Into Wearable Art</h1>
                <p>Professional t-shirt printing and custom apparel services with premium quality, fast turnaround, and exceptional customer support. Perfect for businesses, events, and personal projects.</p>
                
                <div class="cta-buttons">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt"></i>
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary">
                            <i class="fas fa-rocket"></i>
                            Start Your Order
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary">
                            <i class="fas fa-sign-in-alt"></i>
                            Login to Account
                        </a>
                    @endauth
                </div>

                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-number">500+</span>
                        <span class="stat-label">Happy Clients</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">24h</span>
                        <span class="stat-label">Fast Turnaround</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Quality Guarantee</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number">50+</span>
                        <span class="stat-label">Product Types</span>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section id="features">
                <h2 class="section-title">Why Choose CLASS Apparel PH?</h2>
                <p class="section-subtitle">We combine cutting-edge technology with expert craftsmanship to deliver exceptional printing results every time.</p>

                <div class="features">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-palette"></i>
                        </div>
                        <h3>Advanced Design Studio</h3>
                        <p>Upload your designs or use our online designer. Support for PNG, JPG, SVG, AI, PSD files with real-time preview and editing tools.</p>
                        <a href="#" class="feature-link">
                            Explore Design Tools
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <h3>Premium Product Catalog</h3>
                        <p>Choose from 50+ apparel types including t-shirts, polos, hoodies, caps, and jackets. All from trusted brands with quality guarantees.</p>
                        <a href="#" class="feature-link">
                            Browse Products
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Real-Time Production Tracking</h3>
                        <p>Track your order from design approval to production, quality check, and delivery. Get notifications at every stage of the process.</p>
                        <a href="#" class="feature-link">
                            View Sample Tracking
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <h3>Transparent Pricing</h3>
                        <p>No hidden fees. See exact costs with bulk discounts, VAT-inclusive pricing, and multiple payment options including GCash and Maya.</p>
                        <a href="#" class="feature-link">
                            Calculate Pricing
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <h3>Production Management</h3>
                        <p>Advanced staff dashboard for workflow management, inventory tracking, quality control, and production scheduling.</p>
                        <a href="#" class="feature-link">
                            Learn About Production
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3>Mobile-First Platform</h3>
                        <p>Access your account, upload designs, and track orders from any device. Perfect for on-the-go business owners and event organizers.</p>
                        <a href="#" class="feature-link">
                            Try Mobile Demo
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Testimonials -->
            <section id="testimonials" class="testimonials">
                <h2 class="section-title" style="color: white;">What Our Clients Say</h2>
                <p class="section-subtitle" style="color: rgba(255, 255, 255, 0.9);">Join hundreds of satisfied customers who trust CLASS Apparel PH for their custom printing needs.</p>

                <div class="testimonial-grid">
                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "The quality exceeded our expectations! We ordered 200 shirts for our company event and they arrived perfectly on time. The online tracking system kept us updated throughout the process."
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">MS</div>
                            <div class="author-info">
                                <h4>Maria Santos</h4>
                                <p>Event Coordinator, TechCorp PH</p>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "As a startup, we needed affordable but high-quality merch. CLASS Apparel delivered exactly what we needed. The design upload was seamless and the customer support was exceptional."
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">JR</div>
                            <div class="author-info">
                                <h4>Juan Reyes</h4>
                                <p>Founder, Startup Manila</p>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-card">
                        <div class="testimonial-text">
                            "We've been using CLASS Apparel for all our school uniform needs for 3 years now. Consistent quality, reliable delivery, and excellent customer service. Highly recommended!"
                        </div>
                        <div class="testimonial-author">
                            <div class="author-avatar">AD</div>
                            <div class="author-info">
                                <h4>Ana Dela Cruz</h4>
                                <p>Principal, St. Mary's Academy</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final CTA -->
            <section id="contact" class="final-cta">
                <h2>Ready to Bring Your Designs to Life?</h2>
                <p>Join our growing community of satisfied customers. Create your account today and experience premium printing services with professional results.</p>
                
                <div class="cta-buttons">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary" style="font-size: 1.125rem; padding: 1.25rem 3rem;">
                            <i class="fas fa-tachometer-alt"></i>
                            Access Your Dashboard
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn btn-primary" style="font-size: 1.125rem; padding: 1.25rem 3rem;">
                            <i class="fas fa-user-plus"></i>
                            Create Free Account
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary" style="font-size: 1.125rem; padding: 1.25rem 3rem;">
                            <i class="fas fa-sign-in-alt"></i>
                            Login to Existing Account
                        </a>
                    @endauth
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="footer">
            <div class="container">
                <div class="footer-content">
                    <div>
                        <a href="/" class="footer-logo">
                            <i class="fas fa-tshirt"></i>
                            CLASS Apparel PH
                        </a>
                        <p class="footer-description">
                            Premium t-shirt printing and custom apparel services in the Philippines. 
                            Transforming ideas into wearable art with quality, speed, and precision.
                        </p>
                    </div>

                    <div>
                        <h3 class="footer-heading">Quick Links</h3>
                        <ul class="footer-links">
                            <li><a href="#features">Features</a></li>
                            <li><a href="#testimonials">Testimonials</a></li>
                            <li><a href="#contact">Contact</a></li>
                            <li><a href="{{ route('login') }}">Login</a></li>
                            <li><a href="{{ route('register') }}">Register</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="footer-heading">Contact Info</h3>
                        <ul class="footer-links">
                            <li><i class="fas fa-map-marker-alt"></i> Maysan, Valenzuela, Philippines</li>
                            <li><i class="fas fa-envelope"></i> admin@classapparelph.com</li>
                            <li><i class="fas fa-phone"></i> +63 912 345 6789</li>
                            <li><i class="fas fa-clock"></i> Mon-Fri: 9AM-6PM</li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="footer-heading">Follow Us</h3>
                        <ul class="footer-links">
                            <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                            <li><a href="#"><i class="fab fa-instagram"></i> Instagram</a></li>
                            <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                            <li><a href="#"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                        </ul>
                    </div>
                </div>

                <div class="footer-bottom">
                    <p>&copy; 2026 CLASS Apparel PH. All rights reserved. | Premium T-Shirt Printing Services • Philippines</p>
                    <p style="margin-top: 0.5rem; font-size: 0.8rem;">VAT Registered • ISO 9001:2015 Certified</p>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript for interactivity -->
    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn')?.addEventListener('click', function() {
            const nav = document.querySelector('.nav');
            nav.style.display = nav.style.display === 'flex' ? 'none' : 'flex';
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                if (targetId === '#') return;
                
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Animate stats counter
        function animateCounter(element, target, duration = 2000) {
            let start = 0;
            const increment = target / (duration / 16);
            const timer = setInterval(() => {
                start += increment;
                if (start >= target) {
                    element.textContent = target + (element.textContent.includes('+') ? '+' : '');
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(start) + (element.textContent.includes('+') ? '+' : '');
                }
            }, 16);
        }

        // Start counter animation when in viewport
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const statNumbers = document.querySelectorAll('.stat-number');
                    statNumbers.forEach(stat => {
                        const target = parseInt(stat.textContent);
                        if (!isNaN(target)) {
                            animateCounter(stat, target);
                        }
                    });
                    observer.disconnect();
                }
            });
        }, { threshold: 0.5 });

        observer.observe(document.querySelector('.hero-stats'));

        // Add active class to current nav link
        window.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('section[id]');
            const navLinks = document.querySelectorAll('.nav a');
            
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= sectionTop - 100) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>