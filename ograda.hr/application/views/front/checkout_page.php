<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<main class="main">
    <nav aria-label="breadcrumb" class="breadcrumb-nav mb-3">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url(); ?>"><i class="icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('home/cart'); ?>">Košarica</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ponuda</li>
            </ol>
        </div>
    </nav>

    <div class="container">
        <?php if (!empty($message)) { ?>
            <div class="alert alert-warning">
                <?php echo $message; ?>
            </div>
        <?php } ?>
        
        <div class="checkout-title">
            <h1>Ponuda</h1>
        </div>
        
        <?php echo form_open(site_url('home/checkout'), ['id' => 'checkout-form']); ?>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Podaci za ponudu</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_firstname">Ime <span class="text-danger">*</span></label>
                                    <input type="text" name="billing_firstname" id="billing_firstname" class="form-control" value="<?php echo isset($contact) ? $contact->firstname : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_lastname">Prezime <span class="text-danger">*</span></label>
                                    <input type="text" name="billing_lastname" id="billing_lastname" class="form-control" value="<?php echo isset($contact) ? $contact->lastname : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_street">Adresa <span class="text-danger">*</span></label>
                                    <input type="text" name="billing_street" id="billing_street" class="form-control" value="<?php echo isset($client) ? $client->billing_street : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_city">Grad <span class="text-danger">*</span></label>
                                    <input type="text" name="billing_city" id="billing_city" class="form-control" value="<?php echo isset($client) ? $client->billing_city : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_state">Županija</label>
                                    <input type="text" name="billing_state" id="billing_state" class="form-control" value="<?php echo isset($client) ? $client->billing_state : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_zip">Poštanski broj <span class="text-danger">*</span></label>
                                    <input type="text" name="billing_zip" id="billing_zip" class="form-control" value="<?php echo isset($client) ? $client->billing_zip : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="billing_country">Država <span class="text-danger">*</span></label>
                            <input type="text" name="billing_country" id="billing_country" class="form-control" value="<?php echo isset($client) ? $client->billing_country : 'Hrvatska'; ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_phone">Telefon <span class="text-danger">*</span></label>
                                    <input type="tel" name="billing_phone" id="billing_phone" class="form-control" value="<?php echo isset($contact) ? $contact->phonenumber : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="billing_email">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="billing_email" id="billing_email" class="form-control" value="<?php echo isset($contact) ? $contact->email : ''; ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!$is_logged_in): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Korisnički račun</h3>
                    </div>
                    <div class="card-body">
                        <div class="account-options">
                            <p>Kupujete prvi put? Možete nastaviti kao gost ili kreirati korisnički račun za buduće ponude.</p>
                            
                            <div class="custom-control custom-radio mb-3">
                                <input type="radio" id="account-guest" name="account_option" class="custom-control-input" value="guest" checked>
                                <label class="custom-control-label" for="account-guest">Nastavite kao gost</label>
                            </div>
                            
                            <div class="custom-control custom-radio mb-3">
                                <input type="radio" id="account-register" name="account_option" class="custom-control-input" value="register">
                                <label class="custom-control-label" for="account-register">Kreirajte korisnički račun</label>
                            </div>
                            
                            <div id="register-fields" style="display: none;" class="mt-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Lozinka <span class="text-danger">*</span></label>
                                            <input type="password" name="password" id="password" class="form-control" minlength="6" disabled>
                                            <small class="form-text text-muted">Minimalno 6 znakova</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_confirm">Potvrdite lozinku <span class="text-danger">*</span></label>
                                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" minlength="6" disabled>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="create_account" id="create_account" value="0">
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h3>Napomene uz ponudu</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <textarea name="order_notes" id="order_notes" class="form-control" rows="4" placeholder="Napomene o vašoj ponudi, npr. posebne informacije za dostavu."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card order-summary mb-4">
                    <div class="card-header">
                        <h3>Sažetak ponude</h3>
                    </div>
                    <div class="card-body">
                        <table class="table order-products">
                            <tbody>
                                <?php foreach ($products as $key => $product) { ?>
                                    <?php if (!empty($product->quantity)) { ?>
                                        <tr>
                                            <td class="product-name">
                                                <?php echo form_hidden('product_items['.$key.'][product_id]', $product->id); ?>
                                                <?php echo form_hidden('product_items['.$key.'][qty]', $product->quantity); ?>
                                                <?php if (isset($product->product_variation_id)) { ?>
                                                    <?php echo form_hidden('product_items['.$key.'][product_variation_id]', $product->product_variation_id); ?>
                                                <?php } ?>
                                                
                                                <?php if (isset($product->selected_height) && !empty($product->selected_height)) { ?>
                                                    <?php echo form_hidden('product_items['.$key.'][selected_height]', $product->selected_height); ?>
                                                <?php } ?>
                                                
                                                <?php if (isset($product->selected_material) && !empty($product->selected_material)) { ?>
                                                    <?php echo form_hidden('product_items['.$key.'][selected_material]', $product->selected_material); ?>
                                                <?php } ?>
                                                
                                                <?php if (isset($product->selected_glass) && !empty($product->selected_glass)) { ?>
                                                    <?php echo form_hidden('product_items['.$key.'][selected_glass]', $product->selected_glass); ?>
                                                <?php } ?>
                                                
                                                <?php if (isset($product->direct_price) && !empty($product->direct_price)) { ?>
                                                    <?php echo form_hidden('product_items['.$key.'][direct_price]', $product->direct_price); ?>
                                                <?php } ?>
                                                
                                                <?php echo htmlspecialchars($product->product_name); ?>
                                                
                                                <?php if (!empty($product->product_variation_id)) { ?>
                                                    <span class="variation">
                                                        (<?php echo htmlspecialchars($product->variation_name); ?>: <?php echo htmlspecialchars($product->variation_value); ?>)
                                                    </span>
                                                <?php } ?>
                                                
                                                <?php if (isset($product->selected_height) && !empty($product->selected_height)) { ?>
                                                    <span class="variation">
                                                        (Visina: <?php echo htmlspecialchars($product->selected_height); ?>)
                                                    </span>
                                                <?php } ?>
                                                
                                                <?php if (isset($product->selected_material) && !empty($product->selected_material)) { ?>
                                                    <span class="variation">
                                                        (Materijal: <?php echo htmlspecialchars($product->selected_material); ?>)
                                                    </span>
                                                <?php } ?>
                                                
                                                <?php if (isset($product->selected_glass) && !empty($product->selected_glass)) { ?>
                                                    <span class="variation">
                                                        (Staklo: <?php echo htmlspecialchars($product->selected_glass); ?>)
                                                    </span>
                                                <?php } ?>
                                                
                                                <div class="quantity-display mt-2">
                                                    Količina: <?php echo $product->quantity; ?>
                                                </div>
                                                
                                                <input type="hidden" name="product_items[<?php echo $key; ?>][qty]" class="product-quantity-input" value="<?php echo $product->quantity; ?>">
                                            </td>
                                            <td class="product-total text-right">
                                                <?php 
                                                // Use the proper price field with fallbacks
                                                $item_price = 0;
                                                if (isset($product->direct_price) && $product->direct_price > 0) {
                                                    $item_price = $product->direct_price;
                                                } else if (isset($product->product_variation_id) && isset($product->variation_rate) && $product->variation_rate > 0) {
                                                    $item_price = $product->variation_rate;
                                                } else if (isset($product->rate) && $product->rate > 0) {
                                                    $item_price = $product->rate;
                                                }
                                                $item_total = $product->quantity * $item_price;
                                                echo app_format_money($item_total, $base_currency->name); 
                                                ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                        
                        <div class="order-totals">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Međuzbroj</span>
                                <span><?php echo app_format_money($total, $base_currency->name); ?></span>
                            </div>
                            
                            <?php if (isset($_SESSION['coupon_id'])) { ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Popust (kupon)</span>
                                    <span>-<?php echo app_format_money($_SESSION['coupon_discount'], $base_currency->name); ?></span>
                                </div>
                                <?php echo form_hidden('coupon_id', $_SESSION['coupon_id']); ?>
                            <?php } ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>PDV (25%)</span>
                                <span>
                                    <?php 
                                    // Calculate tax at a flat 25% of subtotal
                                    $tax_amount = $total * 0.25;
                                    echo app_format_money($tax_amount, $base_currency->name); 
                                    ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($shipping_cost)) { ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>
                                        Troškovi dostave
                                        <small>(<?php echo app_format_money($base_shipping_cost, $base_currency->name) . ' + ' . $shipping_tax . '%'; ?>)</small>
                                    </span>
                                    <span>
                                        <?php echo form_hidden('shipping_cost', $shipping_cost); ?>
                                        <?php echo app_format_money($shipping_cost, $base_currency->name); ?>
                                    </span>
                                </div>
                            <?php } ?>
                            
                            <div class="d-flex justify-content-between total-row">
                                <span><strong>Ukupno</strong></span>
                                <span><strong><?php
                                // Calculate final total with fixed 25% tax
                                $final_total = $total + $tax_amount + $shipping_cost;
                                
                                // Apply coupon if present
                                if (isset($_SESSION['coupon_id']) && isset($_SESSION['coupon_discount'])) {
                                    $final_total -= $_SESSION['coupon_discount'];
                                }
                                
                                echo app_format_money($final_total, $base_currency->name); 
                                ?></strong></span>
                            </div>
                        </div>
                        
                        <div class="order-button-wrapper mt-4">
                            <p class="terms-note">
                                Podnošenjem ponude pristajete na naše <a href="<?php echo site_url('terms-of-use'); ?>" target="_blank">uvjete korištenja</a> i <a href="<?php echo site_url('privacy-policy'); ?>" target="_blank">politiku privatnosti</a>.
                            </p>
                            
                            <button type="submit" class="btn btn-block btn-primary">
                                <i class="icon-lock mr-2"></i> Zatražite ponudu
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php echo form_close(); ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate initial totals
    updateOrderTotals();
});

