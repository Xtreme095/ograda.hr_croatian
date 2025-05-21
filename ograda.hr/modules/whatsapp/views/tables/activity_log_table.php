<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    db_prefix() . 'whatsapp_activity_log.id as id',
    'category',
    '1',
    '1',
    'response_code',
    db_prefix() . 'whatsapp_activity_log.rel_type as rel_type',
    'recorded_at',
    '1',
];

$join = [
    'LEFT JOIN ' . db_prefix() . 'whatsapp_campaigns ON ' . db_prefix() . 'whatsapp_campaigns.id = ' . db_prefix() . 'whatsapp_activity_log.category_id',
    'LEFT JOIN ' . db_prefix() . 'whatsapp_bot ON ' . db_prefix() . 'whatsapp_bot.id = ' . db_prefix() . 'whatsapp_activity_log.category_id',
];

$where = [];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'whatsapp_activity_log';

$additionalSelect = [
    db_prefix() . 'whatsapp_campaigns.template_id',
    db_prefix() . 'whatsapp_bot.name as bot_name',
    db_prefix() . 'whatsapp_campaigns.name as campaign_name',
];

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['id'];

    $row[] = _l($aRow['category']);

    $name = '-';
    $template_name = '-';

    if ($aRow['category'] == 'Message Bot') {
        $name = $aRow['bot_name'];
    } else {
        $template = get_whatsapp_template($aRow['template_id']);
        $template_name = $template['template_name'] ?? _l('not_found_or_deleted');
        $name = $aRow['campaign_name'] ?? _l('not_found_or_deleted');
    }

    $row[] = $name;
    $row[] = $template_name;

    $color = 'label-default';
    if ($aRow['response_code'] >= 200 && $aRow['response_code'] <= 299) {
        $color = 'label-success';
    } elseif ($aRow['response_code'] >= 300 && $aRow['response_code'] <= 399) {
        $color = 'label-info';
    } elseif ($aRow['response_code'] >= 400 && $aRow['response_code'] <= 499) {
        $color = 'label-warning';
    } elseif ($aRow['response_code'] >= 500 && $aRow['response_code'] <= 599) {
        $color = 'label-danger';
    }
    $row[] = '<span class="label ' . $color . '">' . $aRow['response_code'] . '</span>';

    $row[] = _l($aRow['rel_type']);

    $row[] = $aRow['recorded_at'];

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
    $options .= '<a href="' . admin_url('whatsapp/view_log_details/') . $aRow['id'] . '" class="btn btn-primary btn-icon"><i class="fa fa-eye"></i></a>';

    if (staff_can('clear_log', 'whatsapp_log_activity')) {
        $options .= '<a href="' . admin_url('whatsapp/delete_log/' . $aRow['id']) . '" data-id="' . $aRow['id'] . '" class="btn btn-danger btn-icon btn-lg _delete"><i class="fa-regular fa-trash-can"></i></a>';
    }

    $options .= '</div>';
    $row[] = $options;

    $output['aaData'][] = $row;
}
