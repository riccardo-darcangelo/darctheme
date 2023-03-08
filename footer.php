<footer>

    <!-- Contact -->
    <section class="footer-contact">
        <div class="contact-wrapper">
            <div class="contact-inner">
                <?php echo do_shortcode('[contact-form-7 title="Kontaktformular 1"]'); ?>
            </div>
            <div class="contact-inner">
            <h3><b>Noch Fragen?</b> Sei nicht schüchtern!</h3>
                <div class="social">
                    <div class="social-links">
                        <a href="<?php echo get_theme_mod('footer_two_social_one_link'); ?>" target="__blank"><i class="fa-brands fa-instagram"></i></a>
                        <a href="<?php echo get_theme_mod('footer_two_social_two_link'); ?>" target="__blank"><i class="fa-brands fa-behance"></i></a>
                        <a href="<?php echo get_theme_mod('footer_two_social_three_link'); ?>" target="__blank"><i class="fa-brands fa-linkedin-in"></i></a>
                    </div>  
                </div>
                <div style="margin-top: 2rem;">
                    <div class="contact-info">
                        <label>Mo. - Fr. 8.00 - 18.00 Uhr</label>
                    </div>
                    <div class="contact-info">
                        <a href="tel:+491728941743">0172 8941740</a>
                    </div>
                    <div class="contact-info">
                        <a href="mailto:info@darcdesign.de">info@darcdesign.de</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="copyright" id="copyright">
        <div class="copyright-wrapper">
            <div class="footer-nav">
                <?php wp_nav_menu( array( 'theme_location' => 'footer-law' ) ); ?>
            </div>
            <label>Copyright &copy; <?php echo date("Y"); ?> DARCDESIGN</label>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

</body>
</html>