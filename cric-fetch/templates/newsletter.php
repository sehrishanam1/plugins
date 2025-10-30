<?php
add_shortcode('newsletter_subscribe', 'newsletter_subscribe_shortcode');

function newsletter_subscribe_shortcode() {
    ob_start(); ?>

    <div class="newsletter-section">
        <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
            <label class="newsletter-title">Subscribe to our <span>NEWSLETTER</span></label>
            <div class="newsletter-input-wrap">
                <input type="email" placeholder="Enter Your Email" required>
                <button type="submit">Subscribe</button>
            </div>
        </form>
    </div>

    <?php
    return ob_get_clean();
}
?>
