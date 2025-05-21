<div class="container coll-composite-fences">
<style>
/* Category image styles */
.top_fence_filter_ele img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    border-radius: 8px;
    transition: transform 0.3s ease;
}

.top_fence_filter_ele:hover img {
    transform: scale(1.05);
}

.top_fence_filter_ele {
    min-width: 180px;
    max-width: 220px;
}

.top_fence_filter_ele a {
    display: block;
    text-decoration: none;
}

.top_fence_filter_ele p {
    margin-top: 10px;
    color: var(--copy-dark-grey-1, #1D1D1D);
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    line-height: 26px;
}

/* Mobile product catalog styles */
@media (max-width: 767px) {
    .products-container .product-default.inner-quickview {
        background-color: #fff;
    }
    
    .products-container .product-default figure img {
        object-fit: contain;
    }
    
    .products-container .col-6 {
        padding-left: 10px;
        padding-right: 10px;
        margin-bottom: 20px;
    }
}
</style>
				<nav aria-label="breadcrumb" class="breadcrumb-nav">
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="<?= site_url('/'); ?>"><i class="icon-home"></i></a></li>
						<li class="breadcrumb-item"><a href="<?= site_url('products'); ?>">Proizvodi</a></li>
						<?php if(!empty($category) && isset($category_name)): ?>
							<li class="breadcrumb-item active" aria-current="page"><?php echo $category_name; ?></li>
						<?php elseif(!empty($q)): ?>
							<li class="breadcrumb-item active" aria-current="page">Pretraga: <?php echo $q; ?></li>
						<?php endif; ?>
					</ol>
				</nav>

				<!--new section -->
				<section class="fence-wizaed-wrapper">
					<div class="page-width">
					<?php if(empty($q) && empty($category)): ?>
						<div class="coll-wrapper">

						
						<div class="coll-title-wrapper">
							<h2>Vrhunska preciznost & <br/>kvaliteta radova</h2>
							
							
							<div class="product-description-short">
							Centar Ograda specijaliziran je za izradu i prodaju visokokvalitetnih ograda, kombinirajući preciznu izradu s dugotrajnošću. Naša rješenja prilagođavamo potrebama klijenata, osiguravajući funkcionalnost i estetiku u svakom projektu. S ponosom pružamo proizvode vrhunske kvalitete koji oplemenjuju svaki prostor.
					<!-- ...<a class="readmore" href="#">Show More</a> -->
							</div>
							<!-- <div class="product-description-full" style="display:none;">
								<p dir="ltr"><span>Looking to install a garden fence that looks great and requires no maintenance? Then you need composite fencing.</span></p>
					<p dir="ltr"><span>Unlike real wood, composite fence panels don't need to be replaced – nor painted, sealed, or stained. Therefore, opting for modern </span><span>composite fencing panels</span><span> will not only save you time, effort, and money, but it's also much more sustainable.</span></p>
								<br><a class="readless" href="#">Show Less</a>
							</div> -->
							
						</div>
						

					
						<div class="filter-collection-wrapper start-products-slider">
							<input type="hidden" a="" ss="" tt="">
							<ul class="filters_wrp scroll-two">
							
								<?php if(isset($product_categories) && !empty($product_categories)): ?>
									<?php foreach($product_categories as $cat): ?>
										<?php 
										// Ensure we have all required fields
										$cat_id = isset($cat['id']) ? $cat['id'] : (isset($cat['p_category_id']) ? $cat['p_category_id'] : 0);
										$cat_name = isset($cat['name']) ? $cat['name'] : (isset($cat['p_category_name']) ? $cat['p_category_name'] : 'Unnamed Category');
										
										if (empty($cat_id)) continue; // Skip if no ID
										?>
										<li class="top_fence_filter_ele" data-tag="<?php echo $cat_id; ?>">
											<a href="<?php echo site_url('products?category=' . $cat_id); ?>">
												<?php 
												// Map category names to the correct image paths
												$category_image = 'assets/front/assets/images/no-image.jpg';
												
												// Check if category name exists and map to correct image
												if (isset($cat_name)) {
													$category_name_lower = mb_strtolower(trim($cat_name));
													
													// Use exact file paths we've verified exist with correct casing
													if (strpos($category_name_lower, 'čelič') !== false || 
													    strpos($category_name_lower, 'celic') !== false || 
													    strpos($category_name_lower, 'metal') !== false || 
													    strpos($category_name_lower, 'metalna') !== false) {
														$category_image = 'assets/front/assets/images/ograde/celicneograde.jpg';
													} 
													else if (strpos($category_name_lower, 'aluminij') !== false) {
														$category_image = 'assets/front/assets/images/ograde/aluminijskeograde.jpg';
													} 
													else if (strpos($category_name_lower, 'kompozit') !== false || 
													        strpos($category_name_lower, 'wpc') !== false) {
														$category_image = 'assets/front/assets/images/ograde/kompozitneograde.jpeg';
													} 
													else if (strpos($category_name_lower, 'stakl') !== false) {
														$category_image = 'assets/front/assets/images/ograde/stakleneograde.jpg';
													} 
													else if (isset($cat['image']) && !empty($cat['image'])) {
														// Fallback to the original image path if category name doesn't match
														$category_image_path = 'modules/products/uploads/categories/' . $cat['image'];
														if (file_exists(FCPATH . $category_image_path)) {
															$category_image = $category_image_path;
														}
													}
													
													// Final fallback if the file doesn't exist
													if (!file_exists(FCPATH . $category_image)) {
														$category_image = 'assets/front/assets/images/no-image.jpg';
													}
												}
												
												// Ensure the image URL is absolute
												$image_url = base_url($category_image);
												?>
												<img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($cat_name); ?>">
												<p><?php echo htmlspecialchars($cat_name); ?><span class="btm_small_type_info"></span></p>
											</a>
										</li>
									<?php endforeach; ?>
								<?php else: ?>
									<li class="top_fence_filter_ele">
										<p>Nema dostupnih kategorija</p>
									</li>
								<?php endif; ?>
							
							</ul>
						</div>
						

						<div class="emptybox">&nbsp;</div>
						</div>

						<?php else: ?>

							<div class="emptybox">&nbsp;</div>
							<?php if(!empty($category) && isset($category_name)): ?>
								<h3><?php echo $category_name; ?></h3>
								<p><a href="<?php echo site_url('products'); ?>" class="btn btn-sm btn-outline-secondary">Povratak na sve kategorije</a></p>
							<?php elseif(!empty($q)): ?>
								<h3>Pretraga za: <?php echo $q; ?></h3>
							<?php endif; ?>
							<div class="emptybox">&nbsp;</div>

						<?php endif; ?>
						<form id="msform" class="toggle-wizard-container" style="display: none;">
						<!-- progressbar -->
						<!-- <ul id="progressbar">
							
							<li class="step-li step2-li active current"><span class="step-name">1. Fence type</span><span class="step-end medium-up--hide">Step 2</span></li>
							
						</ul> -->
						
						<!-- <fieldset class="step-content step-two-content active">
							<div class="inner-wizard">
							<div class="section-wizard-heading">
								<h2>1. Choose your fence type</h2>
								<div class="rte">
								<p>Choose from a range of contemporary fence styles to elevate your garden.</p>
								</div>
							</div>
							<div class="section-wizard-urls slattype deco-screen-wrapper">
								<ul class="decorative-screen-type isdeco scroll-two">
								<li area-label="Full Slats" class="wizard-no-screen wizard-filter-value" data-tag="pf_t_fence_style=fence-style_Full+Slats">
									<div class="imgwrapperwizard">
									<img src="https://cdn.shopify.com/s/files/1/0520/1670/9803/files/no-screen.png?v=1675507926">
									</div>
									<p>No Screen</p>
								</li>
								<li area-label="Slats With Decorative Screen" class="wizard-decorative-top-screen wizard-filter-value" data-tag="pf_t_fence_style=fence-style_Slats+with+Decorative+Screen">
									<div class="imgwrapperwizard">
									<img src="https://cdn.shopify.com/s/files/1/0520/1670/9803/files/deco-top.png?v=16755079256">
									</div>
									<p>Decorative top</p>
								</li>
								<li area-label="Full Slatted Screen" class="wizard-full-slatted-screen wizard-filter-value" data-tag="pf_t_fence_style=fence-style_Full+Slatted+Screen">
									<div class="imgwrapperwizard">
									<img src="https://cdn.shopify.com/s/files/1/0520/1670/9803/files/full-slatted.png?v=1675507926">
									</div>
									<p>Full Slatted</p>
								</li>
								<li area-label="Full Decorative Screen Fence" class="wizard-full-decorative-screen wizard-filter-value" data-tag="pf_t_fence_style=fence-style_Full+Decorative+Screen+Fence">
									<div class="imgwrapperwizard">
									<img src="https://cdn.shopify.com/s/files/1/0520/1670/9803/files/full-decorative.png?v=1679042495">
									</div>
									<p>Full decorative</p>
								</li>
								</ul>

							<div class="scoll-indo-msg">
								<p>Scroll to see more&gt; </p>
							</div>
								
							</div>
							</div>
							<div class="dflex-wizard">
							<div class="section-wizard-heading">
								<button type="button" class="hidewizard btn">Hide Fence Builder</button>
								<a href="#" class="resetbtn-wiz">
								<span>Reset</span>
								<span>
									<svg xmlns="http://www.w3.org/2000/svg" width="21" height="19" viewBox="0 0 21 19" fill="none">
									<path d="M6.3905 7.23771C8.12725 4.22958 11.9737 3.19892 14.9819 4.93567C17.99 6.67241 19.0206 10.5189 17.2839 13.527C15.5472 16.5351 11.7007 17.5658 8.69255 15.8291C7.29495 15.0222 6.3242 13.7598 5.85954 12.3378" stroke="#1D1D1D" stroke-width="1.16397" stroke-linecap="round"></path>
									<path d="M5.87109 4.18945L5.80857 7.49502L9.11413 7.55754" stroke="#1D1D1D" stroke-width="1.16397" stroke-linecap="round" stroke-linejoin="round"></path>
									</svg>
								</span>
								</a>
							</div>
							<div class="section-wizard-urls btns">
								<div class="nextprev">
								<input type="button" name="next" class="next action-button step2" value="Next >" style="display: none;">
								</div>
							</div>
								<p class="infomsg-wizard">&nbsp;</p>
							</div>
						</fieldset> -->


						
						
						</form>
							
					</div>
				</section>
				<!-- the end new section -->
				<div class="sticky-wrapper"><nav class="toolbox sticky-header horizontal-filter filter-sorts" data-sticky-options="{'mobile': true}">
					<div class="sidebar-overlay d-lg-none"></div>
					<a href="#" class="sidebar-toggle border-0"><svg data-name="Layer 3" id="Layer_3" viewBox="0 0 32 32" xmlns="http://www.w3.org/2000/svg">
							<line x1="15" x2="26" y1="9" y2="9" class="cls-1"></line>
							<line x1="6" x2="9" y1="9" y2="9" class="cls-1"></line>
							<line x1="23" x2="26" y1="16" y2="16" class="cls-1"></line>
							<line x1="6" x2="17" y1="16" y2="16" class="cls-1"></line>
							<line x1="17" x2="26" y1="23" y2="23" class="cls-1"></line>
							<line x1="6" x2="11" y1="23" y2="23" class="cls-1"></line>
							<path d="M14.5,8.92A2.6,2.6,0,0,1,12,11.5,2.6,2.6,0,0,1,9.5,8.92a2.5,2.5,0,0,1,5,0Z" class="cls-2"></path>
							<path d="M22.5,15.92a2.5,2.5,0,1,1-5,0,2.5,2.5,0,0,1,5,0Z" class="cls-2"></path>
							<path d="M21,16a1,1,0,1,1-2,0,1,1,0,0,1,2,0Z" class="cls-3"></path>
							<path d="M16.5,22.92A2.6,2.6,0,0,1,14,25.5a2.6,2.6,0,0,1-2.5-2.58,2.5,2.5,0,0,1,5,0Z" class="cls-2"></path>
						</svg>
						<span>Filter</span>
					</a>

					<!-- <div class="toolbox-item toolbox-sort select-custom">
						<select name="orderby" class="form-control">
							<option value="menu_order" selected="selected">Default sorting</option>
							<option value="popularity">Sort by popularity</option>
							<option value="rating">Sort by average rating</option>
							<option value="date">Sort by newness</option>
							<option value="price">Sort by price: low to high</option>
							<option value="price-desc">Sort by price: high to low</option>
						</select>
					</div>End .toolbox-item -->

					<div class="toolbox-item toolbox-show ml-auto">
						<!-- <label>Show:</label> -->

						<!-- <div class="select-custom">
							<select name="count" class="form-control">
								<option value="20">20</option>
								<option value="30">30</option>
								<option value="40">40</option>
								<option value="50">50</option>
							</select>
						</div> -->
						<!-- End .select-custom -->
					</div><!-- End .toolbox-item -->

					<div class="toolbox-item layout-modes">
						<!-- <a href="category.html" class="layout-btn btn-grid active" title="Grid">
							<i class="icon-mode-grid"></i>
						</a>
						<a href="category-list.html" class="layout-btn btn-list" title="List">
							<i class="icon-mode-list"></i>
						</a> -->
					</div><!-- End .layout-modes -->
				</nav></div>

				<div class="products-container">
					<!-- Main products display -->
					<?php if(isset($products) && !empty($products)): ?>
						<div class="row">
							<?php foreach($products as $product): 
								$product_slug = slugify($product['product_name']);
								$product_url = site_url('product/' . $product_slug);
								
								// Get product image
								$imageUrl = base_url('modules/products/uploads/image-not-available.png'); // Default image
								if (!empty($product['product_image'])) {
									$productImagePath = 'modules/products/uploads/' . $product['product_image'];
									if (file_exists(FCPATH . $productImagePath)) {
										$imageUrl = base_url($productImagePath);
									}
								}
							?>
							<div class="col-6 col-sm-4 col-lg-3">
								<div class="product-default inner-quickview">
									<figure>
										<a href="<?php echo $product_url; ?>">
											<img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="height:300px; object-fit:cover;">
										</a>
										<?php if(isset($product['is_featured']) && $product['is_featured']): ?>
										<div class="label-group">
											<span class="product-label label-hot">Istaknuto</span>
										</div>
										<?php endif; ?>
									</figure>
									<div class="product-details">
										<div class="category-wrap">
											<div class="category-list">
												<a href="#"><?php echo htmlspecialchars($product['p_category_name'] ?? 'Ograde'); ?></a>
											</div>
										</div>
										<h3 class="product-title">
											<a href="<?php echo $product_url; ?>"><?php echo htmlspecialchars($product['product_name']); ?></a>
										</h3>
										<?php if (!empty($product['rate'])): ?>
										<div class="price-box">
											<span class="product-price"><?php echo $product['rate']; ?>€</span>
										</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
							<?php endforeach; ?>
						</div>
					<?php else: ?>
						<div class="alert alert-info">Nema proizvoda za prikaz.</div>
					<?php endif; ?>
				</div>
                
                <!-- Recommended Products Section -->
                <section class="product-section recommended-section">
                    <div class="container">
                        <h2 class="title title-underline pb-1 appear-animate" data-animation-name="fadeInLeftShorter">Preporučeno za vas</h2>
                        <div class="owl-carousel owl-theme appear-animate" data-owl-options="{
                        'loop': false,
                        'dots': false,
                        'nav': true,
                        'margin': 20,
                        'responsive': {
                            '0': {
                                'items': 2
                            },
                            '576': {
                                'items': 2
                            },
                            '991': {
                                'items': 4
                            }
                        }
                    }">
                            <?php 
                            if (isset($recommended_products) && is_array($recommended_products) && !empty($recommended_products)):
                            foreach ($recommended_products as $product): 
                                if (empty($product) || !isset($product['product_name'])) continue;
                                $product_slug = slugify($product['product_name']);
                                $product_url = site_url('product/' . $product_slug);
                                
                                // Get product image
                                $imageUrl = base_url('modules/products/uploads/image-not-available.png'); // Default image
                                if (!empty($product['product_image'])) {
                                    $productImagePath = 'modules/products/uploads/' . $product['product_image'];
                                    if (file_exists(FCPATH . $productImagePath)) {
                                        $imageUrl = base_url($productImagePath);
                                    }
                                }
                            ?>
                            <div class="product-default inner-quickview">
                                <figure>
                                    <a href="<?php echo $product_url; ?>">
                                        <img src="<?php echo $imageUrl; ?>" width="300" height="300" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    </a>
                                    <?php if(isset($product['is_featured']) && $product['is_featured']): ?>
                                    <div class="label-group">
                                        <span class="product-label label-hot">Istaknuto</span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="btn-icon-group">
                                        <!-- <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                                class="icon-shopping-cart"></i></a> -->
                                    </div>
                                </figure>
                                <div class="product-details">
                                    <div class="category-wrap">
                                        <div class="category-list">
                                            <a href="#"><?php echo htmlspecialchars($product['p_category_name'] ?? 'Ograde'); ?></a>
                                        </div>
                                    </div>
                                    <h3 class="product-title">
                                        <a href="<?php echo $product_url; ?>"><?php echo htmlspecialchars($product['product_name']); ?></a>
                                    </h3>
                                    <div class="ratings-container">
                                        <div class="product-ratings">
                                            <span class="ratings" style="width:80%"></span>
                                            <span class="tooltiptext tooltip-top"></span>
                                        </div>
                                    </div>
                                    <?php if (!empty($product['rate'])): ?>
                                    <div class="price-box">
                                        <span class="product-price"><?php echo $product['rate']; ?>€</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; 
                            else: ?>
                            <div class="alert alert-info">Nema preporučenih proizvoda za prikaz.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
                
                <!-- Recently Viewed Products Section -->
                <section class="product-section recently-viewed-section" style="padding-top: 0px;">
                    <div class="container">
                        <h2 class="title title-underline pb-1 appear-animate" data-animation-name="fadeInLeftShorter">Nedavno pogledano</h2>
                        <div class="owl-carousel owl-theme appear-animate" data-owl-options="{
                        'loop': false,
                        'dots': false,
                        'nav': true,
                        'margin': 20,
                        'responsive': {
                            '0': {
                                'items': 2
                            },
                            '576': {
                                'items': 2
                            },
                            '991': {
                                'items': 4
                            }
                        }
                    }">
                            <?php 
                            if (isset($recently_viewed_products) && is_array($recently_viewed_products) && !empty($recently_viewed_products)):
                            foreach ($recently_viewed_products as $product): 
                                if (empty($product) || !isset($product['product_name'])) continue;
                                $product_slug = slugify($product['product_name']);
                                $product_url = site_url('product/' . $product_slug);
                                
                                // Get product image
                                $imageUrl = base_url('modules/products/uploads/image-not-available.png'); // Default image
                                if (!empty($product['product_image'])) {
                                    $productImagePath = 'modules/products/uploads/' . $product['product_image'];
                                    if (file_exists(FCPATH . $productImagePath)) {
                                        $imageUrl = base_url($productImagePath);
                                    }
                                }
                            ?>
                            <div class="product-default inner-quickview">
                                <figure>
                                    <a href="<?php echo $product_url; ?>">
                                        <img src="<?php echo $imageUrl; ?>" width="300" height="300" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                                    </a>
                                    <?php if(isset($product['is_featured']) && $product['is_featured']): ?>
                                    <div class="label-group">
                                        <span class="product-label label-hot">Istaknuto</span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="btn-icon-group">
                                        <!-- <a href="#" class="btn-icon btn-add-cart product-type-simple"><i
                                                class="icon-shopping-cart"></i></a> -->
                                    </div>
                                </figure>
                                <div class="product-details">
                                    <div class="category-wrap">
                                        <div class="category-list">
                                            <a href="#"><?php echo htmlspecialchars($product['p_category_name'] ?? 'Ograde'); ?></a>
                                        </div>
                                    </div>
                                    <h3 class="product-title">
                                        <a href="<?php echo $product_url; ?>"><?php echo htmlspecialchars($product['product_name']); ?></a>
                                    </h3>
                                    <div class="ratings-container">
                                        <div class="product-ratings">
                                            <span class="ratings" style="width:80%"></span>
                                            <span class="tooltiptext tooltip-top"></span>
                                        </div>
                                    </div>
                                    <?php if (!empty($product['rate'])): ?>
                                    <div class="price-box">
                                        <span class="product-price"><?php echo $product['rate']; ?>€</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; 
                            else: ?>
                            <div class="alert alert-info">Nema nedavno gledanih proizvoda za prikaz.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
			</div>
		</div>	
		<?php @include('cart.php'); ?>
    <?php @include('flash-popup.php'); ?>
</div>

<div class="mb-5"></div>

<!-- <hr class="mb-4"> -->
<div class="row">
	<div class="col-lg-12">
		<!-- <a href="#" class="btn btn-outline-dark btn-load-more">Load More...</a> -->
	</div><!-- End .col-lg-9 -->
</div><!-- End .row -->
</div><!-- End .container -->