/**
 * Update checkout item quantities and recalculate totals
 */
function updateCheckoutItem(input) {
    const productId = input.dataset.product_id;
    const variationId = input.dataset.product_variation_id || 0;
    const quantity = input.value;
    
    // Find the row and get price data
    const row = input.closest('tr');
    const totalCell = row.querySelector('.product-total');
    
    // Get unit price from the data attribute
    const unitPrice = parseFloat(input.dataset.unit_price);
    
    // Calculate the new total
    const newTotal = unitPrice * quantity;
    
    // Format the total with proper currency symbol
    const currencySymbol = '<?php echo $base_currency->symbol; ?>';
    let formattedTotal = currencySymbol + newTotal.toFixed(2).replace('.', ',');
    
    // Update the total in the display
    totalCell.innerHTML = formattedTotal;
    
    // Update hidden quantity input
    const hiddenInput = row.querySelector('.product-quantity-input');
    if (hiddenInput) {
        hiddenInput.value = quantity;
    }
    
    // Get CSRF token
    const csrfToken = '<?php echo $this->security->get_csrf_hash(); ?>';
    
    // Add loading indicator
    let loadingIndicator = row.querySelector('.loading-indicator');
    if (!loadingIndicator) {
        loadingIndicator = document.createElement('span');
        loadingIndicator.className = 'loading-indicator';
        loadingIndicator.innerHTML = ' <i class="fa fa-spinner fa-spin"></i>';
        totalCell.appendChild(loadingIndicator);
    }
    loadingIndicator.classList.add('active');
    
    // Send AJAX request to update the cart
    $.ajax({
        url: '<?php echo site_url('home/update_cart_item'); ?>',
        type: 'POST',
        data: {
            '<?php echo $this->security->get_csrf_token_name(); ?>': csrfToken,
            product_id: productId,
            variation_id: variationId,
            quantity: quantity
        },
        success: function(response) {
            // Remove loading indicator active state
            if (loadingIndicator) {
                loadingIndicator.classList.remove('active');
            }
            
            try {
                // Update the order totals
                updateOrderTotals();
            } catch (e) {
                console.error('Error updating totals:', e);
                // If JavaScript calculation fails, reload the page
                window.location.reload();
            }
        },
        error: function() {
            // Remove loading indicator active state
            if (loadingIndicator) {
                loadingIndicator.classList.remove('active');
            }
            // Reload the page on error
            window.location.reload();
        }
    });
}

