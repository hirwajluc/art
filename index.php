<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GREATER Art Competition 2025 - The Power of Creativity</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            overflow-x: hidden;
            background: 
                linear-gradient(135deg, rgba(10, 77, 58, 0.9) 0%, rgba(26, 122, 94, 0.85) 30%, rgba(45, 143, 71, 0.8) 70%, rgba(74, 222, 128, 0.75) 100%),
                url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 800"><defs><linearGradient id="skyGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:%2387CEEB;stop-opacity:1" /><stop offset="100%" style="stop-color:%23E0F6FF;stop-opacity:1" /></linearGradient></defs><rect width="1200" height="800" fill="url(%23skyGrad)"/><g><circle cx="150" cy="120" r="60" fill="%23FFD700" opacity="0.9"/><path d="M120,120 L180,120 M150,90 L150,150 M135,105 L165,135 M165,105 L135,135" stroke="%23FFA500" stroke-width="3"/></g><g><rect x="200" y="400" width="15" height="200" fill="%23228B22"/><rect x="220" y="380" width="15" height="220" fill="%2332CD32"/><rect x="240" y="420" width="15" height="180" fill="%23228B22"/><rect x="260" y="390" width="15" height="210" fill="%2332CD32"/><rect x="280" y="410" width="15" height="190" fill="%23228B22"/></g><g><polygon points="400,200 420,180 440,200 460,180 480,200 500,180 520,200 540,180 560,200 580,180 600,200 620,180 640,200 660,180 680,200 700,180 720,200 740,180 760,200 780,180 800,200" fill="none" stroke="%234169E1" stroke-width="3"/><circle cx="450" cy="300" r="80" fill="%234169E1" opacity="0.7"/><circle cx="750" cy="320" r="60" fill="%234169E1" opacity="0.5"/></g><g><rect x="900" y="500" width="80" height="100" fill="%23696969"/><polygon points="900,500 940,450 980,500" fill="%23FF6347"/><rect x="920" y="480" width="8" height="15" fill="%23FFD700"/><rect x="932" y="475" width="8" height="20" fill="%23FFD700"/><rect x="944" y="470" width="8" height="25" fill="%23FFD700"/></g><g><ellipse cx="1050" cy="550" rx="100" ry="30" fill="%23228B22" opacity="0.6"/><rect x="1020" y="530" width="60" height="40" fill="%234169E1" opacity="0.8"/></g><g><polygon points="100,600 150,550 200,600 250,550 300,600 350,550 400,600" fill="%23228B22" opacity="0.7"/><circle cx="500" cy="650" r="40" fill="%23FFD700" opacity="0.6"/><circle cx="600" cy="680" r="35" fill="%23FFD700" opacity="0.5"/></g></svg>') center/cover;
            background-attachment: fixed;
        }

        /* Animated background elements */
        .bg-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }

        .floating-element:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }

        .floating-element:nth-child(2) {
            top: 60%;
            right: 15%;
            animation-delay: 2s;
        }

        .floating-element:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(5deg); }
        }

        /* Header */
        header {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            display: flex;
            align-items: center;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.95);
            padding: 8px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.8);
            gap: 12px;
            transition: all 0.3s ease;
        }

        .logo:hover {
            background: rgba(255, 255, 255, 1);
            transform: scale(1.05);
        }

        .logo img {
            height: 50px;
            width: auto;
        }

        .logo-text {
            font-size: 1.8rem;
            font-weight: bold;
            color: #4ade80;
            display: none; /* Hide text since we now have both logos */
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #4ade80;
        }

        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            text-align: center;
            position: relative;
            color: white;
            padding: 120px 2rem 10rem 2rem;
        }

        .hero-content {
            max-width: 800px;
            animation: fadeInUp 1s ease-out;
            padding-top: 0;
        }

        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7), 0 0 20px rgba(74, 222, 128, 0.3);
            color: #4ade80;
            font-weight: 900;
            letter-spacing: 1px;
            line-height: 1.2;
        }

        .hero .subtitle {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .theme-text {
            font-size: 2rem;
            font-style: italic;
            margin-bottom: 2rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-block;
            position: relative;
            z-index: 10;
        }

        .btn-primary {
            background: linear-gradient(45deg, #4ade80, #22c55e);
            color: white;
            box-shadow: 0 4px 15px rgba(74, 222, 128, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(74, 222, 128, 0.6);
        }

        .btn:active {
            opacity: 0.9;
        }

        .btn-secondary {
            background: transparent;
            color: white;
            border: 2px solid #4ade80;
        }

        .btn-secondary:hover {
            background: #4ade80;
            color: #0a4d3a;
            transform: translateY(-3px);
        }

        /* Sections */
        .section {
            padding: 5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 3rem;
            color: white;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        /* Info Cards */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .info-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .info-card h3 {
            color: #4ade80;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .info-card p {
            color: white;
            opacity: 0.9;
        }

        /* Awards Section */
        .awards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }

        .award-card {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2), rgba(255, 165, 0, 0.2));
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 215, 0, 0.3);
            transition: all 0.3s ease;
        }

        .award-card:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 25px rgba(255, 215, 0, 0.3);
        }

        .award-card h4 {
            color: #ffd700;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .award-card .amount {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
        }

        /* Timeline */
        .timeline {
            position: relative;
            margin: 3rem 0;
        }

        .timeline-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 4px solid #4ade80;
            transition: all 0.3s ease;
        }

        .timeline-item:hover {
            transform: translateX(10px);
            background: rgba(255, 255, 255, 0.15);
        }

        .timeline-date {
            color: #4ade80;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .timeline-event {
            color: white;
            margin-top: 0.5rem;
        }

        /* Footer */
        footer {
            background: rgba(0, 0, 0, 0.9);
            color: white;
            text-align: center;
            padding: 3rem 2rem;
            backdrop-filter: blur(10px);
        }

        .footer-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #4ade80;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.5rem;
            }

            .hero .subtitle {
                font-size: 1.2rem;
            }

            .theme-text {
                font-size: 1.5rem;
            }

            .nav-links {
                display: none;
            }

            .logo {
                width: 100%;
                justify-content: center;
                padding: 6px 12px;
            }

            .logo img {
                height: 40px;
                max-width: 45%;
                object-fit: contain;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .section {
                padding: 3rem 1rem;
            }
        }

        /* Countdown Timer */
        .countdown {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            max-width: 400px;
            margin: 2rem auto;
        }

        .countdown-item {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .countdown-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4ade80;
        }

        .countdown-label {
            font-size: 0.8rem;
            color: white;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="floating-element">🌱</div>
        <div class="floating-element">☀️</div>
        <div class="floating-element">💡</div>
    </div>

    <!-- Header -->
    <header>
        <nav>
            <a href="#" class="logo">
                <img src="Greater_full_logo.png" alt="GREATER Logo">
                <img src="erasmusplus.png" alt="ERASMUS Logo">
            </a>
            <ul class="nav-links">
                <li><a href="#home">Home</a></li>
                <li><a href="#about">About</a></li>
                <li><a href="#categories">Categories</a></li>
                <li><a href="#awards">Awards</a></li>
                <li><a href="#timeline">Timeline</a></li>
            </ul>
        </nav>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-content">
            <h1>GREATER Art Competition</h1>
            <p class="subtitle">Energy connects us all through light, movement, and the stories we live every day.</p>
            <div class="theme-text">
                "The Power of Creativity: Green Energy for Tomorrow"
            </div>
            <p class="subtitle">With your camera or your phone, capture how energy shapes life, hope, and change in your world.</p>
            
            <!-- Countdown Timer -->
            <div class="countdown">
                <div class="countdown-item">
                    <div class="countdown-number" id="days">00</div>
                    <div class="countdown-label">Days</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="hours">00</div>
                    <div class="countdown-label">Hours</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="minutes">00</div>
                    <div class="countdown-label">Minutes</div>
                </div>
                <div class="countdown-item">
                    <div class="countdown-number" id="seconds">00</div>
                    <div class="countdown-label">Seconds</div>
                </div>
            </div>

            <div class="cta-buttons">
                <a href="https://greaterproject.eu/art/registration.php" class="btn btn-primary">Register Now</a>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="about" class="section animate-on-scroll">
        <h2 class="section-title">Join the GREATER Art Contest!</h2>
        <div class="info-grid">
            <div class="info-card">
                <h3>🎨 Show Your Vision</h3>
                <p>Show us how <strong>energy affects people's lives and our planet</strong>, and how <strong>renewable energy</strong> can inspire change and hope for a <strong>greener, fairer future</strong>.</p>
            </div>
            <div class="info-card">
                <h3>🌍 What to Represent</h3>
                <p>Explore how energy shapes life, the environment, and our imagination. Show how it connects people, transforms spaces, and relates to <strong>climate change</strong> as both a challenge and a path toward a sustainable future.</p>
            </div>
            <div class="info-card">
                <h3>✨ Be Original</h3>
                <p>All works must be <strong>original</strong> — not copied or downloaded from the web. We want to see <strong>your perspective, your story, your community</strong>.</p>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section id="about" class="section animate-on-scroll">
        <h2 class="section-title">About the Competition</h2>
        <div class="info-grid">
            <div class="info-card">
                <h3>🎨 Open to All</h3>
                <p>The competition is open to anyone residing in Rwanda.</p>
            </div>
            <div class="info-card">
                <h3>🌍 Sustainable Focus</h3>
                <p>Showcase renewable energy's impact - solar, wind, hydro, and geothermal - in building a greener world for developing and emerging countries.</p>
            </div>
            <div class="info-card">
                <h3>🤝 International Partnership</h3>
                <p>Collaboration between Rwandan and European institutions under the ERASMUS+ programme, with €800,000 EU contribution.</p>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="section animate-on-scroll">
        <h2 class="section-title">Competition Categories</h2>
        <div class="info-grid">
            <div class="info-card">
                <h3>📸 Digital Photography & Paint</h3>
                <p><strong>Formats:</strong> JPEG, TIFF, PNG, PDF<br>
                <strong>Requirements:</strong> Min 1920px, 180 dpi<br>
                Capture the beauty and impact of renewable energy in everyday life.</p>
            </div>
            <div class="info-card">
                <h3>🎬 Short Video Clips</h3>
                <p><strong>Formats:</strong> MP4, MOV<br>
                <strong>Requirements:</strong> Min 720p resolution<br>
                Create compelling stories about green energy innovation.</p>
            </div>
        </div>
    </section>

    <!-- Awards Section -->
    <section id="awards" class="section animate-on-scroll">
        <h2 class="section-title">Awards & Recognition</h2>
        <div class="awards-grid">
            <div class="award-card">
                <h4>🥇 First Place</h4>
                <div class="amount">250,000 RWF</div>
            </div>
            <div class="award-card">
                <h4>🥈 Second Place</h4>
                <div class="amount">100,000 RWF</div>
            </div>
            <div class="award-card">
                <h4>🥉 Third Place</h4>
                <div class="amount">50,000 RWF</div>
            </div>
            <div class="award-card">
                <h4>🏆 4th-8th Place</h4>
                <div class="amount">Certificate of Excellence</div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section id="timeline" class="section animate-on-scroll">
        <h2 class="section-title">Important Dates</h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date">May 15, 2025</div>
                <div class="timeline-event">Registration and submission open</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">June 30, 2026</div>
                <div class="timeline-event">Submission deadline</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">TBA</div>
                <div class="timeline-event">Winners announced</div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">TBA</div>
                <div class="timeline-event">Awarding Ceremony</div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-links">
                <a href="https://greaterproject.eu/art/submit.php">Submit Your Work</a>
                <a href="https://greaterproject.eu">GREATER Project</a>
                <a href="#about">Competition Rules</a>
                <a href="https://greaterproject.eu/contact">Contact Us</a>
            </div>
            <p>&copy; 2025 GREATER Project. Funded by the European Union under ERASMUS+ Programme.</p>
            <p><em>Growing Rwanda Energy Awareness Through Higher Education</em></p>
        </div>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.animate-on-scroll').forEach(el => {
            observer.observe(el);
        });

        // Countdown Timer - Updated to July 31, 2025
        function updateCountdown() {
            const targetDate = new Date('2026-06-30T23:59:59').getTime();
            const now = new Date().getTime();
            const difference = targetDate - now;

            if (difference > 0) {
                const days = Math.floor(difference / (1000 * 60 * 60 * 24));
                const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((difference % (1000 * 60)) / 1000);

                document.getElementById('days').textContent = days.toString().padStart(2, '0');
                document.getElementById('hours').textContent = hours.toString().padStart(2, '0');
                document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
                document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');
            } else {
                document.getElementById('days').textContent = '00';
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
            }
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);
        updateCountdown();

        // Header scroll effect
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.style.background = 'rgba(0, 0, 0, 0.95)';
            } else {
                header.style.background = 'rgba(0, 0, 0, 0.9)';
            }
        });

        // Parallax effect for floating elements
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const parallax = document.querySelectorAll('.floating-element');
            const speed = 0.5;

            parallax.forEach(element => {
                const yPos = -(scrolled * speed);
                element.style.transform = `translateY(${yPos}px)`;
            });
        });
    </script>
</body>
</html>