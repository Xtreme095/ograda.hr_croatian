<?php defined('BASEPATH') or exit('No direct script access allowed'); 

// Make sure the slugify function is available
if (!function_exists('slugify')) {
    function slugify($text, string $divider = '-') {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        // trim
        $text = trim($text, $divider);
        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);
        // lowercase
        $text = strtolower($text);
        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
}
?>

<main class="main">
    <nav aria-label="breadcrumb" class="breadcrumb-nav mb-3">
        <div class="container">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo site_url(); ?>"><i class="icon-home"></i></a></li>
                <li class="breadcrumb-item"><a href="<?php echo site_url('products'); ?>">Proizvodi</a></li>
                <li class="breadcrumb-item active" aria-current="page">Košarica</li>
            </ol>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="cart-title">
                    <h1>Košarica</h1>
                    <p class="lead">Pregledajte proizvode u košarici i nastavite na proces narudžbe.</p>
                </div>
                
                <?php if (!empty($message)) { ?>
                    <div class="alert alert-warning">
                        <?php echo $message; ?>
                    </div>
                <?php } ?>
                
                <?php if (empty($products)) { ?>
                    <div class="cart-empty-container text-center py-5">
                        <div class="cart-empty-icon mb-4">
                            <i class="icon-shopping-cart" style="font-size: 5rem; color: #ddd;"></i>
                        </div>
                        <h2 class="cart-empty-title">Vaša košarica je prazna</h2>
                        <p class="cart-empty-text mb-4">Dodajte proizvode u košaricu kako biste nastavili kupovinu.</p>
                        <a href="<?php echo site_url('products'); ?>" class="btn btn-outline-primary">Pregledajte proizvode</a>
                    </div>
                <?php } else { ?>
                    <div id="cart_container">
                    
                    <div class="cart-table-container">
                        <table class="table table-cart">
                            <thead>
                                <tr>
                                    <th class="product-col">Proizvod</th>
                                    <th class="price-col text-center">Cijena</th>
                                    <th class="qty-col text-center">Količina</th>
                                    <th class="text-right">Ukupno</th>
                                    <th class="text-center">Akcije</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $key => $product) { ?>
                                    <?php if (!empty($product->quantity)) { ?>
                                        <tr class="product-row">
                                            <td class="product-col">
                                                <?php echo form_hidden('product_items['.$key.'][product_id]', $product->id); ?>
                                                
                                                <?php if (isset($product->selected_height) && !empty($product->selected_height)) { ?>
                                                    <?php echo form_hidden('product_items['.$key.'][selected_height]', $product->selected_height); ?>
                                                <?php } ?>
                                                
                                                <figure class="product-image-container">
                                                    <?php 
                                                    $product_image = module_dir_url('products', 'uploads/') . $product->product_image;
                                                    $no_image = module_dir_url('products', 'uploads/') . 'image-not-available.png';
                                                    ?>
                                                    <a href="<?php echo site_url('product/' . slugify($product->product_name)); ?>">
                                                        <img src="<?php echo file_exists(FCPATH . 'modules/products/uploads/' . $product->product_image) ? $product_image : $no_image; ?>" alt="<?php echo htmlspecialchars($product->product_name); ?>">
                                                    </a>
                                                </figure>
                                                
                                                <div class="product-details">
                                                    <h3 class="product-title">
                                                        <a href="<?php echo site_url('product/' . slugify($product->product_name)); ?>">
                                                            <?php echo htmlspecialchars($product->product_name); ?>
                                                        </a>
                                                    </h3>
                                                    
                                                    <?php if (isset($product->product_variation_id)) { 
                                                        // Log variation data for debugging
                                                        error_log('CART_PAGE_VARIATION: Product ' . $product->id . ' - Variation ID: ' . $product->product_variation_id . ', Name: ' . ($product->variation_name ?? 'not set') . ', Value: ' . ($product->variation_value ?? 'not set'));
                                                    ?>
                                                        <?php echo form_hidden('product_items['.$key.'][product_variation_id]', $product->product_variation_id); ?>
                                                        <div class="product-variant">
                                                            <span class="variant-label"><?php echo isset($product->variation_name) ? htmlspecialchars($product->variation_name) : 'Vrsta materijala'; ?>:</span>
                                                            <span class="variant-value"><?php echo isset($product->variation_value) && !empty($product->variation_value) ? htmlspecialchars($product->variation_value) : 'Standard'; ?></span>
                                                        </div>
                                                    <?php } ?>
                                                    
                                                    <?php if (isset($product->selected_height) && !empty($product->selected_height)) { ?>
                                                        <div class="product-variant">
                                                            <span class="variant-label">Visina:</span>
                                                            <span class="variant-value"><?php echo htmlspecialchars($product->selected_height); ?></span>
                                                        </div>
                                                    <?php } ?>
                                                    
                                                    <div class="product-description">
                                                        <?php echo htmlspecialchars(substr($product->product_description, 0, 100) . (strlen($product->product_description) > 100 ? '...' : '')); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            
                                            <td class="price-col text-center">
                                                <?php 
                                                // Use the proper price field with fallbacks
                                                $item_price = 0;
                                                if (isset($product->direct_price) && $product->direct_price > 0) {
                                                    $item_price = $product->direct_price;
                                                    error_log('CART_PAGE: Product ' . $product->id . ' - Using direct_price: ' . $item_price);
                                                    // Add hidden field to pass the direct price to checkout
                                                    echo form_hidden('product_items['.$key.'][direct_price]', $product->direct_price);
                                                } else if (isset($product->product_variation_id) && isset($product->variation_rate) && $product->variation_rate > 0) {
                                                    $item_price = $product->variation_rate;
                                                    error_log('CART_PAGE: Product ' . $product->id . ' - Using variation_rate: ' . $item_price);
                                                    // Even for variation rate, store it as direct_price to ensure it's passed to checkout
                                                    echo form_hidden('product_items['.$key.'][direct_price]', $product->variation_rate);
                                                } else if (isset($product->rate) && $product->rate > 0) {
                                                    $item_price = $product->rate;
                                                    error_log('CART_PAGE: Product ' . $product->id . ' - Using base rate: ' . $item_price);
                                                    // Store base rate as direct_price if that's what we're using
                                                    echo form_hidden('product_items['.$key.'][direct_price]', $product->rate);
                                                }
                                                echo app_format_money($item_price, $base_currency->name); 
                                                ?>/m1
                                            </td>
                                            
                                            <td class="qty-col">
                                                <?php
                                                $qty_attr = [
                                                    'class' => 'form-control quantity-input',
                                                    'min' => '1',
                                                    'max' => $product->is_digital ? '100' : $product->quantity_number,
                                                    'data-product_id' => $product->id,
                                                    'data-product_variation_id' => $product->product_variation_id ?? ''
                                                ];
                                                ?>
                                                <div class="quantity-container">
                                                    <button type="button" class="quantity-btn decrease"><i class="icon-minus"></i></button>
                                                    <input type="number" name="product_items[<?php echo $key; ?>][qty]" value="<?php echo $product->quantity; ?>" <?php foreach ($qty_attr as $attr => $val) { echo $attr . '="' . $val . '" '; } ?>>
                                                    <button type="button" class="quantity-btn increase"><i class="icon-plus"></i></button>
                                                </div>
                                            </td>
                                            
                                            <td class="total-col text-right">
                                                <?php 
                                                // Use the proper price field with fallbacks
                                                $item_price = 0;
                                                if (isset($product->direct_price) && $product->direct_price > 0) {
                                                    $item_price = $product->direct_price;
                                                    error_log('CART_PAGE_TOTAL: Product ' . $product->id . ' - Using direct_price: ' . $item_price);
                                                } else if (isset($product->product_variation_id) && isset($product->variation_rate) && $product->variation_rate > 0) {
                                                    $item_price = $product->variation_rate;
                                                    error_log('CART_PAGE_TOTAL: Product ' . $product->id . ' - Using variation_rate: ' . $item_price);
                                                } else if (isset($product->rate) && $product->rate > 0) {
                                                    $item_price = $product->rate;
                                                    error_log('CART_PAGE_TOTAL: Product ' . $product->id . ' - Using base rate: ' . $item_price);
                                                }
                                                $item_total = $product->quantity * $item_price;
                                                echo app_format_money($item_total, $base_currency->name); 
                                                ?>
                                            </td>
                                            
                                            <td class="action-col text-center">
                                                <button type="button" class="btn-remove remove-item" data-product-id="<?php echo $product->id; ?>" data-variation-id="<?php echo $product->product_variation_id ?? ''; ?>" title="Ukloni proizvod">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="cart-bottom">
                        <div class="row">
                            <div class="col-lg-6">
                                <?php if (0 == get_option('coupons_disabled')) { ?>
                                    <div class="coupon-section mt-4">
                                        <h5>Kupon</h5>
                                        <div class="input-group">
                                            <input type="text" id="coupon_code" class="form-control" placeholder="Unesite kod kupona">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-primary apply_coupon" id="apply_coupon_btn">Primijeni</button>
                                                <button type="button" class="btn btn-danger remove_coupon d-none">Ukloni</button>
                                            </div>
                                        </div>
                                        <div id="coupon_message"></div>
                                        <?php echo form_hidden('coupon_id', ''); ?>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <div class="col-lg-6">
                                <div class="cart-summary">
                                    <h4 class="section-title">Sažetak košarice</h4>
                                    
                                    <table class="table table-totals">
                                        <tbody>
                                            <tr id="subtotal">
                                                <td>Vrijednost proizvoda</td>
                                                <td class="subtotal text-right">
                                                    <?php echo app_format_money($total, $base_currency->name); ?>
                                                </td>
                                            </tr>
                                            
                                            <tr id="coupon_discount" class="d-none">
                                                <td>Popust (kupon)</td>
                                                <td class="coupon_discount text-right"></td>
                                            </tr>
                                            
                                            <tr class="tax-area">
                                                <td>
                                                    PDV (25%)
                                                </td>
                                                <td class="text-right">
                                                    <?php 
                                                    // Calculate tax at a flat 25% of subtotal
                                                    $tax_amount = $total * 0.25;
                                                    echo app_format_money($tax_amount, $base_currency->name); 
                                                    ?>
                                                </td>
                                            </tr>
                                            
                                            <?php if (!empty($shipping_cost)) { ?>
                                                <tr id="shipping_costs">
                                                    <td>
                                                        Troškovi dostave
                                                        <small>(<?php echo app_format_money($base_shipping_cost, $base_currency->name) . ' + ' . $shipping_tax . '%'; ?>)</small>
                                                    </td>
                                                    <td class="shipping_costs text-right">
                                                        <?php echo form_hidden('shipping_cost', $shipping_cost); ?>
                                                        <?php echo app_format_money($shipping_cost, $base_currency->name); ?>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                            
                                            <tr class="total-row">
                                                <td><strong>Ukupna procjena</strong></td>
                                                <td class="total text-right">
                                                    <strong><?php 
                                                    // Calculate final total with fixed 25% tax
                                                    $final_total = $total + $tax_amount + $shipping_cost;
                                                    
                                                    // Apply coupon if there is one (handled by AJAX)
                                                    // The total will be updated via JavaScript when a coupon is applied
                                                    
                                                    echo app_format_money($final_total, $base_currency->name); 
                                                    ?></strong>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    
                                    <p class="text-muted small">* Cijena je prikazana s uključenim PDV-om.</p>
                                    
                                    <div class="cart-products-summary mt-4">
                                        <h5 class="summary-heading">Odabrani proizvodi</h5>
                                        <div class="selected-products-list">
                                            <?php foreach ($products as $product) { ?>
                                                <div class="selected-product-item">
                                                    <div class="product-mini-name">
                                                        <?php echo htmlspecialchars($product->product_name); ?>
                                                        <?php if (isset($product->variation_value) && !empty($product->variation_value)) { ?>
                                                            <span class="badge badge-secondary"><?php echo htmlspecialchars($product->variation_value); ?></span>
                                                        <?php } ?>
                                                        <?php if (isset($product->selected_height) && !empty($product->selected_height)) { ?>
                                                            <span class="badge badge-info"><?php echo htmlspecialchars($product->selected_height); ?></span>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="product-mini-qty">
                                                        <span class="badge badge-primary"><?php echo $product->quantity; ?> m1</span>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    
                                    <div class="text-center mt-4">
                                        <a href="<?php echo site_url('home/checkout'); ?>" class="btn btn-lg btn-primary btn-block mb-3">
                                            <i class="fa fa-shopping-cart mr-2"></i> Zatraži ponudu
                                        </a>
                                        <a href="<?php echo site_url('products'); ?>" class="btn btn-outline-secondary">
                                            <i class="fa fa-arrow-left mr-2"></i> Nastavi pregledavanje
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Quantity controls
    const quantities = document.querySelectorAll('.quantity-input');
    
    quantities.forEach(function(qty) {
        const decreaseBtn = qty.parentElement.querySelector('.decrease');
        const increaseBtn = qty.parentElement.querySelector('.increase');
        
        // Remove existing listeners by cloning and replacing buttons
        const newDecreaseBtn = decreaseBtn.cloneNode(true);
        const newIncreaseBtn = increaseBtn.cloneNode(true);
        
        decreaseBtn.parentNode.replaceChild(newDecreaseBtn, decreaseBtn);
        increaseBtn.parentNode.replaceChild(newIncreaseBtn, increaseBtn);
        
        // Add new event listeners with proper step handling
        newDecreaseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            let value = parseInt(qty.value);
            let min = parseInt(qty.getAttribute('min')) || 1;
            
            if (value > min) {
                qty.value = value - 1;
                updateCartItem(qty);
            }
            
            return false;
        });
        
        newIncreaseBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            let value = parseInt(qty.value);
            let max = parseInt(qty.getAttribute('max')) || 100;
            
            if (value < max) {
                qty.value = value + 1;
                updateCartItem(qty);
            }
            
            return false;
        });
        
        // Direct input handling
        qty.addEventListener('input', function() {
            let value = parseInt(this.value);
            let min = parseInt(this.getAttribute('min')) || 1;
            let max = parseInt(this.getAttribute('max')) || 100;
            
            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
            }
        });
        
        qty.addEventListener('change', function() {
            let value = parseInt(this.value);
            let min = parseInt(this.getAttribute('min')) || 1;
            let max = parseInt(this.getAttribute('max')) || 100;
            
            if (isNaN(value) || value < min) {
                this.value = min;
            } else if (value > max) {
                this.value = max;
            }
            
            updateCartItem(this);
        });
    });
    
    /**
     * Helper function to parse currency values from text
     * Handles different number formats and currency symbols
     */
    function parseCurrencyValue(text) {
        // For debugging
        console.log('Parsing currency value from:', text);
        
        if (!text || typeof text !== 'string') {
            console.error('Invalid input to parseCurrencyValue:', text);
            return 0;
        }
        
        // Direct matching for standard formats with known decimal separators
        // Match pattern: 123,45 or 123.45 (captures whole and decimal parts separately)
        const decimalMatch = text.match(/(\d+)[,\.](\d+)/);
        
        if (decimalMatch) {
            // We have groups: [1]=whole number part, [2]=decimal part
            // Combine them properly: whole + '.' + decimal
            const parsedValue = parseFloat(decimalMatch[1] + '.' + decimalMatch[2]);
            console.log('Matched decimal value:', parsedValue);
            return parsedValue;
        }
        
        // Fallback: remove all non-numeric characters except for dots and commas
        const numericValue = text.replace(/[^0-9,.]/g, '');
        console.log('Fallback - cleaned value:', numericValue);
        
        // Only has digits (no decimal separator)
        if (!numericValue.includes(',') && !numericValue.includes('.')) {
            return parseInt(numericValue);
        }
        
        // Handle European format (1.234,56)
        if (numericValue.includes('.') && numericValue.includes(',') && 
            numericValue.lastIndexOf(',') > numericValue.lastIndexOf('.')) {
            // Remove dots and convert comma to dot
            const normalizedValue = numericValue.replace(/\./g, '').replace(',', '.');
            console.log('European format:', normalizedValue);
            return parseFloat(normalizedValue);
        }
        
        // Handle US/UK format (1,234.56)
        if (numericValue.includes(',') && numericValue.includes('.') && 
            numericValue.lastIndexOf('.') > numericValue.lastIndexOf(',')) {
            // Remove commas
            const normalizedValue = numericValue.replace(/,/g, '');
            console.log('US/UK format:', normalizedValue);
            return parseFloat(normalizedValue);
        }
        
        // Handle only comma decimal separator
        if (numericValue.includes(',') && !numericValue.includes('.')) {
            const normalizedValue = numericValue.replace(',', '.');
            console.log('Comma decimal separator:', normalizedValue);
            return parseFloat(normalizedValue);
        }
        
        // Default case - assume it's in correct format for parseFloat
        console.log('Default parsing:', numericValue);
        return parseFloat(numericValue);
    }
    
    function updateCartItem(input) {
        const productId = input.dataset.product_id;
        const variationId = input.dataset.product_variation_id || '';
        const quantity = input.value;
        
        console.log('Updating cart item:', {
            productId,
            variationId: variationId || 'none',
            quantity
        });
        
        // Store the input element for focus management
        const activeInput = input;
        
        // Find the row and get price data
        const row = input.closest('tr');
        const priceCell = row.querySelector('.price-col');
        const totalCell = row.querySelector('.total-col');
        
        // Extract price using the helper function - make sure to extract just the price without /m1
        const priceText = priceCell.textContent.trim();
        const unitPrice = parseCurrencyValue(priceText);
        
        // If we couldn't parse a valid price, don't proceed
        if (isNaN(unitPrice) || unitPrice <= 0) {
            console.error('Failed to extract a valid unit price', unitPrice);
            return;
        }
        
        // Calculate the new total
        const newTotal = unitPrice * quantity;
        
        // Get CSRF token
        const csrfToken = '<?php echo $this->security->get_csrf_hash(); ?>';
        const csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
        
        // Show loading indicator
        totalCell.classList.add('updating');
        const loadingIndicator = document.createElement('span');
        loadingIndicator.className = 'loading-indicator';
        loadingIndicator.innerHTML = ' <i class="fa fa-spinner fa-spin"></i>';
        totalCell.appendChild(loadingIndicator);
        
        // Use AJAX to update cart
        $.ajax({
            url: '<?php echo site_url('home/update_cart_item'); ?>',
            type: 'POST',
            data: {
                [csrfName]: csrfToken,
                product_id: productId,
                variation_id: variationId,
                quantity: quantity
            },
            success: function(response) {
                try {
                    // Remove loading indicator
                    totalCell.classList.remove('updating');
                    const loadingElement = totalCell.querySelector('.loading-indicator');
                    if (loadingElement) {
                        loadingElement.remove();
                    }
                    
                    console.log('Response:', response);
                    // Parse the response and update cart data
                    const cartData = JSON.parse(response);
                    
                    if (cartData.success) {
                        // Format the total with proper decimal places and thousands separators
                        let formattedTotal = '<?php echo $base_currency->symbol; ?>' + newTotal.toFixed(2).replace('.', ',');
                        
                        // Update the total in the cart display
                        totalCell.innerHTML = formattedTotal;
                        
                        // Now update the cart summary totals
                        updateCartTotals();
                        
                        // Update the product summary section
                        updateProductSummary(productId, variationId, quantity);
                        
                        // Keep focus on the active input
                        if (activeInput) {
                            activeInput.focus();
                        }
                    } else {
                        // If unsuccessful, show error and reset quantity
                        console.error('Failed to update cart:', cartData.message);
                        alert('Došlo je do greške prilikom ažuriranja košarice: ' + cartData.message);
                        location.reload(); // Reload to get proper state
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    alert('Došlo je do greške prilikom ažuriranja košarice.');
                    location.reload(); // Reload to get proper state
                }
            },
            error: function(xhr, status, error) {
                console.error('Error updating cart:', error);
                console.log('Response:', xhr.responseText);
                alert('Došlo je do greške prilikom ažuriranja košarice.');
                // Remove loading indicator in case of error
                totalCell.classList.remove('updating');
                const loadingElement = totalCell.querySelector('.loading-indicator');
                if (loadingElement) {
                    loadingElement.remove();
                }
                location.reload(); // Reload to get proper state
            }
        });
    }
    
    function updateCartTotals() {
        console.log('Updating cart totals...');
        
        // Calculate new subtotal, tax, and total
        let subtotal = 0;
        
        // Go through each product row and add up the totals
        document.querySelectorAll('.product-row').forEach(function(row) {
            const totalCell = row.querySelector('.total-col');
            if (totalCell && !totalCell.classList.contains('updating')) {
                const totalText = totalCell.textContent.trim();
                // Use our helper function to extract the numeric value
                const itemTotal = parseCurrencyValue(totalText);
                if (!isNaN(itemTotal)) {
                    subtotal += itemTotal;
                    console.log('Added item total to subtotal:', itemTotal, 'New subtotal:', subtotal);
                }
            }
        });
        
        // Format numbers with 2 decimal places
        subtotal = parseFloat(subtotal.toFixed(2));
        console.log('Final subtotal (rounded):', subtotal);
        
        // Get coupon discount if any
        let couponDiscount = 0;
        const couponDiscountElement = document.querySelector('.order-totals .justify-content-between:nth-child(2) span:last-child');
        if (couponDiscountElement && couponDiscountElement.textContent.includes('-')) {
            couponDiscount = parseCurrencyValue(couponDiscountElement.textContent);
            console.log('Coupon discount:', couponDiscount);
        }
        
        // Calculate tax (25%)
        const taxAmount = parseFloat((subtotal * 0.25).toFixed(2));
        console.log('Tax amount (25%):', taxAmount);
        
        // Get shipping cost if any
        let shippingCost = 0;
        const shippingCostElement = document.querySelector('.shipping_costs');
        if (shippingCostElement) {
            shippingCost = parseCurrencyValue(shippingCostElement.textContent);
            if (isNaN(shippingCost)) {
                shippingCost = 0;
            }
            console.log('Shipping cost:', shippingCost);
        }
        
        // Update final total - ensure we're using fixed decimal places for calculation
        const finalTotal = parseFloat((subtotal - couponDiscount + taxAmount + shippingCost).toFixed(2));
        console.log('Final total:', finalTotal);
        
        // Update the displayed totals
        const subtotalCell = document.querySelector('.subtotal');
        if (subtotalCell) {
            subtotalCell.innerHTML = '<?php echo $base_currency->symbol; ?>' + subtotal.toFixed(2).replace('.', ',');
        }
        
        const taxCell = document.querySelector('.tax-area .text-right');
        if (taxCell) {
            taxCell.innerHTML = '<?php echo $base_currency->symbol; ?>' + taxAmount.toFixed(2).replace('.', ',');
        }
        
        const totalCell = document.querySelector('.total');
        if (totalCell) {
            totalCell.innerHTML = '<strong><?php echo $base_currency->symbol; ?>' + finalTotal.toFixed(2).replace('.', ',') + '</strong>';
        }
        
        // Log the calculation for debugging
        console.log({
            subtotal: subtotal,
            couponDiscount: couponDiscount,
            tax: taxAmount, 
            shipping: shippingCost,
            final: finalTotal
        });
    }
    
    function updateProductSummary(productId, variationId, newQuantity) {
        console.log('Updating product summary for product:', productId, 'variation:', variationId, 'quantity:', newQuantity);
        
        // Find product info from the cart rows first
        const productRows = document.querySelectorAll('.product-row');
        let targetProduct = null;
        let targetVariation = null;
        let targetHeight = null;
        
        // First find the product details we're updating
        for (let row of productRows) {
            const rowInputs = row.querySelectorAll('input[type="hidden"]');
            let rowProductId = null;
            let rowVariationId = null;
            let rowHeight = null;
            
            // Get all data from hidden inputs
            rowInputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.includes('[product_id]')) {
                    rowProductId = input.value;
                } else if (name && name.includes('[product_variation_id]')) {
                    rowVariationId = input.value;
                } else if (name && name.includes('[selected_height]')) {
                    rowHeight = input.value;
                }
            });
            
            // Check if this is the row we're updating
            if (rowProductId == productId && (rowVariationId == variationId || (!rowVariationId && !variationId))) {
                targetProduct = {
                    name: row.querySelector('.product-title a').textContent.trim(),
                    id: rowProductId
                };
                
                // Get variation info if exists
                if (row.querySelector('.variant-value')) {
                    targetVariation = row.querySelector('.variant-value').textContent.trim();
                }
                
                // Get height info if exists
                if (rowHeight) {
                    targetHeight = rowHeight;
                }
                
                break;
            }
        }
        
        if (!targetProduct) {
            console.error('Could not find target product in cart rows');
            return;
        }
        
        console.log('Found target product:', targetProduct.name, 'variation:', targetVariation, 'height:', targetHeight);
        
        // Now find the matching summary item
        const summaryItems = document.querySelectorAll('.selected-product-item');
        for (let item of summaryItems) {
            const miniName = item.querySelector('.product-mini-name');
            if (!miniName) continue;
            
            // Check product name match
            const itemName = miniName.childNodes[0].textContent.trim();
            if (itemName !== targetProduct.name) continue;
            
            // Check badges for variation and height
            const badges = item.querySelectorAll('.badge');
            let variationMatch = !targetVariation; // If no target variation, count as match
            let heightMatch = !targetHeight; // If no target height, count as match
            
            // Check each badge
            badges.forEach(badge => {
                const badgeText = badge.textContent.trim();
                
                // If we have a target variation and this badge matches it
                if (targetVariation && badgeText === targetVariation) {
                    variationMatch = true;
                }
                
                // If we have a target height and this badge matches it
                if (targetHeight && badgeText === targetHeight) {
                    heightMatch = true;
                }
            });
            
            // If everything matches, update the quantity
            if (variationMatch && heightMatch) {
                const qtyBadge = item.querySelector('.product-mini-qty .badge');
                if (qtyBadge) {
                    qtyBadge.textContent = newQuantity + ' m1';
                    console.log('Updated quantity badge for', targetProduct.name, 'to', newQuantity);
                    return; // Found and updated
                }
            }
        }
        
        console.warn('Could not find matching product in summary');
    }
    
    // Coupon functionality
    const applyBtn = document.querySelector('.apply_coupon');
    const removeBtn = document.querySelector('.remove_coupon');
    const couponInput = document.getElementById('coupon_code');
    
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            const code = couponInput.value.trim();
            
            if (code !== '') {
                // Get CSRF token
                const csrfToken = '<?php echo $this->security->get_csrf_hash(); ?>';
                const csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
                
                $.ajax({
                    url: '<?php echo site_url('products/client/apply_coupon'); ?>',
                    type: 'POST',
                    data: {
                        [csrfName]: csrfToken,
                        coupon_code: code
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            
                            if (data.status) {
                                $('#coupon_discount').removeClass('d-none');
                                $('.coupon_discount').html(data.html);
                                $('input[name="coupon_id"]').val(data.coupon_id);
                                
                                applyBtn.classList.add('d-none');
                                removeBtn.classList.remove('d-none');
                                
                                // Update totals
                                $('.total').html(data.total_html);
                            } else {
                                alert(data.message);
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error applying coupon:', error);
                    }
                });
            }
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener('click', function() {
            // Get CSRF token
            const csrfToken = '<?php echo $this->security->get_csrf_hash(); ?>';
            const csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
            
            $.ajax({
                url: '<?php echo site_url('products/client/remove_coupon'); ?>',
                type: 'POST',
                data: {
                    [csrfName]: csrfToken
                },
                success: function(response) {
                    try {
                        const data = JSON.parse(response);
                        
                        if (data.status) {
                            $('#coupon_discount').addClass('d-none');
                            $('input[name="coupon_id"]').val('');
                            couponInput.value = '';
                            
                            removeBtn.classList.add('d-none');
                            applyBtn.classList.remove('d-none');
                            
                            // Update total
                            $('.total').html(data.total_html);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                }
            });
        });
    }
    
    // Remove item functionality
    const removeButtons = document.querySelectorAll('.remove-item');
    
    removeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const variationId = this.dataset.variationId;
            const row = this.closest('tr');
            
            if (confirm('Jeste li sigurni da želite ukloniti ovaj proizvod iz košarice?')) {
                // Get CSRF token
                const csrfToken = '<?php echo $this->security->get_csrf_hash(); ?>';
                const csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
                
                $.ajax({
                    url: '<?php echo site_url('products/client/remove_cart'); ?>',
                    type: 'POST',
                    data: {
                        [csrfName]: csrfToken,
                        product_id: productId,
                        product_variation_id: variationId
                    },
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            
                            // Always consider it a success
                            // Remove the row with animation
                            row.style.transition = 'all 0.3s';
                            row.style.opacity = '0';
                            row.style.height = '0';
                            
                            setTimeout(function() {
                                row.remove();
                                
                                // Update cart count in header
                                const cartCountElements = document.querySelectorAll('.cart-count');
                                const cartCount = data.cart_data ? data.cart_data.length : 0;
                                
                                cartCountElements.forEach(function(element) {
                                    if (cartCount > 0) {
                                        element.textContent = cartCount;
                                        element.classList.remove('d-none');
                                    } else {
                                        element.classList.add('d-none');
                                        // If cart is empty, reload to show empty cart message
                                        window.location.reload();
                                    }
                                });
                                
                                // If cart still has items but we need updated totals
                                if (cartCount > 0) {
                                    // Update totals without full page reload by calculating new values
                                    // For simplicity, we're reloading for now
                                    window.location.reload();
                                }
                            }, 300);
                        } catch (e) {
                            console.error('Error parsing response:', e);
                            // Even if there's an error parsing the response, remove the item
                            window.location.reload();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error removing item:', error);
                        // Still reload the page to show updated cart
                        window.location.reload();
                    }
                });
            }
        });
    });
});
</script>