/**
 * Helper function to parse currency values from text
 * Handles different number formats and currency symbols
 */
function parseCurrencyValue(text) {
    // Remove any non-numeric characters except for , and .
    const numericValue = text.replace(/[^0-9,.]/g, '');
    
    // If the string has both . and , we need to determine which is the decimal separator
    if (numericValue.includes('.') && numericValue.includes(',')) {
        // If last separator is comma, it's likely the decimal separator
        const lastDotIndex = numericValue.lastIndexOf('.');
        const lastCommaIndex = numericValue.lastIndexOf(',');
        
        if (lastCommaIndex > lastDotIndex) {
            // Format: 1.234,56 (European)
            return parseFloat(numericValue.replace(/\./g, '').replace(',', '.'));
        } else {
            // Format: 1,234.56 (American)
            return parseFloat(numericValue.replace(/,/g, ''));
        }
    } else if (numericValue.includes(',')) {
        // Only commas, assume it's a decimal separator
        return parseFloat(numericValue.replace(',', '.'));
    } else {
        // Only periods or no separators
        return parseFloat(numericValue);
    }
}

/**
 * Update the order totals in the checkout summary
 */
function updateOrderTotals() {
    // Calculate new subtotal
    let subtotal = 0;
    
    // Go through each product row and add up the totals
    document.querySelectorAll('.order-products tr').forEach(function(row) {
        const totalCell = row.querySelector('.product-total');
        if (totalCell) {
            const totalText = totalCell.textContent.trim();
            const itemTotal = parseCurrencyValue(totalText);
            if (!isNaN(itemTotal)) {
                subtotal += itemTotal;
            }
        }
    });
    
    // Get tax rate (25%)
    const taxRate = 0.25;
    
    // Calculate tax
    const tax = subtotal * taxRate;
    
    // Get the shipping cost
    let shippingCost = 0;
    const shippingCostElements = document.querySelectorAll('.order-totals .d-flex:nth-child(3)');
    shippingCostElements.forEach(function(element) {
        if (element.textContent.includes('Troškovi dostave')) {
            const costSpan = element.querySelector('span:nth-child(2)');
            if (costSpan) {
                shippingCost = parseCurrencyValue(costSpan.textContent.trim());
            }
        }
    });
    
    // Get any coupon discount
    let discount = 0;
    const discountElements = document.querySelectorAll('.order-totals .d-flex:nth-child(2)');
    discountElements.forEach(function(element) {
        if (element.textContent.includes('Popust')) {
            const discountSpan = element.querySelector('span:nth-child(2)');
            if (discountSpan) {
                discount = parseCurrencyValue(discountSpan.textContent.trim());
            }
        }
    });
    
    // Calculate total
    const total = (subtotal - discount + tax + shippingCost).toFixed(2);
    
    // Update the subtotal display
    const subtotalElements = document.querySelectorAll('.order-totals .d-flex:nth-child(1) span:nth-child(2)');
    subtotalElements.forEach(function(element) {
        if (element.parentElement.textContent.includes('Međuzbroj')) {
            element.textContent = '<?php echo $base_currency->symbol; ?>' + subtotal.toFixed(2).replace('.', ',');
        }
    });
    
    // Update the tax display
    const taxElements = document.querySelectorAll('.order-totals .d-flex span:nth-child(2)');
    taxElements.forEach(function(element) {
        if (element.parentElement.textContent.includes('PDV')) {
            element.textContent = '<?php echo $base_currency->symbol; ?>' + tax.toFixed(2).replace('.', ',');
        }
    });
    
    // Update the total display
    const totalElement = document.querySelector('.total-row span:nth-child(2)');
    if (totalElement) {
        totalElement.textContent = '<?php echo $base_currency->symbol; ?>' + parseFloat(total).toFixed(2).replace('.', ',');
    }
    
    console.log('Updated totals: Subtotal=' + subtotal + ', Tax=' + tax + ', Shipping=' + shippingCost + ', Discount=' + discount + ', Total=' + total);
}
</script>

