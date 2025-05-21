<footer class="footer bg-white position-relative">
            <div class="footer-middle">
                <div class="container position-static">
                    <div class="row">
                        <div class="col-lg-2 col-sm-6 pb-2 pb-sm-0 d-flex align-items-center">
                            <div class="widget m-b-3">
                                <img src="<?php echo base_url('assets/front/assets/images/logo-no-background.png'); ?>" alt="Logo" width="202"
                                    height="54" class="logo-footer">
                            </div><!-- End .widget -->
                        </div><!-- End .col-lg-3 -->

                        <div class="col-lg-3 col-sm-6 pb-4 pb-sm-0">
                            <div class="widget mb-2">
                                <h4 class="widget-title mb-1 pb-1">Posjetite nas</h4>
                                <ul class="contact-info">
                                    <li>
                                        <span class="contact-info-label">Adresa:</span>Gojlanska ulica 47, 10040 Zagreb
                                    </li>
                                    <li>
                                        <span class="contact-info-label">Tel:</span><a href="tel:+385912001500">+385 912001500</a>
                                    </li>
                                    <li>
                                        <span class="contact-info-label">Email:</span> <a
                                            href="mailto:info@profilinezagreb.hr">info@ograda.hr</a>
                                    </li>
                                    <li>
                                        <span class="contact-info-label">Radno vrijeme:</span>
                                        Ponedjeljak - Petak / 08:00 - 16:00 <br> Subota - Nedjelja - Zatvoreno
                                    </li>
                                </ul>
                                <div class="social-icons">
                                    <a href="#" class="social-icon social-facebook icon-facebook" target="_blank"
                                        title="Facebook"></a>
                                    <a href="#" class="social-icon social-twitter icon-twitter" target="_blank"
                                        title="Twitter"></a>
                                    <a href="#" class="social-icon social-linkedin fab fa-linkedin-in" target="_blank"
                                        title="Linkedin"></a>
                                </div><!-- End .social-icons -->
                            </div><!-- End .widget -->
                        </div><!-- End .col-lg-3 -->

                        <div class="col-lg-3 col-sm-6 pb-2 pb-sm-0 links-for-users">
                            <div class="widget nav-menu-footer">
                                <h4 class="widget-title pb-1">Linkovi za korisnike</h4>

                                <ul class="links">
                                    <li><i aria-hidden="true" class="fas fa-play"></i><a href="<?= site_url('/about-us'); ?>">O nama</a></li>
                                    <li><i aria-hidden="true" class="fas fa-play"></i><a href="<?= site_url('/contact'); ?>">Kontaktirajte nas</a></li>
                                    <li><i aria-hidden="true" class="fas fa-play"></i><a href="<?= site_url('/products'); ?>">Naše usluge</a></li>
                                    <li><i aria-hidden="true" class="fas fa-play"></i><a href="<?= site_url('/privacy-policy'); ?>">Izjava o privatnosti</a></li>
                                    <li><i aria-hidden="true" class="fas fa-play"></i><a href="<?= site_url('/terms-of-use'); ?>">Uvjeti korištenja</a></li>
                                 </ul>
                            </div><!-- End .widget -->
                        </div><!-- End .col-lg-3 -->

                        <div class="col-lg-4 col-sm-6 pb-0">
                            <div class="widget widget-newsletter mb-1 mb-sm-3">
                                <h4 class="widget-title">Novosti</h4>

                                <p class="mb-2">Budite u tijeku s našim najnovijim vijestima, primajte ponude i još mnogo toga:</p>
                                <!-- <form action="#" class="d-flex mb-0 w-100">
                                    <input type="email" class="form-control mb-0 bg-brown" placeholder="Email"
                                        required="">

                                    <input type="submit" class="btn shadow-none" value="OK">
                                </form> -->
                                <form action="<?= base_url('subscribe-newsletter'); ?>" method="post" class="d-flex mb-0 w-100">
                                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
                                    value="<?= $this->security->get_csrf_hash(); ?>">

                                    <input type="email" name="email" class="form-control mb-0 bg-brown" placeholder="Email" required="">
                                    <input type="submit" class="btn shadow-none" value="OK">
                                </form>
                            </div><!-- End .widget -->
                        </div><!-- End .col-lg-3 -->
                    </div><!-- End .row -->
                </div><!-- End .container -->
            </div><!-- End .footer-middle -->

            <div class="container">
                <div class="footer-bottom d-sm-flex align-items-center bg-white">
                    <div class="footer-left">
                        <h5 class="footer-copyright text-brown">Ograda.hr  © 2025 Sva prava pridržana</h5>
                    </div>

                    <div class="footer-right ml-auto mt-1 mt-sm-0">
                        <div class="payment-icons bg-brown">
                            <span class="payment-icon visa"
                                style="background-image: url('<?= base_url('assets/front/assets/images/payments/payment-visa.svg') ?>')"></span>
                            <span class="payment-icon paypal"
                                style="background-image: url('<?= base_url('assets/front/assets/images/payments/payment-paypal.svg') ?>')"></span>
                            <span class="payment-icon stripe"
                                style="background-image: url('<?= base_url('assets/front/assets/images/payments/payment-stripe.png') ?>')"></span>
                            <span class="payment-icon verisign"
                                style="background-image:  url('<?= base_url('assets/front/assets/images/payments/payment-verisign.svg') ?>')"></span>
                        </div>
                    </div>
                </div>
            </div><!-- End .footer-bottom -->
        </footer>
    </div><!-- End .page-wrapper -->

    <div class="loading-overlay">
        <div class="bounce-loader">
            <div class="bounce1"></div>
            <div class="bounce2"></div>
            <div class="bounce3"></div>
        </div>
    </div>

    <div class="mobile-menu-overlay"></div><!-- End .mobil-menu-overlay -->

    <div class="mobile-menu-container">
        <div class="mobile-menu-wrapper">
            <span class="mobile-menu-close"><i class="fa fa-times"></i></span>
            <nav class="mobile-nav">
                <ul class="mobile-menu">
                    <li><a href="<?= site_url('/'); ?>">Home</a></li>
                    <li>
                        <a href="<?= site_url('/about-us'); ?>">O nama</a>
                    </li>
                    <li>
                        <a href="<?= site_url('/products'); ?>">Ograde</a>
                    </li>
                    <li>
                        <a href="<?= site_url('/contact'); ?>">Kontakt</a>
                    </li>

                    <!-- <li>
                        <a href="#">Pages<span class="tip tip-hot">Hot!</span></a>
                        <ul>
                            <li>
                                <a href="wishlist.html">Wishlist</a>
                            </li>
                            <li>
                                <a href="cart.html">Shopping Cart</a>
                            </li>
                            <li>
                                <a href="checkout.html">Checkout</a>
                            </li>
                            <li>
                                <a href="dashboard.html">Dashboard</a>
                            </li>
                            <li>
                                <a href="login.html">Login</a>
                            </li>
                            <li>
                                <a href="forgot-password.html">Forgot Password</a>
                            </li>
                        </ul>
                    </li> -->
                </ul>

                <!-- <ul class="mobile-menu mt-2 mb-2">
                    <li class="border-0">
                        <a href="#">
                            Special Offer!
                        </a>
                    </li>
                    <li class="border-0">
                        <a href="#" target="_blank" rel="noopener" title="buy-porto">
                            Buy Porto!
                            <span class="tip tip-hot">Hot</span>
                        </a>
                    </li>
                </ul> -->

                <!-- <ul class="mobile-menu">
                    <li><a href="login.html">My Account</a></li>
                    <li><a href="contact.html">Contact Us</a></li>
                    <li><a href="blog.html">Blog</a></li>
                    <li><a href="wishlist.html">My Wishlist</a></li>
                    <li><a href="cart.html">Cart</a></li>
                    <li><a href="login.html" class="login-link">Log In</a></li>
                </ul> -->
            </nav><!-- End .mobile-nav -->

            <form class="search-wrapper mb-2" action="#">
                <input type="text" class="form-control mb-0" placeholder="Search..." required />
                <button class="btn icon-search text-white bg-transparent p-0" title="submit" type="submit"></button>
            </form>

            <div class="social-icons">
                <a href="https://www.facebook.com/p/Ogradahr-61567005054532/" class="social-icon social-facebook icon-facebook" target="_blank" title="facebook">
                </a>
                <a href="#" class="social-icon social-twitter icon-twitter" target="_blank" title="twitter">
                </a>
                <a href="#" class="social-icon social-instagram icon-instagram" target="_blank" title="instagram">
                </a>
            </div>
        </div><!-- End .mobile-menu-wrapper -->
    </div><!-- End .mobile-menu-container -->
    

    <!-- <div class="sticky-navbar">
        <div class="sticky-info">
            <a href="<?= site_url('/'); ?>">
                <i class="icon-home"></i>Home
            </a>
        </div>
        <div class="sticky-info">
            <a href="<?= site_url('/products'); ?>" class="">
                <i class="icon-bars"></i>Kategorije
            </a>
        </div>
        <div class="sticky-info">
            <a href="#" class="">
                <i class="icon-wishlist-2"></i>Wishlist
            </a>
        </div>
        <div class="sticky-info">
            <a href="#" class="">
                <i class="icon-user-2"></i>Account
            </a>
        </div>
        <div class="sticky-info">
            <a href="#" class="">
                <i class="icon-shopping-cart position-relative">
                    <span class="cart-count badge-circle">3</span>
                </i>Cart
            </a>
        </div>
    </div> -->

    <!-- <div class="newsletter-popup mfp-hide bg-img" id="newsletter-popup-form"
        style="background: #f1f1f1 no-repeat center/cover url(assets/front/assets/images/photo-11-2.png)">
        <div class="newsletter-popup-content">
            <img src="<?php echo base_url('assets/front/assets/images/demoes/demo42/main-logo.png'); ?>" alt="Logo" class="logo-newsletter" width="111" height="44">
            <h2>Subscribe to newsletter</h2>

            <p>
                Budite u tijeku s našim najnovijim vijestima, primajte ponude i još mnogo toga.
            </p>

            <form action="#">
                <div class="input-group">
                    <input type="email" class="form-control" id="newsletter-email" name="newsletter-email"
                        placeholder="Email" required />
                    <input type="submit" class="btn btn-primary" value="Prijavi se" />
                </div>
            </form>
            <div class="newsletter-subscribe">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" class="custom-control-input" value="0" id="show-again" />
                    <label for="show-again" class="custom-control-label">
                        Ne prikazuj više ovaj skočni prozor
                    </label>
                </div>
            </div>
        </div> End .newsletter-popup-content -->

       <!--tton title="Close (Esc)" type="button" class="mfp-close">
            ×
        </button>
    </div>End .newsletter-popup -->

    <a id="scroll-top" href="#top" title="Top" role="button"><i class="icon-angle-up"></i></a>

    <!-- Plugins JS File -->
       
    
   
    <script src="<?php echo base_url('assets/front/assets/js/popper.min.js'); ?>" ></script>
    <script src="<?php echo base_url('assets/front/assets/js/bootstrap.bundle.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/front/assets/js/optional/isotope.pkgd.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/front/assets/js/plugins.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/front/assets/js/jquery.appear.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/front/assets/js/jquery.plugin.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/front/assets/js/plugins/jquery-numerator.min.js')?>"></script>
    <script src="<?php echo base_url('assets/front/assets/js/nouislider.min.js') ?>"></script>
    <script src="<?php echo base_url('assets/front/assets/js/countdown.js') ?>"></script>

    <!-- Main JS File -->
    <script src="<?php echo base_url('assets/front/assets/js/main.min.js'); ?>"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const navMenu = document.querySelectorAll("li.nav-menu");

        
        let currentPath = window.location.pathname;
        if (currentPath.length > 1) {
            currentPath = currentPath.replace(/\/$/, ""); 
        }

        navMenu.forEach(item => {
            const linkElement = item.querySelector("a");
            if (!linkElement) return;

            
            let linkPath = new URL(linkElement.href, window.location.origin).pathname;
            if (linkPath.length > 1) {
                linkPath = linkPath.replace(/\/$/, ""); 
            }

            //console.log("Comparing:", currentPath, "vs", linkPath); 

            
            if (currentPath === linkPath || (linkPath !== "/" && currentPath.startsWith(linkPath))) {
                item.classList.add("active");
            }

            item.addEventListener("click", function () {
                navMenu.forEach(li => li.classList.remove("active"));
                this.classList.add("active");
            });
        });
    });

