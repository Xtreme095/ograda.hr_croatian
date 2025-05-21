<?php

defined('BASEPATH') || exit('No direct script access allowed');

$aColumns = [
    'name',
    'reply_type',
    db_prefix() . 'whatsapp_bot.trigger as trigger_message',
    'rel_type',
    'is_bot_active',
];

$sIndexColumn = 'id';
$sTable       = db_prefix().'whatsapp_bot';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $row[] = $aRow['name'];

    $row[] = _l('text_bot').' : '.str_replace('Reply bot: ', '', whatsapp_get_reply_type($aRow['reply_type'])['label']);

    $row[] = $aRow['trigger_message'];

    $color = ('leads' == $aRow['rel_type'] ? '#3a25e9' : ('contacts' == $aRow['rel_type'] ? '#ff4646' : '#7bf565'));
    $row[] = '<span class="label" style="color:' . $color . ';border:1px solid ' . adjust_hex_brightness($color, 0.4) . ';background: ' . adjust_hex_brightness($color, 0.04) . ';">' . _l($aRow['rel_type']) . '</span>';

    $checked = '';
    if (1 == $aRow['is_bot_active']) {
        $checked = 'checked';
    }

    $row[] = '<div class="onoffswitch">
                <input type="checkbox" data-switch-url="'.admin_url('whatsapp/bots/change_active_status/message').'" name="onoffswitch" class="onoffswitch-checkbox" id="c_'.$aRow['id'].'" data-id="'.$aRow['id'].'" '.$checked.'>
                <label class="onoffswitch-label" for="c_'.$aRow['id'].'"></label>
            </div>';

    $options = '<div class="tw-flex tw-items-center tw-space-x-3">';

    if (staff_can('edit', 'whatsapp_message_bot')) {
        $options .= '<a href="'.admin_url('whatsapp/bots/bot/text/'.$aRow['id']).'" data-toggle="tooltip" data-title="'._l('edit').'" class="tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700"><i class="fa-regular fa-pen-to-square fa-lg"></i></a>';
    }

    if (staff_can('delete', 'whatsapp_message_bot')) {
        $options .= '<a href="javascript:void(0)" data-id="'.$aRow['id'].'" data-type="text" data-toggle="tooltip" data-title="'._l('delete').'" id="delete_message_bot" class="tw-mt-px tw-text-neutral-500 hover:tw-text-neutral-700 focus:tw-text-neutral-700 _delete"><i class="fa-regular fa-trash-can fa-lg"></i></a>';
    }

    if (!staff_can('edit', 'whatsapp_message_bot') && !staff_can('delete', 'whatsapp_message_bot')) {
        $options .= '-';
    }

    $options .= '</div>';

    $row[] = $options;

    $output['aaData'][] = $row;
}