<style>
/* Cart Styles */
.cart-title {
    margin-bottom: 2rem;
}

.cart-title h1 {
    font-size: 28px;
    font-weight: 600;
    color: #333;
}

.table-cart {
    margin-bottom: 2rem;
}

.table-cart thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
    color: #333;
    padding: 1rem;
}

.product-col {
    display: flex;
    align-items: center;
}

.product-image-container {
    width: 100px;
    height: 100px;
    margin-right: 1.5rem;
    margin-bottom: 0;
}

.product-image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.product-title {
    font-size: 16px;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.product-title a {
    color: #333;
    text-decoration: none;
}

.product-variant {
    font-size: 14px;
    margin-bottom: 0.5rem;
}

.variant-label {
    font-weight: 500;
}

.product-description {
    font-size: 13px;
    color: #777;
}

.quantity-container {
    display: flex;
    align-items: center;
    max-width: 130px;
    margin: 0 auto;
}

.quantity-container button {
    background-color: #6c7569;
    color: white;
    border: none;
    padding: 0;
    cursor: pointer;
    font-size: 18px;
    flex: 1;
}

.quantity-btn {
    width: 36px;
    height: 36px;
    background-color: #6c7569;
    border: none;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    flex-shrink: 0;
    padding: 0;
}

.quantity-btn:hover {
    background-color: #596257;
}

.quantity-btn i {
    color: white;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    line-height: 1;
}

.quantity-input {
    width: 50px;
    height: 36px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 0 5px;
    font-size: 14px;
    padding: 0 5px;
}

.quantity-input::-webkit-inner-spin-button,
.quantity-input::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.quantity-input[type=number] {
    -moz-appearance: textfield;
}

.total-col {
    font-weight: 600;
    font-size: 16px;
}

.btn-remove {
    color: #cc0000;
    font-size: 18px;
}

.cart-summary {
    background-color: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    margin-left: auto;
}

.cart-summary h4 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 1.5rem;
    color: #333;
}

