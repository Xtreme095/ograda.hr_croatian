<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<main class="main">
    <nav aria-label="breadcrumb" class="breadcrumb-nav mb-3">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url(); ?>"><i class="icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('home/cart'); ?>">Košarica</a></li>
                <li class="breadcrumb-item active" aria-current="page">Narudžba uspješna</li>
            </ol>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card order-success-card">
                    <div class="card-header text-center">
                        <i class="icon-ok-circled success-icon"></i>
                        <h2>Hvala na vašoj narudžbi!</h2>
                    </div>
                    <div class="card-body">
                        <p class="text-center lead">Vaša narudžba je uspješno zaprimljena i trenutno se obrađuje.</p>
                        
                        <div class="order-details">
                            <h3>Detalji narudžbe</h3>
                            <p>Broj narudžbe: <strong>#<?php echo $invoice_id; ?></strong></p>
                            <p>Poslali smo vam potvrdu narudžbe na vašu email adresu.</p>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p>Možete pratiti status vaše narudžbe putem sljedećeg linka:</p>
                            <a href="<?php echo site_url('invoice/' . $invoice_id . '/' . $hash); ?>" class="btn btn-primary">Pregledajte svoju narudžbu</a>
                        </div>
                        
                        <div class="next-steps mt-5">
                            <h3>Što je sljedeće?</h3>
                            <ul>
                                <li>Primiti ćete potvrdu narudžbe na vašu email adresu</li>
                                <li>Naš tim će pripremiti vašu narudžbu</li>
                                <li>Obavijestiti ćemo vas kada narudžba bude spremna za isporuku</li>
                            </ul>
                        </div>
                        
                        <div class="create-account-section mt-5">
                            <h3>Želite li kreirati korisnički račun?</h3>
                            <p>Kreirajte račun kako biste mogli jednostavnije pratiti vaše narudžbe u budućnosti i uživati u ostalim pogodnostima.</p>
                            <a href="<?php echo site_url('authentication/register'); ?>" class="btn btn-outline-primary">Registrirajte se</a>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="<?php echo site_url(); ?>" class="btn btn-link">Povratak na početnu stranicu</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
    .order-success-card {
        border-radius: 4px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-bottom: 3rem;
        margin-top: 2rem;
    }
    
    .success-icon {
        font-size: 64px;
        color: #28a745;
        display: block;
        margin: 10px auto;
    }
    
    .order-details,
    .next-steps,
    .create-account-section {
        padding: 15px;
        border-radius: 4px;
        background-color: #f8f9fa;
        margin-top: 30px;
    }
    
    .order-details h3,
    .next-steps h3,
    .create-account-section h3 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #333;
    }
    
    .next-steps ul {
        padding-left: 20px;
    }
    
    .next-steps li {
        margin-bottom: 10px;
    }
</style> 