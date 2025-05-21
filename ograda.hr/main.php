<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title><?php echo $title ?? 'Profiline'; ?></title>

    <meta name="keywords" content="<?= isset($keywords) ? $keywords : 'default, keywords, here'; ?>" />
    <meta name="description" content="Profi Line Zagreb | Ograde | Metalne kontrukcije">
    <meta name="author" content="Profi Line">
    <meta name="csrf-token" content="<?= $this->security->get_csrf_hash(); ?>">


    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo base_url('assets/front/assets/images/icons/cropped-favicon-192x192.png'); ?>">
    <link rel="preload" href="<?php echo base_url('assets/front/assets/vendor/fontawesome-free/webfonts/fa-regular-400.woff2'); ?>" as="font" type="font/woff2"
        crossorigin="anonymous">
    <link rel="preload" href="<?php echo base_url('assets/front/assets/vendor/fontawesome-free/webfonts/fa-solid-900.woff2'); ?>" as="font" type="font/woff2"
        crossorigin="anonymous">
    <link rel="preload" href="<?php echo base_url('assets/front/assets/vendor/fontawesome-free/webfonts/fa-brands-400.woff2'); ?>" as="font" type="font/woff2"
        crossorigin="anonymous">

    <!-- Google Fonts - Direct link instead of webfont.js -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Google Analytics snippet added by Site Kit -->
    <script src="https://www.googletagmanager.com/gtag/js?id=GT-NF7F4CMQ" id="google_gtagjs-js" async></script>
    <script id="google_gtagjs-js-after">
    window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}
    gtag("set","linker",{"domains":["ograda.hr"]});
    gtag("js", new Date());
    gtag("set", "developer_id.dZTNiMT", true);
    gtag("config", "GT-NF7F4CMQ");
    gtag("config", "AW-16729942608");
    window._googlesitekit = window._googlesitekit || {}; window._googlesitekit.throttledEvents = []; window._googlesitekit.gtagEvent = (name, data) => { var key = JSON.stringify( { name, data } ); if ( !! window._googlesitekit.throttledEvents[ key ] ) { return; } window._googlesitekit.throttledEvents[ key ] = true; setTimeout( () => { delete window._googlesitekit.throttledEvents[ key ]; }, 5 ); gtag( "event", name, { ...data, event_source: "site-kit" } ); }
    </script>

    <!-- Plugins CSS File -->
    <link rel="stylesheet" href="<?php echo base_url('assets/front/assets/css/bootstrap.min.css'); ?>">

    <!-- Main CSS File -->
    <link rel="stylesheet" href="<?php echo base_url('assets/front/assets/css/demo42.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/front/assets/css/demo23.min.css'); ?>">
    <link rel="stylesheet" href="<?php echo base_url('assets/front/assets/css/custom.css'); ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('assets/front/assets/vendor/fontawesome-free/css/all.min.css'); ?>">

    <!-- N8N Chat Widget CSS -->
    <link href="https://cdn.jsdelivr.net/npm/@n8n/chat/dist/style.css" rel="stylesheet" />

    <script src="<?php echo base_url('assets/front/assets/js/jquery.min.js'); ?>"></script>

   
</head>

<body>
    <?php $this->load->view('front/layouts/header'); ?>
    <?php //$this->load->view('layouts/sidebar'); ?> 

    <main class="main">
        <?php echo $content; ?> <!-- Dynamic content -->
    </main>

    <?php $this->load->view('front/layouts/footer'); ?>
</body>

</html>
