<!DOCTYPE html>
<html lang="en">
<head>
    <title>WhatsApp Cloud API Business Interaction Module Documentation for Perfex CRM</title>
    <!-- Meta Information -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Comprehensive documentation for the WhatsApp Cloud API Business Interaction Module integrated with Perfex CRM.">
    <meta name="author" content="Themesic Interactive">
    <link rel="shortcut icon" href="favicon.png">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <script defer src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/fontawesome/js/all.min.js'); ?>"></script>
    
    <!-- Plugins CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.2/styles/atom-one-dark.min.css">
    
    <!-- Theme CSS -->
    <link id="theme-style" rel="stylesheet" href="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/css/theme.css'); ?>">
</head>

<body class="docs-page">
    <!-- Header Section -->
    <header class="header fixed-top">
        <div class="branding docs-branding">
            <div class="container-fluid position-relative py-2">
                <div class="docs-logo-wrapper">
                    <button id="docs-sidebar-toggler" class="docs-sidebar-toggler docs-sidebar-visible me-2 d-xl-none" type="button">
                        <span></span><span></span><span></span>
                    </button>
                    <div class="site-logo">
                        <a class="navbar-brand" href="https://1.envato.market/themesic">
                            <img class="logo-icon me-2" src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/images/logohd.png'); ?>" alt="logo" width=48>
                        </a>
                        WhatsApp Cloud API Chat Module Documentation
                    </div>
                </div>

                <!-- Top Utilities -->
                <div class="docs-top-utilities d-flex justify-content-end align-items-center">
                    <ul class="social-list list-inline mx-md-3 mx-lg-5 mb-0 d-none d-lg-flex">
                        <a href="https://1.envato.market/themesic" class="btn btn-primary d-none d-lg-flex">Purchase it</a>
                    </ul>
                    <a href="https://themesic.com/support/" class="btn btn-primary d-none d-lg-flex">Get Support</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Documentation Wrapper -->
    <div class="docs-wrapper">
        <!-- Sidebar -->
        <div id="docs-sidebar" class="docs-sidebar">
            <br>
            <nav id="docs-nav" class="docs-nav navbar">
                <ul class="section-items list-unstyled nav flex-column pb-3">
                    <li class="nav-item section-title">
                        <a class="nav-link scrollto" href="#section-1">
                            <span class="theme-icon-holder me-2"><i class="fas fa-map-signs"></i></span>Introduction
                        </a>
                    </li>
                    <li class="nav-item"><a class="nav-link scrollto" href="#item-1-3">Dependencies</a></li>
                    <li class="nav-item section-title mt-3"><a class="nav-link scrollto" href="#section-2"><span class="theme-icon-holder me-2"><i class="fas fa-cogs"></i></span>Installation</a></li>
                    <li class="nav-item"><a class="nav-link scrollto" href="#item-2-1">Procedure</a></li>
                    <li class="nav-item section-title mt-3"><a class="nav-link scrollto" href="#section-3"><span class="theme-icon-holder me-2"><i class="fas fa-tools"></i></span>Configuration</a></li>
                    <li class="nav-item"><a class="nav-link scrollto" href="#item-3-1">Facebook Developers Account</a></li>
                    <li class="nav-item"><a class="nav-link scrollto" href="#item-3-2">Facebook Application</a></li>
                    <li class="nav-item"><a class="nav-link scrollto" href="#item-3-3">Connect Your Phone Number</a></li>
                    <li class="nav-item"><a class="nav-link scrollto" href="#item-3-4">Module's Settings</a></li>
                </ul>
            </nav>
        </div>

        <!-- Content Section -->
        <div class="docs-content">
            <div class="container">
                <article class="docs-article" id="section-1">
                    <header class="docs-header">
                        <center><img style="max-width:100%;" src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/images/main.jpg'); ?>" alt="WhatsApp Cloud API Interaction Module for Perfex"></center>
                        <br><br>
                        <h1 class="docs-heading">Introduction <span class="docs-time">WhatsApp Cloud API Interaction Module</span></h1>
                    </header>
                    <section class="docs-intro">
                        <p>
                            <strong>Thank you</strong> for purchasing the WhatsApp Cloud API Interaction Module for Perfex CRM.
                            Our Communication Module integrates seamlessly with Perfex CRM, enabling real-time communication between staff and clients directly from the Perfex dashboard. Say goodbye to switching between multiple platforms—everything you need is within reach.
                        </p>
                    </section>

                    <!-- Dependencies Section -->
                    <section class="docs-section" id="item-1-3">
                        <h2 class="section-heading">Dependencies</h2>
                        <ul>
                            <li>You need to own a <strong>Facebook Developers Account</strong>. Not sure how to create one? Visit the <a href="#section-3">Configuration</a> section for detailed instructions.</li>
                            <li>A <strong>Facebook App</strong> is required, which can be created through a verified (or unverified) Business/Individual account. Refer to the <a href="#section-3">Configuration</a> section for more guidance.</li>
                            <li>If you skip the business verification, your messaging capabilities will be limited. Learn more about <a href="https://developers.facebook.com/docs/whatsapp/messaging-limits" target="_blank">WhatsApp Messaging limits</a> and how to increase them <a href="https://www.facebook.com/business/help/2640149499569241" target="_blank">here</a>.</li>
                        </ul>
                    </section>
                </article>

                <!-- Installation Section -->
                <article class="docs-article" id="section-2">
                    <header class="docs-header">
                        <h1 class="docs-heading">Installation</h1>
                    </header>
                    <section class="docs-section" id="item-2-0">
                        <p>Follow these simple steps to install and activate the module within seconds. For any queries, raise a support ticket at our <a href="https://themesic.com/clients" target="_blank">Support Area</a>.</p>
                    </section>

                    <section class="docs-section" id="item-2-1">
                        <h2 class="section-heading">Installation Steps</h2>
                        <ol>
                            <li>Extract the downloaded file and locate the <strong>"upload.zip"</strong> file. This contains the module files needed for Perfex CRM installation. The <strong>"documentation"</strong> folder can be ignored as it includes instructions not required for installation.</li>
                            <li>Go to Perfex CRM's Admin area and navigate to <strong>SETUP > MODULES</strong>. Upload the <strong>"upload.zip"</strong> file.</li>
                            <li>Find the newly installed module and click <strong>ACTIVATE</strong>.</li>
                            <li>Enter your License Key when prompted, and the module will be successfully activated.</li>
                        </ol>
                        <p><strong>That's it!</strong> Your module is now activated.</p>
                    </section>
                </article>

                <!-- Configuration Section -->
                <article class="docs-article" id="section-3">
                    <header class="docs-header">
                        <h1 class="docs-heading">Configuration</h1>
                        <p>Follow the steps outlined in the screenshots provided below. For any questions, feel free to reach out through our <a href="https://themesic.com/clients" target="_blank">Support Area</a>.</p>
                    </header>

                    <!-- Facebook Developers Account -->
                    <section class="docs-section" id="item-3-1">
                        <h2 class="section-heading">Facebook Developers Account</h2>
                        <p>To create a Facebook Developers Account, follow these steps:</p>
                        <ol>
                            <li>Login to your Facebook account and navigate to <a href="https://developers.facebook.com">Facebook Developers</a>. Click <strong>Get Started</strong>.</li>
                            <li>Enter your phone number for verification.</li>
                            <li>Once verified, select <strong>Developer</strong> and proceed.</li>
                        </ol>
                    </section>

                    <!-- Facebook Application Creation -->
                    <section class="docs-section" id="item-3-2">
                        <h2 class="section-heading">Facebook Application Creation</h2>
                        <p>Follow the steps below to create a Facebook Application for the module:</p>
                        <ol>
                            <li>Sign in to the <a href="https://developers.facebook.com">Facebook for Developers</a> portal, and click <strong>Create App</strong>.</li>
                            <li>Select <strong>Business</strong> as your app type.</li>
                            <li>Provide the required business information.</li>
                            <li>Navigate to the <strong>WhatsApp</strong> section and click <strong>Set up</strong>.</li>
                            <li>Accept the terms and conditions to continue.</li>
                            <li>Send a test message to verify the setup.</li>
                        </ol>
                    </section>

                    <!-- Connect Your Phone Number -->
                    <section class="docs-section" id="item-3-3">
                        <h2 class="section-heading">Connect Your Phone Number</h2>
                        <p>Follow these steps to connect your phone number to the WhatsApp Cloud API:</p>
                        <ol>
                            <li>Click <strong>Add Phone Number</strong> and follow the on-screen instructions.</li>
                            <li>Verify the phone number by entering the code sent to your number.</li>
                        </ol>
                    </section>

                   <section class="docs-section" id="item-3-4">
                            <h2 class="section-heading">Module's Settings</h2>
                            <p>To configure the WhatsApp Official Chat Module and ensure smooth operation, you need to complete the following steps by filling in the required fields. The settings page is essential for linking your WhatsApp Business account and activating the communication between Perfex CRM and WhatsApp Cloud API.</p>
                            
                            <h3>Steps to Configure the Module:</h3>
                            <ol>
                                <li>
                                    <strong>Navigate to the Settings Page:</strong>
                                    <p>In the Perfex CRM Admin panel, go to <strong>Settings &gt; WhatsApp Official Chat</strong> to access the module's configuration page.</p>
                                </li>
                                
                                <li>
                                    <strong>Enter Required Fields:</strong>
                                    <p>Input the values you gathered during the Facebook Application creation and Token generation process. Ensure the following fields are accurately filled:</p>
                                    <ul>
                                        <li><strong>WhatsApp Business Account ID:</strong> This is the unique identifier for your WhatsApp Business account.</li>
                                        <li><strong>WhatsApp Access Token:</strong> A token that authenticates your WhatsApp Business API. Ensure it is copied correctly from the Facebook Developer Portal.</li>
                                        <li><strong>WhatsApp Lead Status:</strong> Set the default lead status for new leads generated via WhatsApp messages (e.g., Raw Lead).</li>
                                        <li><strong>WhatsApp Lead Source:</strong> Specify the source of the lead (e.g., WhatsApp, Facebook) to organize incoming leads.</li>
                                        <li><strong>WhatsApp Lead Assigned:</strong> Assign the responsible team member to manage the incoming leads. This is useful for sales or customer support teams.</li>
                                        <li><strong>Convert New Message to Lead Automatically:</strong> Choose whether new messages should automatically be converted into leads.</li>
                                        <li><strong>WhatsApp Webhook URL:</strong> This URL handles WhatsApp message events. It is crucial for the communication between WhatsApp and the CRM.</li>
                                        <li><strong>WhatsApp Webhook Token:</strong> A token for verifying the authenticity of webhook requests sent to your server.</li>
                                        <li><strong>WhatsApp OpenAI Status:</strong> Enable or disable the OpenAI integration to use AI for message automation.</li>
                                        <li><strong>WhatsApp Welcome Template:</strong> Specify the welcome message template to be sent automatically to new contacts.</li>
                                        <li><strong>WhatsApp Blueticks Status:</strong> Enable or disable read receipts for WhatsApp messages.</li>
                                        <li><strong>Enable WebHooks Re-send:</strong> If enabled, this option allows failed webhook requests to be resent.</li>
                                    </ul>
                                </li>
                                
                                <li>
                                    <strong>Configure Webhook Settings in Meta Developer Portal:</strong>
                                    <p>Once you've filled in the necessary fields, you will need to log into your <strong>Meta Developer Portal</strong> (formerly Facebook Developer) and configure the webhook settings:</p>
                                    <ul>
                                        <li>Locate the <strong>Webhook Configuration</strong> section in your Meta Developer Portal account.</li>
                                        <li>Enter the Webhook URL and Token that you received from the WhatsApp Official Chat Module during the module setup.</li>
                                        <li>Ensure that your webhook is subscribed to the <strong>message</strong> event, which is critical for sending and receiving WhatsApp messages within Perfex CRM.</li>
                                    </ul>
                                </li>
                            </ol>
                            
                            <p><strong>Congratulations!</strong> You have successfully configured the WhatsApp Official Chat Module. You can now start sending and receiving WhatsApp messages directly from Perfex CRM.</p>
                        </section>

                </article>
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="container text-center py-5">
            <small class="copyright">
                WhatsApp Cloud API Interaction Module brought to you by &copy; <a href="https://1.envato.market/themesic" target="_blank">Themesic Interactive</a>
            </small>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/plugins/popper.min.js'); ?>"></script>
    <script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/plugins/bootstrap/js/bootstrap.min.js'); ?>"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.15.8/highlight.min.js"></script>
    <script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/highlight-custom.js'); ?>"></script>
    <script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/plugins/simplelightbox/simple-lightbox.min.js'); ?>"></script>
    <script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/plugins/gumshoe/gumshoe.polyfills.min.js'); ?>"></script>
    <script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/docs.js'); ?>"></script>
</body>
</html>
