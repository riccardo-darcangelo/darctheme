<?php 

/*
Template Name: Wordpress
*/

get_header(); ?>

<main class="mobile-dev">
    <section class="hero" id="wp-hero" style="background-color: #F6F6F6; background-image: url('<?php bloginfo('template_url'); ?>/assets/img/dots.png');">
        <div class="hero-wrapper" >
            <h1><bdi>Wordpress</bdi> 360GRAD</h1>
            <h2>Ich helfe Dir Rund um Deine Wordpress-Seite</h2>
            <a href="#" class="cta">Erfahre mehr</a>
        </div>
        <div class="hero-wrapper wp-hero-img">
            <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/wp-hero.png" alt="app development">
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
            <h3>So kann ich Ihnen helfen!</h3>
            <h4>Meine Leistungen</h4>
            <hr>
        </div>
        <div class="about-app">
                <div class="services-wrapper boxed-wrapper">
                    <div class="card card-boxed">
                        <h4><bdi>01</bdi> Website Entwicklung</h4>
                        <div class="text">
                            <p>
                                Neuentwicklung von Wordpress-Seiten & Weiterentwicklung bestehender Seiten.
                            </p>
                        </div>
                    </div>
                    <div class="card card-boxed">
                        <h4><bdi>02</bdi> Theme Entwicklung</h4>
                        <div class="text">
                            <p>
                                Entwicklung von Themes perfekt zugeschnitten auf Ihre Bedürfnisse, 
                                ausgelegt auf die beste User Experience.
                            </p>
                        </div>
                    </div>
                    <div class="card card-boxed">
                        <h4><bdi>03</bdi> Plugin Entwicklung</h4>
                        <div class="text">
                            <p>
                                Entwicklung maßgeschneideter Plugins und Weiterentwicklung bestehender Plugins.
                            </p>
                        </div>
                    </div>
                    <div class="card card-boxed">
                        <h4><bdi>04</bdi> WooCommerce</h4>
                        <div class="text">
                            <p>
                                Entwicklung neuer Shops & Weiterentwicklung bestehender Shops.
                            </p>
                        </div>
                    </div>
                    <div class="card card-boxed">
                        <h4><bdi>05</bdi> Wartung & Updates</h4>
                        <div class="text">
                            <p>
                                Wartung, Updates, Migration und Backups Ihrer Wordpress-Seite. Überprüfung und Anpassung der Cyper-Security.
                            </p>
                        </div>
                    </div>
                    <div class="card card-boxed">
                        <h4><bdi>06</bdi> SEO & Leistung</h4>
                        <div class="text">
                            <p>
                                Beratung, Überprüfung und technische Verbesserung für eine bessere Performance und Top-Ranking Ihrer Wordpress-Seite.
                            </p>
                        </div>
                    </div>
                    <div class="card card-boxed">
                        <h4><bdi>07</bdi> Elementor</h4>
                        <div class="text">
                            <p>
                                Support & Entwicklung für Wordpress-Seiten, die mit dem Elementor Pagebuilder erstellt wurden.
                            </p>
                        </div>
                    </div>
                    <div class="card card-boxed">
                        <h4><bdi>08</bdi> Server & Performance</h4>
                        <div class="text">
                            <p>
                                Konfiguration & Administration Ihrer Server. Umzug, Neuausrichtung und Entwicklung neuer Serverarchitektur.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <!-- Why WP -->
    <section class="section">
        <div class="section-title">
            <h3>Vorteile von Wordpress!</h3>
            <hr>
            <div class="about-app">
                <div class="services-wrapper">
                    <div class="card img-card">
                        <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/usability.webp" alt="usability">
                        <div class="card-text-wrapper">
                            <h4>Usability</h4>
                            <div class="text">
                                <p>
                                    Wordpress ist schnell aufgesetzt und hat eine nutzerfreundliche Benutzeroberfläche.
                                    So lassen sich einfach, schnell und ohne Programmierung Inhalte erstellen, bearbeiten und verwalten.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card img-card">
                        <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/flexibility.webp" alt="flexibilität">
                        <div class="card-text-wrapper">
                            <h4>Flexibilität</h4>
                            <div class="text">
                                <p>
                                    Wordpress eignet sich für "fast" jeden Usecase. Egal ob als kleine Blogseite, Unternehmenswebsite
                                    oder als großer Online-Shop.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card img-card">
                        <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/community.webp" alt="community">
                        <div class="card-text-wrapper">
                            <h4>Community Support</h4>
                            <div class="text">
                                <p>
                                    Wordpress hat eine große internationale Community! Egal ob Plugin, Theme oder Support, 
                                    bei jedem Fall finden Sie passende Unterstützung.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="card img-card">
                        <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/performance.webp" alt="Performance">
                        <div class="card-text-wrapper">
                            <h4>Performence & SEO</h4>
                            <div class="text">
                                <p>
                                    WordPress ist sowohl aus SEO- als auch aus Performance-Sicht ein 
                                    hervorragendes CMS - dank schlanken, suchmaschinenfreundlichen Codes.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How Work works -->
    <section class="section">
        <div class="section-title">
            <h3>Wie läuft ein Projekt ab?</h3>
            <hr>
        </div>
        <div class="about-app work-steps">
            <!-- <div class="steps-img">
                <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/wp-mockup-2.png" alt="Wie läuft ein Projekt ab?">
            </div> -->
            <div class="steps-wrapper">
                <div>
                    <!-- Step 1 -->
                    <div class="outer-step" id="step-1">
                        <div class="card steps border-left border-bottom">
                            <h4>Kennenlernen & Planung</h4>
                            <div class="text">
                                <p>
                                    Mit einem ersten kostenlosen Gespräch, besprechen wir Ihre Ziele, Anforderungen und Lösungsmöglichkeiten.
                                    Auf Basis dieses Gesprächs erstelle ich ein Angebot, passend für Ihre Bedürfnisse.
                                    <br>
                                </p>
                                <a href="#">Mehr zum Erstgesrpäch <i class="fa-light fa-arrow-right"></i></a>
                            </div>
                        </div>
                        <div class="steps-img border-bottom">
                            <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/step-1.jpg" alt="Wie läuft ein Projekt ab?">
                        </div>
                    </div>
                    <!-- Step 2 -->
                    <div class="outer-step" id="step-2">
                        <div class="steps-img border-bottom">
                            <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/step-2.jpg" alt="Wie läuft ein Projekt ab?">
                        </div>
                        <div class="card steps border-right border-bottom">
                            <h4>Design & Entwicklung</h4>
                            <div class="text">
                                <p>
                                    Auf Basis Ihrer Anforderungen erstelle ich erste Designs, welche auf die bestmögliche User Experience Ihrer Zielgruppe ausgerichtet ist.
                                    <br>
                                    Sind Sie mit dem Design zufrieden beginnt die eigentliche Entwicklung & Programmierung Ihrer Seite auf meinem Entwicklungssystem.
                                </p>
                            </div>
                            <a href="#">Mehr zur Designarbeit <i class="fa-light fa-arrow-right"></i></a>
                        </div>
                    </div>
                    <!-- Step 3 -->
                    <div class="outer-step" id="step-3">
                        <div class="card steps border-left border-bottom">
                            <h4>Anpassungen & User Testings</h4>
                            <div class="text">
                                <p>
                                    Ist der erste Prototyp Ihrer Seite fertig, führe ich gemeinsam mit Ihnen und ausgewählten Usern Testings durch, um Fehler schnell
                                    zu Identifiziern und zu bereinigen.
                                </p>
                            </div>
                        </div>
                        <div class="steps-img border-bottom">
                            <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/step-3.jpg" alt="Wie läuft ein Projekt ab?">
                        </div>
                    </div>
                    <!-- Step 4 -->
                    <div class="outer-step" id="step-4">
                        <div class="steps-img border-bottom">
                            <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/step-4.jpg" alt="Wie läuft ein Projekt ab?">
                        </div>
                        <div class="card steps border-right border-bottom">
                            <h4>Launch!</h4>
                            <div class="text">
                                <p>
                                    Fallen alle Test positiv aus stelle ich die Webseite auf Ihrem Live-System online, somit ist die Seite nun für jeden öffentlich zugänglich.
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Step 5 -->
                    <div class="outer-step" id="step-5">
                        <div class="card steps">
                            <h4>Regelmäßiger Service</h4>
                            <div class="text">
                                <p>
                                    Für wiederkehrende Leistungen vereinbaren wir einen regulären Festpreis. Beispiele sind Service Retainer, Wartungspakete und SEO-/Speed-Audits.
                                </p>
                            </div>
                        </div>
                        <div class="steps-img">
                            <img src="<?php bloginfo('template_url'); ?>/assets/img/wordpress-template/step-5.jpg" alt="Wie läuft ein Projekt ab?">
                        </div>
                    </div>
                </div>
            </div>    
        </div>
    </section>
</main>

<?php get_footer(); ?>