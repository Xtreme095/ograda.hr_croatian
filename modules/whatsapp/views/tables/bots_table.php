<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    'name',
    'bot_type',
    '`trigger`', // Wrap trigger in backticks to avoid conflicts with SQL keywords
    'rel_type',
    'reply_type', // New column for reply type
    'is_bot_active',
];

$where = [];

$sIndexColumn = 'id';
$sTable       = db_prefix().'whatsapp_bot';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['name'];
    $row[] = whatsapp_get_bot_type($aRow['bot_type'])['label'] ?? '-'; // Use helper to get bot type label
    $row[] = $aRow['trigger'];

    // rel_type with color-coded labels
    $colorMap = [
        'leads' => '#3a25e9',
        'contacts' => '#ff4646',
        'customers' => '#7bf565',
        'other' => '#aaaaaa',
    ];

    $color = $colorMap[$aRow['rel_type']] ?? $colorMap['other'];
    $row[] = '<span class="label" style="color:' . $color . '; border:1px solid ' . adjust_hex_brightness($color, 0.4) . '; background: ' . adjust_hex_brightness($color, 0.04) . ';">' . _l($aRow['rel_type']) . '</span>';

    // New column for reply type
    $row[] = whatsapp_get_reply_type($aRow['reply_type'])['label'] ?? '-'; // Use helper to get reply type label

    // Active status switch
    $checked = $aRow['is_bot_active'] == 1 ? 'checked' : '';
    $row[] = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="' . admin_url('whatsapp/bots/change_active_status/template') . '" name="onoffswitch" class="onoffswitch-checkbox" id="c_' . $aRow['id'] . '" data-id="' . $aRow['id'] . '" ' . $checked . '>
                <label class="onoffswitch-label" for="c_' . $aRow['id'] . '"></label>
            </div>';

    // Action options
    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';
    
    if (staff_can('edit', 'whatsapp_template_bot')) {
        $options .= '<a href="' . admin_url('whatsapp/bots/form/' . $aRow['id']) . '" data-toggle="tooltip" data-title="' . _l('edit') . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700">
                        <i class="fa-regular fa-pen-to-square fa-lg"></i>
                    </a>';
    }
    
    if (staff_can('delete', 'whatsapp_message_bot')) {
        $options .= '<a href="javascript:void(0)" data-id="' . $aRow['id'] . '" id="delete_message_bot" data-toggle="tooltip" data-title="' . _l('delete') . '" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete">
                        <i class="fa-regular fa-trash-can fa-lg"></i>
                    </a>';
    }

    if (!staff_can('edit', 'whatsapp_template_bot') && !staff_can('delete', 'whatsapp_message_bot')) {
        $options .= '-';
    }

    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