<style>
/* Checkout Styles */
.checkout-title {
    margin-bottom: 2rem;
}

.checkout-title h1 {
    font-size: 28px;
    font-weight: 600;
    color: #333;
}

.card {
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 1.5rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem 1.5rem;
}

.card-header h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 0;
    color: #333;
}

.card-body {
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #444;
}

.custom-control-label {
    font-weight: 500;
    color: #444;
}

.payment-details {
    font-size: 0.9rem;
    color: #666;
}

.order-summary {
    position: sticky;
    top: 20px;
}

.order-products {
    margin-bottom: 1.5rem;
}

.product-name {
    font-weight: 500;
    color: #333;
}

.variation {
    display: block;
    font-size: 0.85rem;
    color: #666;
}

.product-quantity {
    color: #666;
    margin-left: 0.5rem;
}

.order-totals {
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

.total-row {
    padding-top: 0.75rem;
    margin-top: 0.75rem;
    border-top: 2px solid #dee2e6;
    font-size: 1.1rem;
}

.terms-note {
    font-size: 0.85rem;
    color: #666;
    margin-bottom: 1rem;
}

.is-invalid {
    border-color: #dc3545;
}

/* Quantity control styles */
.quantity-container {
    display: flex;
    align-items: center;
    max-width: 120px;
}

.quantity-btn {
    width: 30px;
    height: 30px;
    padding: 0;
    background: #f4f4f4;
    border: 1px solid #ddd;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    user-select: none;
}

.quantity-btn:hover {
    background: #e9e9e9;
}

.quantity-input {
    width: 45px;
    height: 30px;
    padding: 0 5px;
    text-align: center;
    border: 1px solid #ddd;
    margin: 0 5px;
}

.quantity-input::-webkit-inner-spin-button,
.quantity-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-input[type=number] {
    -moz-appearance: textfield;
}

.loading-indicator {
    display: inline-block;
    margin-left: 5px;
    color: #666;
    visibility: hidden;
}

.loading-indicator.active {
    visibility: visible;
}

/* Order totals styles */
.order-totals {
    margin-top: 20px;
    padding: 15px;
    background-color: #f9f9f9;
    border-radius: 4px;
}

.total-row {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #ddd;
    font-size: 1.1em;
}

/* Ensure order summary is more visible */
.order-summary {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.order-summary h4 {
    margin-bottom: 15px;
}

.order-summary table {
    width: 100%;
}

/* Responsive styling for product rows */
@media (max-width: 767px) {
    .product-name {
        width: 65%;
    }
    
    .product-total {
        width: 35%;
    }
    
    .quantity-container {
        margin-top: 10px;
    }
}
</style> 