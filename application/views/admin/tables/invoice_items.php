<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [];

if (staff_can('delete', 'items')) {
    $aColumns[] = '1';
}

$aColumns = array_merge($aColumns, [
    'description',
    'long_description',
    db_prefix() . 'items.rate as rate',
    't1.taxrate as taxrate_1',
    'commodity_code',
    'unit',
    db_prefix() . 'items_groups.name as group_name',
]);

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'items';

$join = [
    'LEFT JOIN ' . db_prefix() . 'taxes t1 ON t1.id = ' . db_prefix() . 'items.tax',
    'LEFT JOIN ' . db_prefix() . 'taxes t2 ON t2.id = ' . db_prefix() . 'items.tax2',
    'LEFT JOIN ' . db_prefix() . 'items_groups ON ' . db_prefix() . 'items_groups.id = ' . db_prefix() . 'items.group_id',
];
$additionalSelect = [
    db_prefix() . 'items.id',
    't1.name as taxname_1',
    't2.name as taxname_2',
    't1.id as tax_id_1',
    't2.id as tax_id_2',
    'group_id',
];

$custom_fields = get_custom_fields('items');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'items.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="items_pr" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    $descriptionOutput = '';
    $descriptionOutput = '<a href="#" data-toggle="modal" data-target="#sales_item_modal" data-id="' . $aRow['id'] . '" class="tw-font-medium">' . e($aRow['description']) . '</a>';
    $descriptionOutput .= '<div class="row-options">';

    if (staff_can('edit', 'items')) {
        $descriptionOutput .= '<a href="#" data-toggle="modal" data-target="#sales_item_modal" data-id="' . $aRow['id'] . '">' . _l('edit') . '</a>';
    }

    if (staff_can('delete', 'items')) {
        $descriptionOutput .= ' | <a href="' . admin_url('invoice_items/delete/' . $aRow['id']) . '" class="_delete">' . _l('delete') . '</a>';
    }

    if (staff_can('create', 'items')) {
        $descriptionOutput .= ' | <a href="' . admin_url('invoice_items/copy/' . $aRow['id']) . '" class=" _edit_item">' . _l('copy') . '</a>';
    }

    $descriptionOutput .= '</div>';

    $row[] = $descriptionOutput;

    $row[] = e($aRow['long_description']);

    $row[] = '<span class="tw-font-medium">' . e(app_format_money($aRow['rate'], get_base_currency())) . '</span>';

    $aRow['taxrate_1'] ??= 0;
    $row[] = '<span data-toggle="tooltip" title="' . e($aRow['taxname_1']) . '" data-taxid="' . $aRow['tax_id_1'] . '">' . e(app_format_number($aRow['taxrate_1'])) . '%</span>';

    $aRow['taxrate_2'] ??= 0;
    $row[] = '<span data-toggle="tooltip" title="' . e($aRow['taxname_2']) . '" data-taxid="' . $aRow['tax_id_2'] . '">' . e(app_format_number($aRow['commodity_code'])) . '</span>';
    $row[] = $aRow['unit'];

    $row[] = e($aRow['group_name']);

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
