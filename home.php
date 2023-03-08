<?php
/**
 *	The template for dispalying the homepage.
 *
 *	@package WordPress
 *	@subpackage DarcTheme
 */
?>
<?php get_header(); ?>
<div class="home">
    <section class="hero" style="background-image: url(<?php echo get_theme_mod('homepage_hero_feature_img'); ?>);">
        <div class="hero-wrapper">
            <h1>Hello World!<br> I'm <bdi>Riccardo</bdi></h1>
            <h2>Full-Stack Entwickler <br> & UX-UI-Designer</h2>
            <a href="#" class="cta">Erfahre mehr</a>
        </div>
        <div class="scroll-down-indicator">
            <div class="field">
                <div class="mouse"></div>
            </div>
        </div>
    </section>

    <!-- Services -->
    <section class="section">
        <div class="section-title">
            <h3><b>Meine</b> Leistungen</h3>
            <hr>
            <div class="services-wrapper">
                <div class="card">
                    <div class="service-icon">
                        <img src="<?php bloginfo('template_url'); ?>/assets/icons/dev_light.webp" alt="development">
                    </div>
                    <h4>Web-, App- & Softwareentwicklung</h4>
                    <div class="text">
                        <p>Ich entwickle Deine Webseite, App oder Software mit modernster Technologie.</p>
                    </div>
                    <a href="#">Erfahre mehr <i class="fa-light fa-arrow-right"></i></a>
                </div>
                <div class="card">
                    <div class="service-icon">
                        <img src="<?php bloginfo('template_url'); ?>/assets/icons/design_light.png" alt="design">
                    </div>
                    <h4>UX & UI Design</h4>
                    <div class="text">
                        <p>Meine Designs sind stets Human-Centered ausgelegt auf die bestmögliche User-Experince.</p>
                    </div>
                    <a href="#">Erfahre mehr <i class="fa-light fa-arrow-right"></i></a>
                </div>
                <div class="card">
                    <div class="service-icon">
                        <img src="<?php bloginfo('template_url'); ?>/assets/icons/wordpress_light.webp" alt="Wordpress">
                    </div>
                    <h4>Wordpress</h4>
                    <div class="text">
                        <p>Ich entwickle dein Theme oder Plugins ausgelegt auf beste Performance.</p>
                    </div>
                    <a href="#">Erfahre mehr <i class="fa-light fa-arrow-right"></i></a>
                </div>
                <div class="card">
                    <div class="service-icon">
                        <img src="<?php bloginfo('template_url'); ?>/assets/icons/ecommerce_light.webp" alt="eCommerce">
                    </div>
                    <h4>eCommerce</h4>
                    <div class="text">
                        <p>Du möchtest deinem eigenen Webshop verkaufen? Ich entwickle die passende eCommerce-Lösungen.</p>
                    </div>
                    <a href="#">Erfahre mehr <i class="fa-light fa-arrow-right"></i></a>
                </div>
                <div class="card">
                    <div class="service-icon">
                        <img src="<?php bloginfo('template_url'); ?>/assets/icons/corporate_light.webp" alt="Corporate Design">
                    </div>
                    <h4>Corporate Design & Identity</h4>
                    <div class="text">
                        <p>Ich helfe dir bei der Entwicklung deines Unternehmensdesign und Unternehmensidentität.</p>
                    </div>
                    <a href="#">Erfahre mehr <i class="fa-light fa-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </section>

    <!-- About -->
    <section class="section">
        <div class="about-wrapper">
            <div class="about-img">
                <div class="img-orange-border"></div>
                <img src="<?php bloginfo('template_url'); ?>/assets/img/about_me_cropped.png" alt="Riccardo D'Arcangelo">
            </div>
            <div class="about-text">
                <div class="section-title">
                    <h3><b>Über</b> Mich</h3>
                    <hr>
                </div>

                <p>
                Ich bin Riccardo, Designern und Fullstack-Entwicklern aus Augsburg. Ich bin spezialisiert auf die Entwicklung von Webseiten, Webservices und modernen Apps. Meine Motivation die neuste Technik mit modernem Design zu kombinieren. Neben meiner klassischen Tätigkeit unterstütze ich meine Kunden auch beim entwerfen eines Corporate Design und einer Corporate Identity. Außerdem unterstütze ich auch bei der Entwicklung der Social Media Kanäle.
                <br><br>
                Als Designer und Entwickler in Augsburg bin ich für dich ein zuverlässiger Ansprechpartner und finde individuelle Lösungen für deinen Erfolg. Unverwechselbar und effektiv: Ich bringen dein Business nach vorn!
                </p>
            </div>
        </div>
    </section>

    <!-- My Skills -->
    <section class="section skills" style="background-image: url('<?php bloginfo('template_url'); ?>/assets/img/designer.jpg');">
        <div class="section-title outer-skills" style="position: relative;">
            <h3>“ Der Unterschied von Design zu Kunst ist, Design hat immer einer Funktion ”</h3>
            <div class="skills-wrapper">
                <div class="skill card">
                    <h4>UX/UI-Design</h4>
                    <p>
                    Design beschränkt sich nicht nur auf das Aussehen, sondern ist auch abhängig von der Nutzererfahrung. Ein gutes Design lenkt den Nutzer intuitiv durch das Produkt und an sein Ziel.
                    <br> <br>
                    „Keep it simple“ und lass das Design für dich Sprechen.
                    </p>
                    <br>
                    <h4>Alles ist Kopfsache!</h4>
                    <p>
                    Jedes Design und jedes Element erzeugen Bilder und Assoziationen im Kopf des Betrachters. Beim Neurodesign mache ich mir genau das zu Nutze.
                    <br> <br>
                    Durch Farben, Formen und Kompositionen erzeuge ich mit meinen Designs echte Gefühle und Verbundenheit zu Deinem Produkt.
                    </p>
                </div>
                <div class="skill card">
                    <h4>Das benötigt ein gutes Design</h4>
                    <div class="skill-lvl">
                        <label>Farb- & Formverständnis</label>
                        <div class="lvl"><div id="lvl-color"></div></div>
                    </div>
                    <div class="skill-lvl">
                        <label>Microtransaktions</label>
                        <div class="lvl"><div id="lvl-micro"></div></div>
                    </div>
                    <div class="skill-lvl">
                        <label>Simple & präzise Nutzerführung</label>
                        <div class="lvl"><div id="lvl-user"></div></div>
                    </div>
                    <div class="skill-lvl">
                        <label>User Experience</label>
                        <div class="lvl"><div id="lvl-ux"></div></div>
                    </div>
                    <div class="skill-lvl">
                        <label>Liebe zum Detail</label>
                        <div class="lvl"><div id="lvl-detail"></div></div>
                    </div>
                </div>
                
            </div>
        </div>
    </section>

    <!-- Portfolio -->
    <section class="portfolio">
        <div class="section-title">
            <h3><b>Mein</b> Portfolio</h3>
            <hr>
        </div>
        <div>
            <div class="post-wrapper">
            <?php while ( have_posts() ) : the_post(); // standard WordPress loop. ?>

                <article class="portfolio-post" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <a href="<?php the_permalink();?>">
                        <div class="post-img">
                            <?php the_post_thumbnail(); ?>
                        </div>
                        <div class="post-content">
                            <div class="inner-post-content">
                                <h4><?php the_title(); ?></h4>
                                <?php the_category(); ?>
                                <hr>
                            </div>
                        </div>
                    </a>
                </article>

            <?php endwhile; // end of the loop. ?>
            </div>
        </div>
    </section>
</div>
<?php get_footer(); ?>