</script>
<script>
    $(document).ready(function () {
        <?php if ($this->session->flashdata('modal_message')): ?>
            var messageData = <?= json_encode($this->session->flashdata('modal_message')) ?>;
            if (messageData) {
                $("#modalFlash").html(messageData.text);
                $(".modal-body").removeClass("bg-success bg-danger").addClass("bg-" + messageData.type);
                $("#messageModal").modal("show");
            }
        <?php endif; ?>
    });
</script>
<script>
    $(document).ready(function () {
        var stickySections = $('.header-top, .top-notice, .content-category, .top-notice.mobile-notice');
        var displayonStuck = $('.category-left, .category-right');

        $(window).scroll(function () {
            if ($(window).scrollTop() > 50) { 
                stickySections.addClass('stuck');
                displayonStuck.removeClass('d-none');
            } else {
                stickySections.removeClass('stuck');
                displayonStuck.addClass('d-none');
            }
        });
    });
</script>

<?php
// Include the api_codes.php file to get the webhook URL and customization options
include_once(APPPATH . '../api_codes.php');
?>

<!-- Custom CSS for N8N Chat Widget -->
<style>
:root {
    --chat--color-primary: #333333; /* Dark gray/charcoal as primary color */
    --chat--color-primary-shade-50: #28282a; /* Slightly darker shade */
    --chat--color-primary-shade-100: #202022; /* Even darker shade */
    --chat--color-secondary: #679b45; /* Green color for user messages */
    --chat--color-secondary-shade-50: #5a8a3d; /* Slightly darker shade */
    --chat--color-white: #ffffff;
    --chat--color-light: #f2f4f8;
    --chat--color-light-shade-50: #e6e9f1;
    --chat--color-light-shade-100: #c2c5cc;
    --chat--color-medium: #d2d4d9;
    --chat--color-dark: #101330;
    --chat--color-disabled: #777980;
    --chat--color-typing: #404040;

    --chat--toggle--size: 60px; /* Slightly smaller toggle button */
    --chat--message--bot--background: #f4f4f4; /* Light gray background for bot messages */
    --chat--message--bot--color: #333333; /* Dark text for bot messages */
    --chat--heading--font-size: 1.6em; /* Slightly smaller heading */
}
</style>

