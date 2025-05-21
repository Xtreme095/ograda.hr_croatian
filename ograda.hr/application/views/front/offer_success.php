<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="container thank-you-page">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mt-5 mb-5">
                <div class="card-body text-center p-5">
                    <div class="success-icon mb-4">
                        <i class="fas fa-check-circle fa-5x text-success"></i>
                    </div>
                    
                    <h1 class="thank-you-title">Hvala Vam!</h1>
                    <h3 class="mb-4">Vaš zahtjev za ponudu je uspješno zaprimljen.</h3>
                    
                    <p class="lead mb-4">
                        Kontaktirat ćemo vas u najkraćem mogućem roku s detaljima vaše ponude.
                        Za sva dodatna pitanja slobodno nas nazovite.
                    </p>
                    
                    <?php if(isset($invoice_id) && isset($invoice_hash)): ?>
                    <div class="order-details mb-4">
                        <p>Broj ponude: <strong>#<?php echo !empty($invoice_number) ? $invoice_number : $invoice_id; ?></strong></p>
                        <p>Potvrdu zahtjeva za ponudom smo vam poslali na e-mail.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="mt-5">
                        <a href="<?php echo site_url(); ?>" class="btn btn-primary btn-lg">Povratak na početnu stranicu</a>
                        <?php if(isset($invoice_id) && isset($invoice_hash)): ?>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.thank-you-page {
    padding: 2rem 0;
}

.thank-you-title {
    font-size: 2.5rem;
    color: #28a745;
    margin-bottom: 1rem;
}

.success-icon {
    color: #28a745;
}

.order-details {
    background-color: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
}

.card {
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style> 
