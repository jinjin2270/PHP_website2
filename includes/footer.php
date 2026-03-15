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
                        <li><a href="../php/popular.php"><i class="fas fa-chevron-right"></i> Popular</a></li>
                        <li><a href="../php/category.php"><i class="fas fa-chevron-right"></i> Categories</a></li>
                        <li><a href="../php/premium.php"><i class="fas fa-chevron-right"></i> Premium</a></li>
                        
                    </ul>
                </div>

                <!-- Column 2 -->
                <div class="footer-column">
                    <h3 class="footer-title">Company</h3>
                    <ul class="footer-links">
                        <li><a href="../php/about.php"><i class="fas fa-chevron-right"></i> About Us</a></li>
                        <li><a href="../php/about.php"><i class="fas fa-chevron-right"></i> Contact</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Press</a></li>
                        <li><a href="../php/category.php"><i class="fas fa-chevron-right"></i> Blog</a></li>
                    </ul>
                </div>

                <!-- Column 3 -->
                <div class="footer-column">
                    <h3 class="footer-title">Support</h3>
                    <ul class="footer-links">
                        <li><a href="../php/about.php"><i class="fas fa-chevron-right"></i> Help Center</a></li>
                        <li><a href="../php/about.php"><i class="fas fa-chevron-right"></i> Community</a></li>
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


<!-- <script>
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".like-btn").forEach(function (button) {
    button.addEventListener("click", function (e) {
      e.preventDefault();
      
      const form = this.closest("form");
      const formData = new FormData(form);

      fetch(form.action, {
        method: "POST",
        body: formData
      })
      .then(response => {
        if (response.redirected) {
          window.location.href = response.url;
        }
      });
    });
  });
});
</script> -->

</body>
</html>