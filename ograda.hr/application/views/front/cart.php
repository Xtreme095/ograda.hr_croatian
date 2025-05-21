   
    <!-- Modal -->
    <div class="modal fade " id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="formModalLabel">Zatražite ponudu</h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <!-- Form -->
                    <form action="/proposal" method="POST">
                    <?php
                    $csrf = get_csrf_for_ajax();
                    ?>
                    <input type="hidden" id="my-id" name="csrf_token_name" value="<?php echo $csrf['hash'] ?>" />
                    <input type="hidden" name="proposal" value="1" />

                     <!-- Hidden fields for height, material, color -->
                    <input type="hidden" name="selected_height" id="selected_height"  value="">
                    <input type="hidden" name="selected_material" id="selected_material"  value="">
                    <input type="hidden" name="selected_color" id="selected_color"  value="">
                    <input type="hidden" name="subject" id="selected_subject"  value="<?php echo $productName; ?>">
                    <input type="hidden" name="rate" id="selected_rate"  value="">
                    <input type="hidden" name="quantity" id="selected_quantity"  value="1">

                        <div class="form-group">
                            <label for="name">Ime</label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Ime" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email" placeholder="Email" required>
                        </div>
                        						

                        <div class="form-group">
                            <label for="phone">Kontakt telefon</label>
                            <input type="text" class="form-control" name="phone" id="phone" placeholder="Kontakt telefon" required>
                        </div>

                        <div class="form-group">
                            <label for="message">Dimenzije ograde dužina i visina</label>
                            <textarea class="form-control" name="message" id="message" rows="3" placeholder="Dimenzije ograde" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="phone">Ukoliko trebate montažu upišite adresu</label>
                            <input type="text" class="form-control" name="address" id="address" placeholder="Adresa" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Pošalji upit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

   


