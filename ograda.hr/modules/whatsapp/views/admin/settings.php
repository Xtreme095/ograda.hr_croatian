<?php 
$sources  = get_instance()->leads_model->get_source();
$statuses = get_instance()->leads_model->get_status();
$staff    = get_instance()->staff_model->get('', ['active' => 1]);
$templates    = get_whatsapp_template();
?>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('business_account_id_description'); ?>" data-placement="left"></i>
            <?php echo render_input('settings[whatsapp_business_account_id]', _l('whatsapp_business_account_id'), get_option('whatsapp_business_account_id')); ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('access_token_description'); ?>" data-placement="left"></i>
            <?php echo render_input('settings[whatsapp_access_token]', _l('whatsapp_access_token'), get_option('whatsapp_access_token')); ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label><?php echo _l('whatsapp_lead_status'); ?></label>
            <select class="selectpicker" data-width="100%" name="settings[whatsapp_lead_status]">
                <?php foreach ($statuses as $status) { ?>
                    <option value="<?php echo $status['id']; ?>" <?php echo (get_option('whatsapp_lead_status') == $status['id']) ? 'selected' : ''; ?>>
                        <?php echo $status['name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label><?php echo _l('whatsapp_lead_source'); ?></label>
            <select class="selectpicker" data-width="100%" name="settings[whatsapp_lead_source]">
                <?php foreach ($sources as $source) { ?>
                    <option value="<?php echo $source['id']; ?>" <?php echo (get_option('whatsapp_lead_source') == $source['id']) ? 'selected' : ''; ?>>
                        <?php echo $source['name']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label><?php echo _l('whatsapp_lead_assigned'); ?></label>
            <select class="selectpicker" data-width="100%" name="settings[whatsapp_lead_assigned]">
                <?php foreach ($staff as $staff_member) { ?>
                    <option value="<?php echo $staff_member['staffid']; ?>" <?php echo (get_option('whatsapp_lead_assigned') == $staff_member['staffid']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($staff_member['firstname'] . ' ' . $staff_member['lastname']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label for="whatsapp_auto_lead_settings"><?php echo _l('convert_whatsapp_message_to_lead'); ?></label>
            <select class="selectpicker" data-width="100%" name="settings[whatsapp_auto_lead_settings]">
                <option value="disable" <?php echo (get_option('whatsapp_auto_lead_settings') == 'disable') ? 'selected' : ''; ?>>
                    <?php echo _l('disable'); ?>
                </option>
                <option value="enable" <?php echo (get_option('whatsapp_auto_lead_settings') == 'enable') ? 'selected' : ''; ?>>
                    <?php echo _l('enable'); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label><?php echo _l('whatsapp_webhook'); ?></label>
            <input type="text" name="settings[whatsapp_webhook]" value="<?php echo htmlspecialchars(base_url('whatsapp/webhook/getdata')); ?>" class="form-control" disabled>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('whatsapp_webhook_token'); ?>" data-placement="left"></i>
            <?php echo render_input('settings[whatsapp_webhook_token]', _l('whatsapp_webhook_token'), get_option('whatsapp_webhook_token')); ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('whatsapp_openai_token'); ?>" data-placement="left"></i>
            <?php echo render_input('settings[whatsapp_openai_token]', _l('whatsapp_openai_token'), get_option('whatsapp_openai_token')); ?>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('whatsapp_openai_status'); ?>" data-placement="left"></i>
            <label><?php echo _l('whatsapp_openai_status'); ?></label>
            <select class="selectpicker" data-width="100%" name="settings[whatsapp_openai_status]">
                <option value="disable" <?php echo (get_option('whatsapp_openai_status') == 'disable') ? 'selected' : ''; ?>>
                    <?php echo _l('disable'); ?>
                </option>
                <option value="enable" <?php echo (get_option('whatsapp_openai_status') == 'enable') ? 'selected' : ''; ?>>
                    <?php echo _l('enable'); ?>
                </option>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('whatsapp_blueticks_status'); ?>" data-placement="left"></i>
            <label><?php echo _l('whatsapp_blueticks_status'); ?></label>
            <select class="selectpicker" data-width="100%" name="settings[whatsapp_blueticks_status]">
                <option value="disable" <?php echo (get_option('whatsapp_blueticks_status') == 'disable') ? 'selected' : ''; ?>>
                    <?php echo _l('disable'); ?>
                </option>
                <option value="enable" <?php echo (get_option('whatsapp_blueticks_status') == 'enable') ? 'selected' : ''; ?>>
                    <?php echo _l('enable'); ?>
                </option>
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label><?php echo _l('whatsapp_welcome_template'); ?></label>
            <select class="selectpicker" data-width="100%" name="settings[whatsapp_welcome_template]">
                <option value=""><?php echo _l('select_none'); ?></option> <!-- Empty option -->
                <?php foreach ($templates as $template) { ?>
                    <option value="<?php echo $template['template_id']; ?>" <?php echo (get_option('whatsapp_welcome_template') == $template['template_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($template['template_name']); ?>
                    </option>
                <?php } ?>
            </select>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label><?php echo _l('enable_webhooks'); ?></label>
            <div class="onoffswitch">
                <input type="checkbox" value="1" class="onoffswitch-checkbox" id="enable_webhooks" name="settings[enable_webhooks]" <?php echo ('1' == get_option('enable_webhooks')) ? 'checked' : ''; ?>>
                <label class="onoffswitch-label" for="enable_webhooks"></label>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <i class="fa fa-question-circle padding-5 pull-left" data-toggle="tooltip" data-title="<?php echo _l('webhooks_url'); ?>" data-placement="left"></i>
            <?php echo render_input('settings[webhooks_url]', _l('webhooks_url'), get_option('webhooks_url')); ?>
        </div>
    </div>
</div>
