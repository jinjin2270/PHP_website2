<?php
// about.php - Located in the php folder
require_once __DIR__ . '/../includes/header.php';
?>

<link rel="stylesheet" href="../style.css">

<main class="about-page">
    <!-- Hero Section -->
    <section class="about-hero">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Our Story</h1>
                <p class="hero-subtitle">Discover the passion behind Future Blog</p>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="about-section mission-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">Our Purpose</span>
                <h2 class="section-title">Why We Exist</h2>
                <div class="section-divider"></div>
                <p class="section-description">We're on a mission to democratize knowledge and empower creators</p>
            </div>
            
            <div class="mission-grid">
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <h3>Elevate Writing</h3>
                    <p>We provide a platform for writers to share their best work with a global audience.</p>
                </div>
                
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3>Spark Ideas</h3>
                    <p>Our community fosters creativity and innovation through thoughtful content.</p>
                </div>
                
                <div class="mission-card">
                    <div class="mission-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Build Community</h3>
                    <p>We connect like-minded individuals who value knowledge and growth.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="about-section team-section">
        <div class="container">
            <div class="section-header">
                <span class="section-tag">The Faces Behind</span>
                <h2 class="section-title">Our Team</h2>
                <div class="section-divider"></div>
                <p class="section-description">Passionate individuals building the future of content</p>
            </div>
            
            <div class="team-grid">
                <div class="team-card">
                    <div class="team-image">
                        <img src="../assets/images/james-wilson.jpg" alt="Alex Johnson">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                    <h3>Alex Johnson</h3>
                    <p class="team-role">Founder & CEO</p>
                    <p class="team-bio">Former journalist with a vision for better content</p>
                </div>
                
                <div class="team-card">
                    <div class="team-image">
                        <img src="../assets/images/emma-thompson.jpg" alt="Sarah Williams">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                    <h3>Sarah Williams</h3>
                    <p class="team-role">Editor-in-Chief</p>
                    <p class="team-bio">Curates the best content for our readers</p>
                </div>
                
                <div class="team-card">
                    <div class="team-image">
                        <img src="../assets/images/michael-brown.jpg" alt="Michael Chen">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                    <h3>Michael Chen</h3>
                    <p class="team-role">Tech Lead</p>
                    <p class="team-bio">Builds the platform that makes it all possible</p>
                </div>
                
                <div class="team-card">
                    <div class="team-image">
                        <img src="../assets/images/sarah-johnson.jpg" alt="Emma Rodriguez">
                        <div class="team-social">
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                    <h3>Emma Rodriguez</h3>
                    <p class="team-role">Community Manager</p>
                    <p class="team-bio">Connects our writers and readers</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="about-stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" data-count="10000">1000</div>
                    <div class="stat-label">Monthly Readers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-count="500">200</div>
                    <div class="stat-label">Published Articles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-count="120">50</div>
                    <div class="stat-label">Expert Contributors</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" data-count="95">30</div>
                    <div class="stat-label">% Satisfaction</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="about-cta">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to join our community?</h2>
                <p>Whether you want to share your knowledge or discover new ideas, we'd love to have you.</p>
                <div class="cta-buttons">
                    <?php if ($isLoggedIn): ?>
                        <a href="submit_post.php" class="btn btn-primary">Write Your First Post</a>
                    <?php else: ?>
                        <a href="../pages/register.html" class="btn btn-primary">Become a Contributor</a>
                        <a href="../pages/login.html" class="btn btn-outline">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="../assets/js/about.js"></script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>