<!-- N8N Chat Widget -->
<script type="module">
    import { createChat } from 'https://cdn.jsdelivr.net/npm/@n8n/chat/dist/chat.bundle.es.js';

    // Create the chat widget with the customization options
    createChat({
        webhookUrl: '<?php echo $n8n_chat_webhook_url; ?>',
        defaultLanguage: '<?php echo $n8n_chat_options['language']; ?>',
        initialMessages: <?php echo json_encode($n8n_chat_options['initialMessages']); ?>,
        i18n: <?php echo json_encode($n8n_chat_options['i18n']); ?>,
        pollingConfig: {
            // Disable automatic polling which causes frequent executions
            enabled: false,
            // If needed, you can set a very long interval instead of disabling completely
            // interval: 300000 // 5 minutes in milliseconds
        },
        chatInputKey: 'chatInput', // Specifies the key used for the message sent to n8n
        chatSessionKey: 'sessionId', // Specifies the key used for the session ID
        sessionPollingEnabled: false, // Disable session polling completely
        loadPreviousSession: false, // Explicitly prevent loading previous sessions
        showWelcomeScreen: <?php echo json_encode($n8n_chat_options['showWelcomeScreen']); ?>
    });
</script>
<!-- End N8N Chat Widget -->

</body>
</html>


