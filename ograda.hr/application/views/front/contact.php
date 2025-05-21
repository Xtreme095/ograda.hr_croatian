
			<nav aria-label="breadcrumb" class="breadcrumb-nav">
				<div class="container">
					<ol class="breadcrumb">
						<li class="breadcrumb-item">
							<a href="<?= site_url('/'); ?>" title="homing"><i class="icon-home"></i></a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">
							Kontakt
						</li>
					</ol>
				</div>
			</nav>

			<!-- <div id="map"></div> -->
			<div class="page-header page-header-bg text-left" style="background: 50%/cover #D4E1EA url('assets/front/assets/images/IMG_ograda-naslovna.jpg');height: 400px;margin-top:0px;">
				<!-- <div class="container" style="text-align: center;">
					<h1>Kontaktirajte nas</h1>
					<a href="contact.html" class="btn btn-dark">Contact</a>
				</div> -->
			</div><!-- End .page-header -->

			<div class="container contact-us-container">
				<div class="contact-info">
					<div class="row">
						<div class="col-12">
							<h2 class="ls-n-25 m-b-1">
								Kako Vam možemo pomoći?
							</h2>

							<p>
								Naša posvećena ekipa je ovdje kako bi vam pružila podršku i odgovorila na sva vaša pitanja. Slobodno nas kontaktirajte putem telefona, e-pošte ili obratite nam se putem obrasca ispod kako bismo započeli vaš projekt. Radujemo se suradnji i ostvarivanju vaših snova o idealnom mobilnom domu.
							</p>
						</div>

						<div class="col-sm-6 col-lg-3">
							<div class="feature-box text-center">
								<!-- <i class="fa-solid fa-location-dot"></i> -->
								 <img class="icon-location" src="<?php echo base_url('assets/front/assets/images/icons/location.png'); ?>"/>
								<div class="feature-box-content">
									<h3>Adresa</h3>
									<h5>Gojlanska ulica 47, 10040 Zagreb</h5>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-lg-3">
							<div class="feature-box text-center">
								<i class="fa fa-mobile-alt"></i>
								<div class="feature-box-content">
									<h3>Kontakt</h3>
									<h5>+385 1 400 1500</h5>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-lg-3">
							<div class="feature-box text-center">
								<i class="far fa-envelope"></i>
								<div class="feature-box-content">
									<h3>E-mail</h3>
									<h5>info@ograda.hr</h5>
								</div>
							</div>
						</div>
						<div class="col-sm-6 col-lg-3">
							<div class="feature-box text-center">
								<i class="far fa-calendar-alt"></i>
								<div class="feature-box-content">
									<h3>Radno vrijeme:</h3>
									<h5>Ponedjeljak - Petak 08:00 do 16:00 <br> Subota 08:00 - 12:00 <br> Nedjelja zatvoreno</h5>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-lg-6">
						<h2 class="mb-2">Kontaktirajte nas</h2>
						
						<form action="<?= base_url('contact/save'); ?>" method="post" class="mb-0">
							<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" 
							value="<?= $this->security->get_csrf_hash(); ?>">
							<div class="form-group">
								<label class="mb-1" for="contact-name">Ime <span class="required">*</span></label>
								<input type="text" class="form-control" id="contact-name" name="name" required />
							</div>

							<div class="form-group">
								<label class="mb-1" for="contact-email">E-mail <span class="required">*</span></label>
								<input type="email" class="form-control" id="contact-email" name="email" required />
							</div>

							<div class="form-group">
								<label class="mb-1" for="contact-message">Vaša poruka <span class="required">*</span></label>
								<textarea cols="30" rows="3" id="contact-message" class="form-control" name="message" required></textarea>
							</div>

							<div class="form-footer mb-0">
								<button type="submit" class="btn btn-primary">Pošalji poruku</button>
							</div>
						</form>

						<?php @include('flash-popup.php'); ?>

					</div>

					<div class="col-lg-6">
						<h2 class="mb-1">Često postavljana pitanja</h2>
						<div id="accordion">
							<div class="card card-accordion">
								<a class="card-header" href="#" data-toggle="collapse" data-target="#collapseOne"
									aria-expanded="true" aria-controls="collapseOne">
									Od čega su napravljeni postovi?
								</a>

								<div id="collapseOne" class="collapse show" data-parent="#accordion">
									<p>Stupovi su proizvedeni od visokokvalitetnog aluminija, s vodećom debljinom stjenke u industriji od 1,8 mm. Sva naša metalna konstrukcija zatim je premazana prahom prema standardu mornarice, što rezultira robusnom i vrhunskom estetikom.</p>
								</div>
							</div>

							<div class="card card-accordion">
								<a class="card-header collapsed" href="#" data-toggle="collapse"
									data-target="#collapseTwo" aria-expanded="true" aria-controls="collapseOne">
									Što je uključeno?
								</a>

								<div id="collapseTwo" class="collapse" data-parent="#accordion">
									<p>Letvice koje ste odabrali, gornje i donje obloge letvica, kape za stupove, setovi za pričvršćivanje, osnovna ploča za stupove s poklopcima i aluminijski stupovi prema vašoj odabranoj duljini.</p>
								</div>
							</div>

							<div class="card card-accordion">
								<a class="card-header collapsed" href="#" data-toggle="collapse"
									data-target="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
									Što ako svoju ogradu postavljam u odvojene dijelove?
								</a>

								<div id="collapseThree" class="collapse" data-parent="#accordion">
									<p>Ako svoju ogradu planirate postaviti u zasebne dijelove, morat ćete zasebno kupiti dodatne stupove. Standardna narudžba pokriva samo postove za kontinuirani rad.</p>
								</div>
							</div>

							<div class="card card-accordion">
								<a class="card-header collapsed" href="#" data-toggle="collapse"
									data-target="#collapseFour" aria-expanded="true" aria-controls="collapseThree">
									Je li kompozitna ograda ekološki prihvatljiva?
								</a>

								<div id="collapseFour" class="collapse" data-parent="#accordion">
									<p>Da, kompozitna ograda koristi mješavinu recikliranog drveta i plastike, koristimo ekvivalentnu težinu od 80 velikih plastičnih boca u svakoj letvici. Ovo je plastika koja je mogla završiti u oceanima ili na odlagalištu. Kompozit je također izvrsna alternativa korištenju drvenih ploča, ne samo da drvena ograda zahtijeva žetvu i čestu zamjenu, već i drvena ograda također zahtijeva kemijske tretmane i boje kako bi se produžio životni vijek i trajnost, te će kemikalije na kraju prodrijeti u tlo.</p>
								</div>
							</div>

							<div class="card card-accordion">
								<a class="card-header collapsed" href="#" data-toggle="collapse"
									data-target="#collapseFive" aria-expanded="true" aria-controls="collapseThree">
									Jesu li dostupne letvice veće duljine?
								</a>

								<div id="collapseFive" class="collapse" data-parent="#accordion">
									<p>Da, imamo na raspolaganju veće duljine letvica. Obratite se našem stručnjaku za proizvode kako biste potvrdili željenu duljinu po narudžbi.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="features-section bg-gray">
                <div class="container">
                    <h2 class="subtitle">Naša odjeljenja</h2> 
					<p class="description-our-departments">Pružamo širok spektar usluga, uključujući ograde, arhitekturu, dizajn interijera, razvoj projekata i konzultacije. Naš stručan tim je tu da vam pomogne ostvariti vašu viziju i stvoriti mobilnu kućicu i ograde koja će nadmašiti sva vaša očekivanja.</p>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="feature-box bg-white">
                                <i class="icon-home"></i>

                                <div class="feature-box-content p-0">
                                    <h3>Prodaja </h3>
                                    <p>Za sva pitanja, cijene i dostupnost naših ograda, slobodno nas kontaktirajte putem telefona, e-pošte ili ispunite kontakt obrazac. Naša prodajna ekipa rado će vam pružiti sve potrebne informacije i pomoći vam pronaći savršeno za vas </p>
                                </div><!-- End .feature-box-content -->
                            </div><!-- End .feature-box -->
                        </div><!-- End .col-lg-4 -->

                        <div class="col-lg-4">
                            <div class="feature-box bg-white">
                                <i class="fas fa-info-circle"></i>

                                <div class="feature-box-content p-0">
                                    <h3>Pomoć & Podrška </h3>
                                    <p>Ako trebate pomoć ili imate bilo kakva pitanja o našim uslugama, slobodno nas kontaktirajte putem telefona, e-pošte ili ispunite kontakt obrazac. Naš tim za podršku posvećen je vašem zadovoljstvu i spreman je pružiti vam stručnu pomoć u svakom koraku vašeg projekta. </p>
                                </div><!-- End .feature-box-content -->
                            </div><!-- End .feature-box -->
                        </div><!-- End .col-lg-4 -->

                        <div class="col-lg-4">
                            <div class="feature-box bg-white">
                                <i class="fas fa-file-medical-alt"></i>

                                <div class="feature-box-content p-0">
                                    <h3>MEDIJI </h3>
                                    <p>Za medijske upite, intervjue ili informacije o našoj tvrtki, molimo kontaktirajte naš tim za odnose s javnošću putem telefona ili e-pošte navedenih u nastavku. Radujemo se suradnji s vama i pružanju potrebnih informacija o našem poslovanju i projektima. </p>
                                </div><!-- End .feature-box-content -->
                            </div><!-- End .feature-box -->
                        </div><!-- End .col-lg-4 -->
                    </div><!-- End .row -->
                </div><!-- End .container -->
            </div><!-- End .features-section -->
			</div>

			<div class="mb-8"></div>
		

	<!-- Google Map-->
	<!-- <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDc3LRykbLB-y8MuomRUIY0qH5S6xgBLX4"></script>
	<script src="assets/js/map.js"></script> -->
	<script>
		setTimeout(function() {
			$(".alert").fadeOut("slow");
		}, 5000);
	</script>
