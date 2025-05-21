<?php defined('BASEPATH') || exit('No direct script access allowed');

// Determine if we're editing an existing template or creating a new one
$is_edit = isset($template) && !empty($template);

$form_action = $is_edit ? admin_url('templates/update/' . $template->template_id) : admin_url('templates/save');
$button_text = $is_edit ? _l('update_template') : _l('save_template');
$form_title = $is_edit ? _l('edit_template') : _l('create_template');
?>

<div class="content">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <h4 class="tw-font-semibold tw-text-neutral-700"><?php echo $form_title; ?></h4>
            <?php echo form_open_multipart($form_action); ?>

            <!-- Template Name -->
            <div class="form-group">
                <label for="template_name"><?php echo _l('template_name'); ?></label>
                <input type="text" id="template_name" name="template_name" class="form-control" value="<?php echo $is_edit ? $template->template_name : ''; ?>" required>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label for="category"><?php echo _l('category'); ?></label>
                <input type="text" id="category" name="category" class="form-control" value="<?php echo $is_edit ? $template->category : ''; ?>" required>
            </div>

            <!-- Language -->
            <div class="form-group">
                <label for="language"><?php echo _l('language'); ?></label>
                <input type="text" id="language" name="language" class="form-control" value="<?php echo $is_edit ? $template->language : ''; ?>" required>
            </div>

            <!-- Header Section -->
            <div class="form-group">
                <label for="header_type"><?php echo _l('header_type'); ?></label>
                <select id="header_type" name="header_type" class="form-control" onchange="toggleHeaderFields()" required>
                    <option value="none" <?php echo $is_edit && $template->header_type == 'none' ? 'selected' : ''; ?>><?php echo _l('none'); ?></option>
                    <option value="text" <?php echo $is_edit && $template->header_type == 'text' ? 'selected' : ''; ?>><?php echo _l('text'); ?></option>
                    <option value="image" <?php echo $is_edit && $template->header_type == 'image' ? 'selected' : ''; ?>><?php echo _l('image'); ?></option>
                    <option value="document" <?php echo $is_edit && $template->header_type == 'document' ? 'selected' : ''; ?>><?php echo _l('document'); ?></option>
                    <option value="video" <?php echo $is_edit && $template->header_type == 'video' ? 'selected' : ''; ?>><?php echo _l('video'); ?></option>
                </select>
            </div>

            <div id="header_text_field" class="form-group" style="display: <?php echo $is_edit && $template->header_type == 'text' ? 'block' : 'none'; ?>;">
                <label for="header_text"><?php echo _l('header_text'); ?></label>
                <input type="text" id="header_text" name="header_text" class="form-control" value="<?php echo $is_edit ? $template->header_text : ''; ?>">
            </div>

            <div id="header_file_field" class="form-group" style="display: <?php echo $is_edit && in_array($template->header_type, ['image', 'document', 'video']) ? 'block' : 'none'; ?>;">
                <label for="header_file"><?php echo _l('upload_header_file'); ?></label>
                <input type="file" id="header_file" name="header_file" class="form-control">
                <?php if ($is_edit && in_array($template->header_type, ['image', 'document', 'video'])): ?>
                    <p><?php echo _l('current_file'); ?>: <a href="<?php echo base_url('uploads/whatsapp_templates/' . $template->header_file); ?>" target="_blank"><?php echo $template->header_file; ?></a></p>
                <?php endif; ?>
            </div>

            <!-- Body Section -->
            <div class="form-group">
                <label for="body_text"><?php echo _l('body_text'); ?></label>
                <textarea id="body_text" name="body_text" class="form-control" required><?php echo $is_edit ? $template->body_text : ''; ?></textarea>
            </div>

            <!-- Footer Section -->
            <div class="form-group">
                <label for="footer_text"><?php echo _l('footer_text'); ?></label>
                <input type="text" id="footer_text" name="footer_text" class="form-control" value="<?php echo $is_edit ? $template->footer_text : ''; ?>">
            </div>

            <!-- Buttons Section -->
            <div class="form-group">
                <label for="buttons"><?php echo _l('buttons'); ?></label>
                <input type="text" id="buttons" name="buttons" class="form-control" value="<?php echo $is_edit ? implode(',', json_decode($template->buttons)) : ''; ?>" placeholder="<?php echo _l('enter_buttons_comma_separated'); ?>">
                <small class="form-text text-muted"><?php echo _l('buttons_info'); ?></small>
            </div>

            <button type="submit" class="btn btn-primary"><?php echo $button_text; ?></button>
            <a href="<?php echo admin_url('templates'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
    function toggleHeaderFields() {
        var headerType = document.getElementById('header_type').value;
        document.getElementById('header_text_field').style.display = headerType === 'text' ? 'block' : 'none';
        document.getElementById('header_file_field').style.display = ['image', 'document', 'video'].includes(headerType) ? 'block' : 'none';
    }
</script>
