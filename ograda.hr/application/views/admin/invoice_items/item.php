<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="modal fade" id="sales_item_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">
                    <span
                        class="edit-title"><?= _l('invoice_item_edit_heading'); ?></span>
                    <span
                        class="add-title"><?= _l('invoice_item_add_heading'); ?></span>
                </h4>
            </div>
            <?= form_open('admin/invoice_items/manage', ['id' => 'invoice_item_form']); ?>
            <?= form_hidden('itemid'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-warning affect-warning hide">
                            <?= _l('changing_items_affect_warning'); ?>
                        </div>
                        <?= render_input('description', 'invoice_item_add_edit_description'); ?>
                        <div class="form-group">
                            <img id="image" width="100%" src="" />
                        </div>
                        <?= render_textarea('long_description', 'invoice_item_long_description'); ?>
                        <div class="form-group">
                            <label for="rate" class="control-label">
                                <?= _l('invoice_item_add_edit_rate_currency', e($base_currency->name) . ' <small>(' . _l('base_currency_string') . ')</small>'); ?></label>
                            <input type="number" id="rate" name="rate" class="form-control" value="">
                        </div>
                        <?php
                            foreach ($currencies as $currency) {
                                if ($currency['isdefault'] == 0 && total_rows(db_prefix() . 'clients', ['default_currency' => $currency['id']]) > 0) { ?>
                        <div class="form-group">
                            <label
                                for="rate_currency_<?= e($currency['id']); ?>"
                                class="control-label">
                                <?= e(_l('invoice_item_add_edit_rate_currency', $currency['name'])); ?></label>
                            <input type="number"
                                id="rate_currency_<?= e($currency['id']); ?>"
                                name="rate_currency_<?= e($currency['id']); ?>"
                                class="form-control" value="">
                        </div>
                        <?php }
                                }
?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"
                                        for="tax"><?= _l('tax_1'); ?></label>
                                    <select class="selectpicker display-block" data-width="100%" name="tax"
                                        data-none-selected-text="<?= _l('no_tax'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($taxes as $tax) { ?>
                                        <option
                                            value="<?= e($tax['id']); ?>"
                                            data-subtext="<?= e($tax['name']); ?>">
                                            <?= e($tax['taxrate']); ?>%
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"
                                        for="tax2"><?= _l('tax_2'); ?></label>
                                    <select class="selectpicker display-block" disabled data-width="100%" name="tax2"
                                        data-none-selected-text="<?= _l('no_tax'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($taxes as $tax) { ?>
                                        <option
                                            value="<?= e($tax['id']); ?>"
                                            data-subtext="<?= e($tax['name']); ?>">
                                            <?= e($tax['taxrate']); ?>%
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix mbot15"></div>
                        <?= render_input('unit', 'unit'); ?>
                        <div class="clearfix mbot15"></div>
                        <?= render_input('commodity_code', 'Stanardni razmak'); ?>
                        <div id="custom_fields_items">
                            <?= render_custom_fields('items'); ?>
                        </div>
                        <?= render_select('group_id', $items_groups, ['id', 'name'], 'item_group'); ?>
                        <?php hooks()->do_action('before_invoice_item_modal_form_close'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default"
                    data-dismiss="modal"><?= _l('close'); ?></button>
                <button type="submit"
                    class="btn btn-primary"><?= _l('submit'); ?></button>
                <?= form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    // Maybe in modal? Eq convert to invoice or convert proposal to estimate/invoice
    if (typeof(jQuery) != 'undefined') {
        init_item_js();
    } else {
        window.addEventListener('load', function() {
            var initItemsJsInterval = setInterval(function() {
                if (typeof(jQuery) != 'undefined') {
                    init_item_js();
                    clearInterval(initItemsJsInterval);
                }
            }, 1000);
        });
    }
    // Items add/edit
    function manage_invoice_items(form) {
        var data = $(form).serialize();

        var url = form.action;
        $.post(url, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success == true) {
                var item_select = $('#item_select');
                if ($("body").find('.accounting-template').length > 0) {
                    if (!item_select.hasClass('ajax-search')) {
                        var group = item_select.find('[data-group-id="' + response.item.group_id + '"]');
                        if (group.length == 0) {
                            var _option = '<optgroup label="' + (response.item.group_name == null ? '' :
                                    response.item.group_name) + '" data-group-id="' + response.item.group_id +
                                '">' + _option + '</optgroup>';
                            if (item_select.find('[data-group-id="0"]').length == 0) {
                                item_select.find('option:first-child').after(_option);
                            } else {
                                item_select.find('[data-group-id="0"]').after(_option);
                            }
                        } else {
                            group.prepend('<option data-subtext="' + response.item.long_description +
                                '" value="' + response.item.itemid + '">(' + accounting.formatNumber(
                                    response.item.rate) + ') ' + response.item.description + '</option>');
                        }
                    }
                    if (!item_select.hasClass('ajax-search')) {
                        item_select.selectpicker('refresh');
                    } else {

                        item_select.contents().filter(function() {
                            return !$(this).is('.newitem') && !$(this).is('.newitem-divider');
                        }).remove();

                        var clonedItemsAjaxSearchSelect = item_select.clone();
                        item_select.selectpicker('destroy').remove();
                        $("body").find('.items-select-wrapper').append(clonedItemsAjaxSearchSelect);
                        init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url +
                            'items/search');
                    }

                    add_item_to_preview(response.item.itemid);
                } else {
                    // Is general items view
                    $('.table-invoice-items').DataTable().ajax.reload(null, false);
                }
                alert_float('success', response.message);
            }
            $('#sales_item_modal').modal('hide');
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        });
        return false;
    }

    function init_item_js() {
        // Add item to preview from the dropdown for invoices estimates
        $("body").on('change', 'select[name="item_select"]', function() {
            var itemid = $(this).selectpicker('val');
            if (itemid != '') {
                add_item_to_preview(itemid);
            }
        });

        // Items modal show action
        $("body").on('show.bs.modal', '#sales_item_modal', function(event) {

            $('.affect-warning').addClass('hide');

            var $itemModal = $('#sales_item_modal');
            $('input[name="itemid"]').val('');
            $itemModal.find('input').not('input[type="hidden"]').val('');
            $itemModal.find('textarea').val('');
            $itemModal.find('select').selectpicker('val', '').selectpicker('refresh');
            $('select[name="tax2"]').selectpicker('val', '').change();
            $('select[name="tax"]').selectpicker('val', '').change();
            $itemModal.find('.add-title').removeClass('hide');
            $itemModal.find('.edit-title').addClass('hide');

            var id = $(event.relatedTarget).data('id');
            // If id found get the text from the datatable
            if (typeof(id) !== 'undefined') {

                $('.affect-warning').removeClass('hide');
                $('input[name="itemid"]').val(id);

                requestGetJSON('invoice_items/get_item_by_id/' + id).done(function(response) {
                    $itemModal.find('input[name="description"]').val(response.description);
                    $itemModal.find('textarea[name="long_description"]').val(response.long_description
                        .replace(/(<|<)br\s*\/*(>|>)/g, " "));
                    $itemModal.find('#image').attr('src', response.image);
                    $itemModal.find('input[name="rate"]').val(response.rate);
                    $itemModal.find('input[name="unit"]').val(response.unit);
                    $itemModal.find('input[name="commodity_code"]').val(response.commodity_code);
                    console.log(response);
                    console.log('response');
                    $('select[name="tax"]').selectpicker('val', response.taxid).change();
                    $('select[name="tax2"]').selectpicker('val', response.taxid_2).change();
                    $itemModal.find('#group_id').selectpicker('val', response.group_id);
                    $.each(response, function(column, value) {
                        if (column.indexOf('rate_currency_') > -1) {
                            $itemModal.find('input[name="' + column + '"]').val(value);
                        }
                    });

                    $('#custom_fields_items').html(response.custom_fields_html);

                    init_selectpicker();
                    init_color_pickers();
                    init_datepicker();

                    $itemModal.find('.add-title').addClass('hide');
                    $itemModal.find('.edit-title').removeClass('hide');
                    validate_item_form();
                });

            }
        });

        $("body").on("hidden.bs.modal", '#sales_item_modal', function(event) {
            $('#item_select').selectpicker('val', '');
        });

        validate_item_form();
    }

    function validate_item_form() {
        // Set validation for invoice item form
        appValidateForm($('#invoice_item_form'), {
            description: 'required',
            rate: {
                required: true,
            }
        }, manage_invoice_items);
    }
</script>