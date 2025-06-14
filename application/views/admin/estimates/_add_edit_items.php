<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel-body">
    <div class="row">
        <div class="col-md-4">
            <?php $this->load->view('admin/invoice_items/item_select'); ?>
        </div>
        <div class="col-md-8 text-right show_quantity_as_wrapper">
            <div class="mtop10">
                <span><?= _l('show_quantity_as'); ?></span>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" value="1" id="1" name="show_quantity_as"
                        data-text="<?= _l('estimate_table_quantity_heading'); ?>"
                        <?= isset($estimate) && $estimate->show_quantity_as == 1 ? 'checked' : 'checked'; ?>>
                    <label
                        for="1"><?= _l('quantity_as_qty'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" value="2" id="2" name="show_quantity_as"
                        data-text="<?= _l('estimate_table_hours_heading'); ?>"
                        <?= isset($estimate) && $estimate->show_quantity_as == 2 ? 'checked' : ''; ?>>
                    <label
                        for="2"><?= _l('quantity_as_hours'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="3" value="3" name="show_quantity_as"
                        data-text="<?= _l('estimate_table_quantity_heading'); ?>/<?= _l('estimate_table_hours_heading'); ?>"
                        <?= isset($estimate) && $estimate->show_quantity_as == 3 ? 'checked' : ''; ?>>
                    <label for="3">
                        <?= _l('estimate_table_quantity_heading'); ?>/<?= _l('estimate_table_hours_heading'); ?>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive s_table">
        <table class="table estimate-items-table items table-main-estimate-edit has-calculations no-mtop">
            <thead>
                <tr>
                    <th></th>
                    <th width="20%" align="left"><i class="fa-solid fa-circle-exclamation tw-mr-1" aria-hidden="true"
                            data-toggle="tooltip"
                            data-title="<?= _l('item_description_new_lines_notice'); ?>"></i>
                        <?= _l('estimate_table_item_heading'); ?>
                    </th>
                    <th width="25%" align="left">
                        <?= _l('estimate_table_item_description'); ?>
                    </th>
                    <?php
                  $custom_fields = get_custom_fields('items');

foreach ($custom_fields as $cf) {
    echo '<th width="15%" align="left" class="custom_field">' . e($cf['name']) . '</th>';
}

$qty_heading = _l('estimate_table_quantity_heading');
if (isset($estimate) && $estimate->show_quantity_as == 2) {
    $qty_heading = _l('estimate_table_hours_heading');
} elseif (isset($estimate) && $estimate->show_quantity_as == 3) {
    $qty_heading = _l('estimate_table_quantity_heading') . '/' . _l('estimate_table_hours_heading');
}
?>
                    <th width="10%" class="qty" align="right">
                        <?= e($qty_heading); ?>
                    </th>
                    <th width="15%" align="right">
                        <?= _l('estimate_table_rate_heading'); ?>
                    </th>
                    <th width="20%" align="right">
                        <?= _l('estimate_table_tax_heading'); ?>
                    </th>
                    <th width="10%" align="right">
                        <?= _l('estimate_table_amount_heading'); ?>
                    </th>
                    <th align="center"><i class="fa fa-cog"></i></th>
                </tr>
            </thead>
            <tbody>
                <tr class="main">
                    <td></td>
                    <td>
                        <textarea name="description" rows="4" class="form-control"
                            placeholder="<?= _l('item_description_placeholder'); ?>"></textarea>
                        
                        <input type="number" style="margin-top: 20px;" name="height" class="form-control" placeholder="Visina" aria-invalid="false" style="min-width: 150px" value="">
                    </td>
                    <td>
                        <textarea name="long_description" rows="4" class="form-control"
                            placeholder="<?= _l('item_long_description_placeholder'); ?>"></textarea>

                        <input type="number" style="margin-top: 20px;" name="length" class="form-control" placeholder="Širina" aria-invalid="false" style="min-width: 150px" value="">
                    </td>
                    <?= render_custom_fields_items_table_add_edit_preview(); ?>
                    <td>
                        <input type="number" name="quantity" min="0" value="1" class="form-control"
                            placeholder="<?= _l('item_quantity_placeholder'); ?>">
                        <input type="text"
                            placeholder="<?= _l('unit'); ?>"
                            data-toggle="tooltip" 612 data-title="e.q kg, lots, packs" name="unit"
                            class="form-control input-transparent text-right">

                        <br><br>
                        <input type="number" name="spacing" class="form-control" placeholder="Razmak" aria-invalid="false" style="min-width: 150px" value="">
                    </td>
                    <td>
                        <input type="number" name="rate" class="form-control"
                            placeholder="<?= _l('item_rate_placeholder'); ?>">
                    </td>
                    <td>
                        <?php
   $default_tax = unserialize(get_option('default_tax'));
$select         = '<select class="selectpicker display-block tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="' . _l('no_tax') . '">';

foreach ($taxes as $tax) {
    $selected = '';
    if (is_array($default_tax)) {
        if (in_array($tax['name'] . '|' . $tax['taxrate'], $default_tax)) {
            $selected = ' selected ';
        }
    }
    $select .= '<option value="' . $tax['name'] . '|' . $tax['taxrate'] . '"' . $selected . 'data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
}
$select .= '</select>';
echo $select;
?>
                    </td>
                    <td></td>
                    <td>
                        <?php
$new_item = 'undefined';
if (isset($estimate)) {
    $new_item = true;
}
?>
                        <button type="button"
                            onclick="add_item_to_table('undefined','undefined',<?= e($new_item); ?>); return false;"
                            class="btn pull-right btn-primary"><i class="fa fa-check"></i></button>
                    </td>
                </tr>
                <tr>
                <td></td>
                    <?php /*<td colspan="1">
                    <input type="number" name="height" class="form-control" placeholder="Visina" aria-invalid="false" style="min-width: 150px">
                    </td>
                    <td colspan="1">
                    <input type="number" name="height" class="form-control" placeholder="Širina" aria-invalid="false" style="min-width: 150px">
                    </td>
                    <td colspan="2">
                    <input type="number" name="height" class="form-control" placeholder="Razmak" aria-invalid="false" style="min-width: 150px">
                    </td>
                    <td colspan="2">
                    <input type="number" name="height" class="form-control" placeholder="Broj polja" aria-invalid="false" style="min-width: 150px">
                    </td>*/ ?>
                </tr>
                <?php if (isset($estimate) || isset($add_items)) {
                    $i               = 1;
                    $items_indicator = 'newitems';
                    if (isset($estimate)) {
                        $add_items       = $estimate->items;
                        $items_indicator = 'items';
                    }

                    foreach ($add_items as $item) { 
                        $manual    = false;
                        $table_row = '<tr class="sortable item">';
                        $table_row .= '<td class="dragger">';
                        if ($item['qty'] == '' || $item['qty'] == 0) {
                            $item['qty'] = 1;
                        }
                        if (! isset($is_proposal)) {
                            $estimate_item_taxes = get_estimate_item_taxes($item['id']);
                        } else {
                            $estimate_item_taxes = get_proposal_item_taxes($item['id']);
                        }
                        if ($item['id'] == 0) {
                            $estimate_item_taxes = $item['taxname'];
                            $manual              = true;
                        }
                        $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                        $amount = $item['rate'] * $item['qty'];
                        $amount = app_format_number($amount);
                        // order input
                        $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                        $table_row .= '</td>';
                        $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="5">' . clear_textarea_breaks($item['description']) . '</textarea>
                        <br><input type="number" name="' . $items_indicator . '[' . $i . '][height]" class="form-control" placeholder="Visina" aria-invalid="false" style="min-width: 150px" value="'.$item['height'].'">
                        </td>';
                        $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['long_description']) . '</textarea>
                        
                        <br><input type="number" name="' . $items_indicator . '[' . $i . '][length]" class="form-control" placeholder="Širina" aria-invalid="false" style="min-width: 150px" value="'.$item['length'].'">
                        </td>';
                        $table_row .= render_custom_fields_items_table_in($item, $items_indicator . '[' . $i . ']');
                        $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control">';
                        $unit_placeholder = '';
                        if (! $item['unit']) {
                            $unit_placeholder = _l('unit');
                            $item['unit']     = '';
                        }
                        $table_row .= '<input type="text" placeholder="' . $unit_placeholder . '" name="' . $items_indicator . '[' . $i . '][unit]" class="form-control input-transparent text-right" value="' . $item['unit'] . '">
                        <input type="number" name="' . $items_indicator . '[' . $i . '][spacing]" class="form-control" placeholder="Razmak" aria-invalid="false" style="min-width: 150px" value="'.$item['spacing'].'">';
                        $table_row .= '</td>';
                        $table_row .= '<td class="rate"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][rate]" value="' . $item['rate'] . '" class="form-control">
                        <br><textarea type="text" name="' . $items_indicator . '[' . $i . '][fields]" class="form-control" placeholder="Broj polja" aria-invalid="false" style="min-width: 150px">'.$item['fields'].'</textarea></td>';
                        $table_row .= '<td class="taxrate">' . $this->misc_model->get_taxes_dropdown_template('' . $items_indicator . '[' . $i . '][taxname][]', $estimate_item_taxes, (isset($is_proposal) ? 'proposal' : 'estimate'), $item['id'], true, $manual) . '</td>';
                        $table_row .= '<td class="amount" align="right">' . $amount . '</td>';
                        $table_row .= '<td><a href="#" class="btn btn-danger pull-left !tw-px-3" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
                        
                        echo $table_row;
                        $table_row .= '</tr>';
                        //echo $additional;
                        $i++;
                    }
                }
?>
            </tbody>
        </table>
    </div>
    <div class="col-md-8 col-md-offset-4">
        <table class="table text-right">
            <tbody>
                <tr id="subtotal">
                    <td><span
                            class="bold tw-text-neutral-700"><?= _l('estimate_subtotal'); ?>
                            :</span>
                    </td>
                    <td class="subtotal">
                    </td>
                </tr>
                <tr id="discount_area">
                    <td>
                        <div class="row">
                            <div class="col-md-7">
                                <span
                                    class="bold tw-text-neutral-700"><?= _l('estimate_discount'); ?></span>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group" id="discount-total">

                                    <input type="number"
                                        value="<?= isset($estimate) ? $estimate->discount_percent : 0; ?>"
                                        class="form-control pull-left input-discount-percent<?php if (isset($estimate) && ! is_sale_discount($estimate, 'percent') && is_sale_discount_applied($estimate)) {
                                            echo ' hide';
                                        } ?>" min="0" max="100" name="discount_percent">

                                    <input type="number" data-toggle="tooltip"
                                        data-title="<?= _l('numbers_not_formatted_while_editing'); ?>"
                                        value="<?= isset($estimate) ? $estimate->discount_total : 0; ?>"
                                        class="form-control pull-left input-discount-fixed<?php if (! isset($estimate) || (isset($estimate) && ! is_sale_discount($estimate, 'fixed'))) {
                                            echo ' hide';
                                        } ?>" min="0" name="discount_total">

                                    <div class="input-group-addon">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <span class="discount-total-type-selected">
                                                    <?php if (! isset($estimate) || isset($estimate) && (is_sale_discount($estimate, 'percent') || ! is_sale_discount_applied($estimate))) {
                                                        echo '%';
                                                    } else {
                                                        echo _l('discount_fixed_amount');
                                                    }