.table-totals {
    margin-bottom: 1.5rem;
}

.table-totals td {
    padding: 0.75rem 0;
    border-top: 1px solid #dee2e6;
}

.total-row {
    font-size: 18px;
    border-top: 2px solid #dee2e6 !important;
}

.checkout-methods {
    margin-top: 2rem;
}

.checkout-methods .btn {
    margin-bottom: 0.5rem;
}

.coupon-section {
    margin-bottom: 2rem;
}

.coupon-section h4 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #333;
}

/* Empty cart styles */
.cart-empty-container {
    padding: 4rem 0;
}

.cart-empty-title {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #333;
}

.cart-empty-text {
    font-size: 16px;
    color: #777;
    max-width: 500px;
    margin: 0 auto 2rem;
}

/* Center align the qty-col header and cells */
.qty-col, 
td.qty-col,
th.qty-col {
    text-align: center !important;
}

/* Make sure the quantity cell is aligned center */
.table-cart td.qty-col {
    text-align: center !important;
}

/* Align quantity container in the middle of the cell */
td .quantity-container {
    margin: 0 auto;
    justify-content: center;
}

/* Additional alignment CSS */
@media (max-width: 767px) {
    /* Restructure mobile view of the cart table */
    .table-cart tbody tr {
        display: flex;
        flex-wrap: wrap;
        position: relative;
        padding: 10px 0;
        border-bottom: 1px solid #dee2e6;
    }
    
    .table-cart tbody tr td.product-col {
        flex: 0 0 100%;
        width: 100%;
        display: flex;
        padding-right: 40px; /* Make space for the remove button */
    }
    
    /* Position the remove button absolutely in the top right corner */
    .table-cart tbody tr td.action-col {
        position: absolute;
        top: 10px;
        right: 10px;
        border: none;
        padding: 0;
    }
    
    /* Make price and quantity sit side by side */
    .table-cart tbody tr td.price-col,
    .table-cart tbody tr td.qty-col {
        flex: 1;
        border: none;
        padding: 10px 5px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Position the total column as a full width row at the bottom */
    .table-cart tbody tr td.total-col {
        flex: 0 0 100%;
        width: 100%;
        text-align: right;
        border: none;
        padding-top: 10px;
        border-top: 1px dashed #eee;
    }
    
    /* Hide table headers on mobile */
    .table-cart thead {
        display: none;
    }
    
    /* Add labels for the mobile view */
    .table-cart tbody tr td.price-col:before {
        content: "Cijena: ";
        font-weight: bold;
        margin-right: 5px;
    }
    
    .table-cart tbody tr td.qty-col:before {
        content: "Kol: ";
        font-weight: bold;
        margin-right: 5px;
    }
    
    .table-cart .quantity-container {
        margin: 0;
        max-width: 110px;
    }
    
    /* Make the quantity controls more compact for mobile */
    .quantity-btn {
        width: 30px;
        height: 30px;
    }
    
    .quantity-input {
        width: 40px;
        height: 30px;
    }
}

/* Ensure all table headers are properly aligned */
.table-cart th {
    vertical-align: middle !important;
}

/* Ensure the price columns are consistent */
.price-col, td.price-col {
    text-align: center !important;
}

/* Align cells vertically */
.table-cart td {
    vertical-align: middle !important;
}

/* Loading indicator styles */
.loading-indicator {
    display: inline-block;
    margin-left: 5px;
    color: #666;
}

/* Cell updating state */
.updating {
    position: relative;
    opacity: 0.7;
}

/* Ensure price formatting is consistent */
.price-col, .total-col, .subtotal, .total {
    font-weight: 600;
}

/* Cart form styles */
.cart-form {
    background-color: #ffffff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.cart-form .section-title {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
    color: #333;
}

.cart-form .form-group {
    margin-bottom: 20px;
}

.cart-form label {
    font-weight: 500;
    color: #555;
    margin-bottom: 5px;
}

.cart-form .form-control {
    height: 45px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

.cart-form .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.cart-form textarea.form-control {
    height: auto;
}

.cart-form .custom-control-label {
    font-size: 14px;
    line-height: 1.5;
}

.cart-form .form-control.is-invalid {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23dc3545' viewBox='0 0 12 12'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right calc(.375em + .1875rem) center;
    background-size: calc(.75em + .375rem) calc(.75em + .375rem);
}

.cart-form .invalid-feedback {
    display: block;
    color: #dc3545;
    font-size: 13px;
    margin-top: 4px;
}

/* Cart summary styles */
.cart-summary {
    background-color: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    margin-left: auto;
}

.cart-summary .section-title {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e5e5e5;
    color: #333;
}

.cart-summary .table-totals {
    margin-bottom: 15px;
}

.cart-summary .table-totals td {
    padding: 10px 0;
    border-top: 1px solid #e5e5e5;
}

.cart-summary .total-row td {
    padding-top: 15px;
    font-size: 18px;
    border-top: 2px solid #dee2e6;
}

.cart-summary .summary-heading {
    font-size: 16px;
    margin-bottom: 15px;
    font-weight: 600;
    color: #333;
}

.cart-products-summary {
    background-color: #fff;
    border-radius: 6px;
    padding: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.selected-product-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.selected-product-item:last-child {
    border-bottom: none;
}

.product-mini-name {
    font-size: 14px;
    margin-right: 10px;
}

.product-mini-name .badge {
    margin-left: 5px;
    font-weight: normal;
}

.product-mini-qty .badge {
    font-size: 12px;
    padding: 4px 8px;
}

/* Form button */
.form-action .btn-primary {
    height: 50px;
    font-weight: 600;
    transition: all 0.3s;
}

.form-action .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Responsive adjustments */
@media (max-width: 991px) {
    .cart-form, .cart-summary {
        margin-bottom: 20px;
    }
}

/* Make cart summary full width on mobile */
@media (max-width: 767px) {
    .col-lg-5, .col-lg-7 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .cart-summary {
        width: 100%;
        margin-top: 20px;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    }
    
    /* Improve mobile styling for checkout button */
    .cart-summary .text-center .btn-lg {
        padding: 15px;
        font-size: 18px;
        width: 100%;
    }
    
    .cart-summary .section-title {
        font-size: 20px;
        text-align: center;
    }
    
    .cart-summary .table-totals {
        font-size: 16px;
    }
    
    .cart-summary .total-row {
        font-size: 20px;
    }
    
    /* Improve spacing for cart summary elements */
    .cart-products-summary {
        margin-top: 20px;
        padding: 15px;
    }
    
    /* Make the coupon section look better on mobile */
    .coupon-section {
        padding: 15px;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 20px;
    }
}

@media (min-width: 992px) {
    .cart-summary {
        width: 100%;
        max-width: 450px;
        float: right;
        position: sticky;
        top: 20px;
    }
    
    .cart-summary .section-title {
        font-size: 22px;
        font-weight: 700;
    }
    
    .cart-summary .table-totals {
        font-size: 16px;
    }
    
    .cart-summary .total-row {
        font-size: 20px;
    }
    
    .cart-summary .text-center .btn-lg {
        padding: 15px 20px;
        font-size: 18px;
        font-weight: 600;
    }
    
    .cart-summary .btn-primary {
        background-color: #ae8553;
        border-color: #ae8553;
        transition: all 0.3s ease;
    }
    
    .cart-summary .btn-primary:hover {
        background-color: #947143;
        border-color: #826339;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }
}
</style>

<script>
$(document).ready(function() {
    adjustLayoutForMobile();
    $(window).resize(adjustLayoutForMobile);

    // Apply coupon button handler
    $('#apply_coupon_btn').on('click', function() {
        const couponCode = $('#coupon_code').val().trim();
        if (!couponCode) {
            $('#coupon_message').html('<div class="text-danger mt-2">Unesite kod kupona</div>');
            return;
        }
        
        // Show loading state
        $(this).prop('disabled', true);
        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Provjera...');
        
        // AJAX call to validate coupon
        $.ajax({
            url: '<?php echo site_url('home/validate_coupon'); ?>',
            type: 'POST',
            data: {
                coupon_code: couponCode,
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#coupon_message').html('<div class="text-success mt-2">' + response.message + '</div>');
                    $('input[name="coupon_id"]').val(response.coupon_id);
                    $('.apply_coupon').addClass('d-none');
                    $('.remove_coupon').removeClass('d-none');
                    
                    // Update cart totals
                    updateCartTotals(response);
                } else {
                    $('#coupon_message').html('<div class="text-danger mt-2">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#coupon_message').html('<div class="text-danger mt-2">Došlo je do greške. Pokušajte ponovo.</div>');
            },
            complete: function() {
                // Reset button state
                $('#apply_coupon_btn').prop('disabled', false);
                $('#apply_coupon_btn').html('Primijeni');
            }
        });
    });
    
    // Remove coupon button handler
    $('.remove_coupon').on('click', function() {
        $('input[name="coupon_id"]').val('');
        $('#coupon_code').val('');
        $('#coupon_message').empty();
        $('.apply_coupon').removeClass('d-none');
        $('.remove_coupon').addClass('d-none');
        
        // Reset cart totals
        $.ajax({
            url: '<?php echo site_url('home/reset_coupon'); ?>',
            type: 'POST',
            data: {
                <?php echo $this->security->get_csrf_token_name(); ?>: '<?php echo $this->security->get_csrf_hash(); ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateCartTotals(response);
                }
            }
        });
    });
    
    function updateCartTotals(data) {
        if (data.cart_total) {
            $('.cart_total').text(data.cart_total);
        }
        
        if (data.discount_amount) {
            $('.discount-row').removeClass('d-none');
            $('.discount_amount').text('-' + data.discount_amount);
        } else {
            $('.discount-row').addClass('d-none');
        }
        
        if (data.grand_total) {
            $('.grand_total').text(data.grand_total);
        }
    }
    
    function adjustLayoutForMobile() {
        if ($(window).width() < 768) {
            // On mobile, make sure both columns are full width in their original order
            // No need to change the DOM structure
        } else {
            // On desktop, we can revert to the original layout if needed
            $('.cart-summary').insertAfter('.col-lg-6:first');
        }
    }
});
</script> 