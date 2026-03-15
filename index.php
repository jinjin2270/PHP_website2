<?php
session_start();
require_once("./php/functions.php"); 
require_once("./config/db.php"); 

$isLoggedIn = isset($_SESSION['user_id']);
$name = $isLoggedIn ? $_SESSION['name'] : '';
$categories = getAllCategories($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Future Blog</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
   
     <nav>
        <div class="container navbar">
            <a href="#" class="logo">
                <i class="fas fa-feather-alt logo-icon"></i> FUTURE
            </a>
            <div class="nav-links">
                <a href="index.php">Home</a>
                <div class="dropdown">
                    <a href="./php/category.php" class="dropbtn">Categories <i class="fas fa-caret-down"></i></a>
                    <div class="dropdown-content">
                        <?php foreach ($categories as $category): ?>
                            <a href="./php/category.php?cat=<?= urlencode($category['name']) ?>&id=<?= $category['id'] ?>">
                                <?= htmlspecialchars($category['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <a href="./php/popular.php">Popular</a>
                <a href="./php/premium.php">Premium</a>
                <a href="./php/about.php">About</a>
            </div>


            <?php if ($isLoggedIn): ?>
                <div class="user-profile">
                    <div class="profile-dropdown">
                        <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($name); ?></span>
                        <div class="dropdown-menu">
                            <a href="./php/edit_profile.php">Edit Profile</a>
                            <a href="./php/submit_post.php">Submit Post</a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <a href="./php/admin_review.php">Review Posts</a>
                            <?php endif; ?>
                            <a href="./php/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <a href="./pages/login.html" class="btn btn-login">Login</a>
                    <a href="./pages/register.html" class="btn btn-register">Register</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    
   
    <section class="hero">
    <video autoplay muted loop playsinline class="hero-video">
        <source src="assets/videos/hero-bg.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    
    <div class="container">
        <h1>Share Your Thoughts With The World</h1>
        <p>Join our community of writers and readers. Discover amazing content, share your stories, and connect with like-minded people.</p>
        
    </div>
</section>


   <main class="container">
    <!-- Hero Slideshow -->
    <div class="slideshow">
        <div class="slideshow-inner">
            <!-- Slide 1 -->
            <div class="slide active" style="background-image: url('./assets/images/slide1.jpg')">
                <div class="slide-content">
                    <span class="slide-category">Writing</span>
                    <h2 class="slide-title">The Art of Storytelling in the Digital Age</h2>
                    <p class="slide-excerpt">Discover how modern writers are adapting ancient storytelling techniques to captivate today's digital audiences.</p>
                    <div class="slide-meta">
                        <div class="slide-author">
                            <img src="./assets/images/emma-thompson.jpg" alt="Emma Thompson" class="slide-author-avatar">
                            <span>Emma Thompson</span>
                        </div>
                        <span><i class="far fa-clock"></i> 5 min read</span>
                    </div>
                </div>
            </div>
            
            <!-- Slide 2 -->
            <div class="slide" style="background-image: url('./assets/images/slide2.jpg')">
                <div class="slide-content">
                    <span class="slide-category">Technology</span>
                    <h2 class="slide-title">How AI is Transforming Content Creation</h2>
                    <p class="slide-excerpt">Exploring the revolutionary tools that are changing how we write, design, and publish content online.</p>
                    <div class="slide-meta">
                        <div class="slide-author">
                            <img src="./assets/images/james-wilson.jpg" alt="James Wilson" class="slide-author-avatar">
                            <span>James Wilson</span>
                        </div>
                        <span><i class="far fa-clock"></i> 7 min read</span>
                    </div>
                </div>
            </div>
            
            <!-- Slide 3 -->
            <div class="slide" style="background-image: url('./assets/images/slide3.jpg')">
                <div class="slide-content">
                    <span class="slide-category">Travel</span>
                    <h2 class="slide-title">Digital Nomad Hotspots for 2025</h2>
                    <p class="slide-excerpt">The best cities around the world for remote workers, ranked by internet speed, community, and quality of life.</p>
                    <div class="slide-meta">
                        <div class="slide-author">
                            <img src="./assets/images/sarah-johnson.jpg" alt="Sarah Johnson" class="slide-author-avatar">
                            <span>Sarah Johnson</span>
                        </div>
                        <span><i class="far fa-clock"></i> 6 min read</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Slide Navigation -->
        <div class="slide-nav">
            <button class="slide-nav-btn prev"><i class="fas fa-chevron-left"></i></button>
            <button class="slide-nav-btn next"><i class="fas fa-chevron-right"></i></button>
        </div>
        
        <!-- Slide Dots -->
        <div class="slide-dots">
            <span class="slide-dot active"></span>
            <span class="slide-dot"></span>
            <span class="slide-dot"></span>
        </div>
    </div>
</main>
              
        <!-- Featured Authors Section -->
        <div class="authors-section">
            <div class="section-header">
                <h2 class="section-title">Featured Authors</h2>
            </div>
            
            <div class="authors-grid">
                <!-- Author 1 -->
                <div class="author-card">
                    <img src="./assets/images/sarah-johnson.jpg" alt="Author" class="author-avatar-large">
                    <h3 class="author-name">Maria Garcia</h3>
                    <p class="author-bio">Technology Writer & AI Enthusiast</p>
                    <div class="author-stats">
                        <div class="author-stat">
                            <span>24</span>
                            Posts
                        </div>
                        <div class="author-stat">
                            <span>3.2K</span>
                            Followers
                        </div>
                    </div>
                </div>
                
                <!-- Author 2 -->
                <div class="author-card">
                    <img src="./assets/images/james-wilson.jpg" alt="Author" class="author-avatar-large">
                    <h3 class="author-name">David Kim</h3>
                    <p class="author-bio">Travel Photographer & Blogger</p>
                    <div class="author-stats">
                        <div class="author-stat">
                            <span>42</span>
                            Posts
                        </div>
                        <div class="author-stat">
                            <span>8.7K</span>
                            Followers
                        </div>
                    </div>
                </div>
                
                <!-- Author 3 -->
                <div class="author-card">
                    <img src="./assets/images/emma-thompson.jpg" alt="Author" class="author-avatar-large">
                    <h3 class="author-name">Sophie Chen</h3>
                    <p class="author-bio">Food Critic & Recipe Developer</p>
                    <div class="author-stats">
                        <div class="author-stat">
                            <span>36</span>
                            Posts
                        </div>
                        <div class="author-stat">
                            <span>5.4K</span>
                            Followers
                        </div>
                    </div>
                </div>
                
                <!-- Author 4 -->
                <div class="author-card">
                    <img src="./assets/images/michael-brown.jpg" alt="Author" class="author-avatar-large">
                    <h3 class="author-name">Michael Brown</h3>
                    <p class="author-bio">Business Strategist & Consultant</p>
                    <div class="author-stats">
                        <div class="author-stat">
                            <span>18</span>
                            Posts
                        </div>
                        <div class="author-stat">
                            <span>2.9K</span>
                            Followers
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trending Tags Section -->
        <div class="tags-section">
            <div class="section-header">
                <h2 class="section-title">Trending Tags</h2>
                <a href="./php/category.php" class="view-all">Explore More <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="tags-cloud">
                <a href="./php/category.php" class="tag tag-lg">#Technology</a>
                <a href="./php/category.php" class="tag tag-lg">#Travel</a>
                <a href="./php/category.php" class="tag tag-md">#Food</a>
                <a href="./php/category.php" class="tag tag-lg">#Business</a>
                <a href="./php/category.php" class="tag tag-md">#Health</a>
                <a href="./php/category.php" class="tag tag-sm">#Design</a>
                <a href="./php/category.php" class="tag tag-md">#Photography</a>
                <a href="./php/category.php" class="tag tag-sm">#Education</a>
                <a href="./php/category.php" class="tag tag-sm">#Startup</a>
                <a href="./php/category.php" class="tag tag-md">#Productivity</a>
                <a href="./php/category.php" class="tag tag-sm">#AI</a>
                <a href="./php/category.php" class="tag tag-sm">#Marketing</a>
                <a href="./php/category.php" class="tag tag-md">#Finance</a>
                <a href="./php/category.php" class="tag tag-sm">#Writing</a>
                <a href="./php/category.php" class="tag tag-sm">#Cooking</a>
            </div>
        </div>
        
        <!-- Testimonials Section -->
        <div class="testimonials-section">
            <div class="testimonials-header">
                <h2>What Our Readers Say</h2>
                <p>Join thousands of satisfied readers who have transformed their knowledge and skills through our content.</p>
            </div>
            
            <div class="testimonials-grid">
                <!-- Testimonial 1 -->
                <div class="testimonial-card">
                    <p class="testimonial-text">Future Blog has completely changed how I consume content online. The quality of articles is unmatched, and I've learned so much from the premium content.</p>
                    <div class="testimonial-author">
                        <img src="./assets/images/james-wilson.jpg" alt="Reader" class="testimonial-avatar">
                        <div class="testimonial-author-info">
                            <h4>Alex Johnson</h4>
                            <p>Premium Member</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 2 -->
                <div class="testimonial-card">
                    <p class="testimonial-text">As a content creator myself, I'm constantly inspired by the depth and variety of topics covered here. It's my go-to resource for staying current in my field.</p>
                    <div class="testimonial-author">
                        <img src="./assets/images/sarah-johnson.jpg" alt="Reader" class="testimonial-avatar">
                        <div class="testimonial-author-info">
                            <h4>Sarah Williams</h4>
                            <p>Writer & Contributor</p>
                        </div>
                    </div>
                </div>
                
                <!-- Testimonial 3 -->
                <div class="testimonial-card">
                    <p class="testimonial-text">The community aspect sets Future Blog apart. The discussions in the comments are as valuable as the articles themselves. I've made real connections here.</p>
                    <div class="testimonial-author">
                        <img src="./assets/images/emma-thompson.jpg" alt="Reader" class="testimonial-avatar">
                        <div class="testimonial-author-info">
                            <h4>Emma Thompson</h4>
                            <p>Active Member</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stats Section -->
        <div class="stats-section">
            <div class="section-header">
                <h2 class="section-title">Our Impact in Numbers</h2>
            </div>
            
            <div class="stats-grid">
                <!-- Stat 1 -->
                <div class="stat-card">
                    <div class="stat-number">10,000+</div>
                    <div class="stat-label">Monthly Readers</div>
                </div>
                
                <!-- Stat 2 -->
                <div class="stat-card">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Published Articles</div>
                </div>
                
                <!-- Stat 3 -->
                <div class="stat-card">
                    <div class="stat-number">120+</div>
                    <div class="stat-label">Expert Contributors</div>
                </div>
                
                <!-- Stat 4 -->
                <div class="stat-card">
                    <div class="stat-number">95%</div>
                    <div class="stat-label">Satisfaction Rate</div>
                </div>
            </div>
        </div>
    
   <footer>
  <div class="container">
    <!-- Footer Top -->
    <div class="footer-top">
      <div class="footer-brand">
        <a href="#" class="logo">Future</a>
        <p class="footer-description">
          A premium blogging platform for creators and thinkers to share their ideas with the world.
        </p>
        <div class="footer-newsletter">
          <h4>Subscribe to our newsletter</h4>
          <form class="newsletter-form">
            <input type="email" placeholder="Your email address" required>
            <button type="submit">
              <i class="fas fa-paper-plane"></i>
            </button>
          </form>
        </div>
      </div>

      <div class="footer-links-grid">
        <!-- Column 1 -->
        <div class="footer-column">
          <h3 class="footer-title">Explore</h3>
          <ul class="footer-links">
            <li><a href="#"><i class="fas fa-chevron-right"></i> Home</a></li>
            <li><a href="./php/popular.php"><i class="fas fa-chevron-right"></i> Popular</a></li>
            <li><a href="./php/category.php"><i class="fas fa-chevron-right"></i> Categories</a></li>
            <li><a href="./php/premium.php"><i class="fas fa-chevron-right"></i> Premium</a></li>
            
          </ul>
        </div>

        <!-- Column 2 -->
        <div class="footer-column">
          <h3 class="footer-title">Company</h3>
          <ul class="footer-links">
            <li><a href="./php/about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
            <li><a href="./php/about.php"><i class="fas fa-chevron-right"></i> Contact</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i> Press</a></li>
            <li><a href="./php/category.php"><i class="fas fa-chevron-right"></i> Blog</a></li>
          </ul>
        </div>

        <!-- Column 3 -->
        <div class="footer-column">
          <h3 class="footer-title">Support</h3>
          <ul class="footer-links">
            <li><a href="./php/about.php"><i class="fas fa-chevron-right"></i> Help Center</a></li>
            <li><a href="./php/about.php"><i class="fas fa-chevron-right"></i> Community</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i> Guidelines</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i> Privacy Policy</a></li>
            <li><a href="#"><i class="fas fa-chevron-right"></i> Terms of Service</a></li>
          </ul>
        </div>

        <!-- Column 4 -->
        <div class="footer-column">
          <h3 class="footer-title">Connect</h3>
          <div class="footer-contact">
            <div class="contact-item">
              <i class="fas fa-map-marker-alt"></i>
              <span>123 Creative Street, Digital City</span>
            </div>
            <div class="contact-item">
              <i class="fas fa-envelope"></i>
              <span>hello@futureblog.com</span>
            </div>
            <div class="contact-item">
              <i class="fas fa-phone-alt"></i>
              <span>+1 (555) 123-4567</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Footer Bottom -->
    <div class="footer-bottom">
      <div class="footer-social">
        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
        <a href="#" class="social-icon"><i class="fab fa-pinterest-p"></i></a>
      </div>

      <div class="footer-legal">
        <span>&copy; 2025 Future Blog. All rights reserved.</span>
        <div class="legal-links">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Service</a>
          <a href="#">Cookie Policy</a>
        </div>
      </div>

      <div class="footer-backtotop">
        <a href="#" id="back-to-top">
          <i class="fas fa-arrow-up"></i> Back to Top
        </a>
      </div>
    </div>
  </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Slideshow functionality
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slide-dot');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    let currentSlide = 0;
    let slideInterval;

    // Function to show a specific slide
    function showSlide(index) {
        // Reset all slides
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        // Show the selected slide
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        currentSlide = index;
    }

    // Function for next slide
    function nextSlide() {
        currentSlide = (currentSlide + 1) % slides.length;
        showSlide(currentSlide);
    }

    // Function for previous slide
    function prevSlide() {
        currentSlide = (currentSlide - 1 + slides.length) % slides.length;
        showSlide(currentSlide);
    }

    // Start autoplay
    function startAutoplay() {
        slideInterval = setInterval(nextSlide, 5000); // Change slide every 5 seconds
    }

    // Stop autoplay
    function stopAutoplay() {
        clearInterval(slideInterval);
    }

    // Event listeners
    nextBtn.addEventListener('click', function() {
        nextSlide();
        stopAutoplay();
        startAutoplay();
    });

    prevBtn.addEventListener('click', function() {
        prevSlide();
        stopAutoplay();
        startAutoplay();
    });

    // Dot navigation
    dots.forEach((dot, index) => {
        dot.addEventListener('click', function() {
            showSlide(index);
            stopAutoplay();
            startAutoplay();
        });
    });

    // Make slides clickable (you'll need to add proper href attributes to each slide)
    slides.forEach(slide => {
        slide.addEventListener('click', function() {
            // You can add specific URLs for each slide here
            // For example: window.location.href = 'article1.html';
            console.log('Slide clicked - add your navigation logic here');
        });
    });

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            prevSlide();
            stopAutoplay();
            startAutoplay();
        } else if (e.key === 'ArrowRight') {
            nextSlide();
            stopAutoplay();
            startAutoplay();
        }
    });

    // Pause on hover
    const slideshow = document.querySelector('.slideshow');
    slideshow.addEventListener('mouseenter', stopAutoplay);
    slideshow.addEventListener('mouseleave', startAutoplay);

    // Initialize
    showSlide(0);
    startAutoplay();
});
</script>
</body>
</html>