?>
                                                </span>
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu" id="discount-total-type-dropdown"
                                                aria-labelledby="dropdown_menu_tax_total_type">
                                                <li>
                                                    <a href="#" class="discount-total-type discount-type-percent<?php if (! isset($estimate) || (isset($estimate) && is_sale_discount($estimate, 'percent')) || (isset($estimate) && ! is_sale_discount_applied($estimate))) {
                                                        echo ' selected';
                                                    } ?>">%</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="discount-total-type discount-type-fixed<?php if (isset($estimate) && is_sale_discount($estimate, 'fixed')) {
                                                        echo ' selected';
                                                    } ?>">
                                                        <?= _l('discount_fixed_amount'); ?>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="discount-total"></td>
                </tr>
                <tr>
                    <td>
                        <div class="row">
                            <div class="col-md-7">
                                <span
                                    class="bold tw-text-neutral-700"><?= _l('estimate_adjustment'); ?></span>
                            </div>
                            <div class="col-md-5">
                                <input type="number" data-toggle="tooltip"
                                    data-title="<?= _l('numbers_not_formatted_while_editing'); ?>"
                                    value="<?php if (isset($estimate)) {
                                        echo $estimate->adjustment;
                                    } else {
                                        echo 0;
                                    } ?>" class="form-control pull-left" name="adjustment">
                            </div>
                        </div>
                    </td>
                    <td class="adjustment"></td>
                </tr>
                <tr>
                    <td><span
                            class="bold tw-text-neutral-700"><?= _l('estimate_total'); ?>
                            :</span>
                    </td>
                    <td class="total">
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="removed-items"></div>
</div>