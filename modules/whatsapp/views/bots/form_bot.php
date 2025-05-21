
<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>



<style>
.flow-builder-container {
  display: flex;
}

.left-panel {
  width: 20%;
  padding: 20px;
  background-color: #f5f5f5;
}

.workspace {
  width: 80%;
  padding: 20px;
  border: 1px solid #ccc;
  min-height: 600px;
  background-color: #fff;
  position: relative;
}

.component {
  padding: 10px;
  margin-bottom: 10px;
  background-color: #eee;
  border: 1px solid #ccc;
  cursor: pointer;
}

.dropped-element {
  position: absolute;
  padding: 10px;
  background-color: #dfe7fd;
  border: 1px solid #999;
  border-radius: 4px;
}

.menu-tree {
    list-style-type: none;
    padding-left: 20px;
    position: relative;
}

.menu-tree .menu-item, 
.menu-tree .submenu-item {
    position: relative;
    padding-left: 20px;
}
</style>
   <style>
        #canvas {
            width: 100%;
            height: 500px;
            border: 1px solid #ddd;
            overflow: auto;
            position: relative;
        }

        .create-flowy {
            display: inline-block;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            cursor: grab;
            border-radius: 5px;
            margin: 5px;
        }

        .hide {
            display: none;
        }
    </style>
<div id="wrapper">
    <div class="content">
        <?php echo form_open_multipart(admin_url('whatsapp/bots/saveBot'), ['id' => 'whatsapp_bot_form'], ['id' => $bot['id'] ?? '']); ?>

        <!-- General Bot Settings Section -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700">
                    <?php echo isset($bot) ? _l('edit_bot') . ' #' . $bot['name'] : _l('create_bot'); ?>
                </h4>
            </div>
        </div>

        <div class="row">
            <!-- General Bot Settings -->
            <div class="col-md-4">
                <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('general_settings'); ?></h4>
                        <?php 
                        echo render_input('name', 'bot_name', $bot['name'] ?? '', '', ['placeholder' => _l('enter_name'), 'autocomplete' => 'off'], [], 'tw-w-full tw-mb-4'); 
                        echo render_select('bot_type', whatsapp_get_bot_type(), ['id', 'label','description'], 'bot_type', $bot['bot_type'] ?? '', [], [], 'tw-w-full tw-mb-4'); 
                        echo render_select('rel_type', whatsapp_get_rel_type(), ['key', 'name'], 'relation_type', $bot['rel_type'] ?? '', [], [], 'tw-w-full tw-mb-4'); 
                        echo render_select('reply_type', whatsapp_get_reply_type(), ['id', 'label','example'], 'reply_type', $bot['reply_type'] ?? '', [], [], 'tw-w-full tw-mb-4'); 
                        echo render_input('trigger', 'trigger', $bot['trigger'] ?? '', '', ['placeholder' => _l('enter_bot_reply_trigger')], [], 'tw-w-full tw-mb-4'); 
                        ?>
                    </div>
                </div>
            </div>

            <!-- Message Bot Settings -->
            <div class="col-md-4 message-bot-section hide">
                <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('message_bot'); ?></h4>
                        <div class="tw-mb-4 hdft hide">
                            <label class="tw-block tw-font-semibold tw-mb-2"><?php echo _l('header'); ?></label>
                            <?php echo render_input('bot_header', 'header', $bot['bot_header'] ?? '', '', ['placeholder' => _l('enter_header')], [], 'tw-w-full'); ?>
                        </div>
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-semibold tw-mb-2"><?php echo _l('body'); ?></label>
                            <?php echo render_textarea('reply_text', 'body', $bot['reply_text'] ?? '', ['rows' => '10'], [], 'tw-w-full'); ?>
                        </div>
                        <div class="tw-mb-4 hdft hide">
                            <label class="tw-block tw-font-semibold tw-mb-2"><?php echo _l('footer'); ?></label>
                            <?php echo render_input('bot_footer', 'footer_bot', $bot['bot_footer'] ?? '', '', ['placeholder' => _l('enter_footer'), 'maxlength' => '60'], [], 'tw-w-full'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Template Bot Settings -->
            <div class="col-md-8 template-bot-section hide">
                <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('template_bot'); ?></h4>
                        <?php echo render_select('template_id', $templates, ['id', 'template_name', 'language'], 'template', $bot['template_id'] ?? ''); ?>
                        <div class="variableDetails row">
                            <div class="row">
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <div class="tw-flex tw-justify-between tw-items-center">
                                            <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin"><?php echo _l('variables'); ?>
                                            </h4>
                                            <span class="text-muted"><?php echo _l('merge_field_note'); ?></span>
                                        </div>
                                        <div class="clearfix"></div>
                                        <hr class="hr-panel-separator">
                                        <div class="variables"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="row" id="preview_message">
                                    <div class="col-md-12">
                                        <div class="panel_s">
                                            <div class="panel-body">
                                                <h4 class="tw-mt-0 tw-font-semibold tw-text-neutral-700 no-margin">
                                                    <?php echo _l('preview'); ?>
                                                </h4>
                                                <div class="clearfix"></div>
                                                <hr class="hr-panel-separator">
                                                <div class="padding" style='background: url(" <?php echo module_dir_url(WHATSAPP_MODULE, 'assets/images/bg.png'); ?>");'>
                                                    <div class="wtc_panel previewImage">
                                                    </div>
                                                    <div class="panel_s no-margin">
                                                        <div class="panel-body previewmsg"></div>
                                                    </div>
                                                    <div class="previewBtn">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        <!-- Media Bot Settings -->
        <div class="col-md-4 media-bot-section hide">
            <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                <div class="panel-body">
                    <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('media_bot'); ?></h4>
                    <div class="tw-mb-4">
                        <label class="tw-block tw-font-semibold tw-mb-2"><?php echo _l('media_type'); ?></label>
                        <?php 
                        echo render_select('media_type', whatsapp_get_media_types(), ['id', 'label'], 'media_type', $bot['media_type'] ?? '', [], [], 'tw-w-full tw-mb-4'); 
                        ?>
                    </div>
                    <div class="tw-mb-4">
                        <label class="tw-block tw-font-semibold tw-mb-2"><?php echo _l('media_file'); ?></label>
                        <input type="file" name="bot_file" id="filename" class="tw-w-full tw-bg-gray-50 tw-border tw-rounded-md tw-p-2" onchange="previewMedia()">
                    </div>
                    <!-- Media Preview -->
                    <div id="media-preview" class="tw-mt-4"></div>
                </div>
            </div>
        </div>

            <!-- Interactive Buttons Bot Settings -->
            <div class="col-md-4 interactive-buttons-bot-section hide">
                <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('interactive_buttons_bot'); ?></h4>
                        <div class="tw-mb-4">
                            <div class="tw-flex tw-space-x-4">
                                <?php for ($i = 1; $i <= 3; $i++) : ?>
                                    <div class="tw-w-1/3">
                                        <?php 
                                        echo render_input("button{$i}", "button{$i}", $bot["button{$i}"] ?? '', '', ['placeholder' => _l("enter_button{$i}")], [], 'tw-w-full');
                                        echo render_input("button{$i}_id", "button{$i}_id", $bot["button{$i}_id"] ?? '', '', ['placeholder' => _l("enter_button{$i}_id"), 'maxlength' => '256'], [], 'tw-w-full'); 
                                        ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <?php 
                            echo render_input('button_name', 'button_name', $bot['button_name'] ?? '', '', ['placeholder' => _l('enter_button_name')], [], 'tw-w-full tw-mb-4'); 
                            echo render_input('button_url', 'button_link', $bot['button_url'] ?? '', '', ['placeholder' => _l('enter_button_url')], [], 'tw-w-full tw-mb-4'); 
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Bot Settings -->
            <div class="col-md-4 location-bot-section hide">
                <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('location_bot'); ?></h4>
                        <?php 
                        echo render_input('latitude', 'latitude', $bot['latitude'] ?? '', '', ['placeholder' => _l('enter_latitude')], [], 'tw-w-full tw-mb-4');
                        echo render_input('longitude', 'longitude', $bot['longitude'] ?? '', '', ['placeholder' => _l('enter_longitude')], [], 'tw-w-full tw-mb-4');
                        echo render_input('location_name', 'location_name', $bot['location_name'] ?? '', '', ['placeholder' => _l('enter_location_name')], [], 'tw-w-full tw-mb-4');
                        echo render_input('location_address', 'location_address', $bot['location_address'] ?? '', '', ['placeholder' => _l('enter_location_address')], [], 'tw-w-full tw-mb-4');
                        ?>
                    </div>
                </div>
            </div>

            <!-- List and Quick Reply Bots -->
            <div class="col-md-4 list-bot-section hide quick-reply-bot-section hide">
                <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('list_or_quick_reply_bot'); ?></h4>
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-semibold tw-mb-2"><?php echo _l('list_items'); ?></label>
                            <?php echo render_textarea('bot_list', 'list_items', $bot['bot_list'] ?? '', ['rows' => '5'], [], 'tw-w-full'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sticker Bot Settings -->
            <div class="col-md-4 sticker-bot-section hide">
                <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('sticker_bot'); ?></h4>
                        <div class="tw-mb-4">
                            <label class="tw-block tw-font-semibold tw-mb-2"><?php echo _l('sticker_file'); ?></label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Bot Settings -->
            <div class="col-md-4 contact-bot-section hide">
                <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
                    <div class="panel-body">
                        <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('contact_bot'); ?></h4>
                        <?php 
                        echo render_input('contact_name', 'contact_name', $bot['contact_name'] ?? '', '', ['placeholder' => _l('enter_contact_name')], [], 'tw-w-full tw-mb-4');
                        echo render_input('contact_first_name', 'contact_first_name', $bot['contact_first_name'] ?? '', '', ['placeholder' => _l('enter_contact_first_name')], [], 'tw-w-full tw-mb-4');
                        echo render_input('contact_last_name', 'contact_last_name', $bot['contact_last_name'] ?? '', '', ['placeholder' => _l('enter_contact_last_name')], [], 'tw-w-full tw-mb-4');
                        echo render_input('contact_number', 'contact_number', $bot['contact_number'] ?? '', '', ['placeholder' => _l('enter_contact_number')], [], 'tw-w-full tw-mb-4');
                        echo render_input('contact_email', 'contact_email', $bot['contact_email'] ?? '', '', ['placeholder' => _l('enter_contact_email')], [], 'tw-w-full tw-mb-4');
                        ?>
                    </div>
                </div>
            </div>
            


             <!-- Menu Bot Section with Collapsible Submenu -->
<div class="col-md-8 menu-bot-section hide">
    <div class="panel_s tw-shadow-lg tw-rounded-md tw-bg-white tw-p-6">
        <div class="panel-body">
            <h4 class="tw-font-semibold tw-text-lg tw-text-neutral-700"><?php echo _l('menu_bot'); ?></h4>

            <!-- Main Menu Container (Collapsible Tree Structure) -->
            <div id="menu-container" class="tw-mb-4">
                <ul class="menu-tree">
                    <?php if (!empty($bot['menu_items'])): ?>
                      
                    <?php else: ?>
                        <!-- When there are no menu items, the container should be empty but the Add Menu Item button should still work -->
                        <li class="menu-item tw-mb-6">
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Button to add new menu item, should always be visible -->
            <button type="button" class="btn btn-primary tw-bg-blue-500 tw-text-white tw-rounded-md tw-py-1 tw-px-4 add-menu-item">Add Menu Item</button>
        </div>
    </div>
</div>

    </div>   <!-- Save Button -->
        <div class="row">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-primary"><?php echo _l('save_bot'); ?></button>
            </div>
        </div>

            </div>
     
        <?php echo form_close(); ?>
    </div>
<?php init_tail(); ?>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/alyssaxuu/flowy/flowy.min.css"> 
<script src="https://cdn.jsdelivr.net/gh/alyssaxuu/flowy/flowy.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize Flowy with callbacks and spacing options
    flowy(
        document.getElementById("canvas"), 
        onGrab, 
        onRelease, 
        onSnap, 
        onRearrange, 
        40, // spacing_x
        100 // spacing_y
    );

    // Callbacks for different events in Flowy
    function onGrab(block) {
        console.log("Block grabbed:", block);
    }

    function onRelease() {
        console.log("Block released");
    }

    function onSnap(block, first, parent) {
        console.log("Block snapped:", block, "First block:", first, "Parent block:", parent);
        return true; // Allow snapping
    }

    function onRearrange(block, parent) {
        console.log("Block rearranged:", block, "New parent:", parent);
        return true; // Allow rearrangement
    }
    
    // Example: Exporting the flowchart data (you can integrate it with WhatsApp Cloud API)
    function exportFlowchart() {
        const flowchartData = flowy.output();
        console.log("Exported Flowchart Data:", JSON.stringify(flowchartData));
    }

    // Add a button to export flowchart
    const exportBtn = document.createElement('button');
    exportBtn.textContent = 'Export Flowchart';
    exportBtn.onclick = exportFlowchart;
    document.querySelector('.panel-body').appendChild(exportBtn);
});
</script>


<script>

$(document).ready(function () {
    var itemIdCounter = 1;  // Unique ID counter for all menu items

    // Recursive function to generate menu items from JSON
    function generateMenuHTML(menuItems, parentId = 0) {
        let html = '<ul class="menu-tree">';  // Ensure correct class name is applied
        if (menuItems && Object.keys(menuItems).length > 0) { // Check if menuItems is not null or undefined
            Object.keys(menuItems).forEach(function (key) {
                let menuItem = menuItems[key];
                if (menuItem.menu_item_parent_id == parentId) {
                    itemIdCounter = Math.max(itemIdCounter, parseInt(menuItem.menu_item_id)); // Update the counter to prevent ID clashes

                    html += `
                        <li class="menu-item tw-mb-6">
                            <label class="tw-block tw-font-semibold tw-mb-2">Menu Item</label>
                            <input type="text" name="menu_items[${menuItem.menu_item_id}][menu_item]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" value="${menuItem.menu_item}" placeholder="Enter Menu Item">

                            <label class="tw-block tw-font-semibold tw-mb-2">Message</label>
                            <textarea name="menu_items[${menuItem.menu_item_id}][message]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" placeholder="Enter Menu Message">${menuItem.message}</textarea>

                            <input type="hidden" name="menu_items[${menuItem.menu_item_id}][menu_item_id]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" value="${menuItem.menu_item_id}" readonly>

                            <input type="hidden" name="menu_items[${menuItem.menu_item_id}][menu_item_parent_id]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" value="${menuItem.menu_item_parent_id}" readonly>

                            <button type="button" class="btn btn-primary tw-bg-blue-500 tw-text-white tw-rounded-md tw-py-1 tw-px-4 add-submenu" data-item-id="${menuItem.menu_item_id}">Add Submenu</button>

                            <ul class="submenu-container">
                                ${generateMenuHTML(menuItems, menuItem.menu_item_id)} <!-- Recursively render submenus -->
                            </ul>
                        </li>
                    `;
                }
            });
        } else {
            // If no menu items, display an empty list
            html += '<li class="menu-item tw-mb-6"></li>';
        }
        html += '</ul>';
        return html;
    }

    // Load and render the existing menu structure from PHP, handle null/empty scenario
    var menuItems = <?php echo json_encode(json_decode($bot['menu_items'] ?? '[]', true)); ?>;
    var renderedMenu = generateMenuHTML(menuItems);
    $('#menu-container').html(renderedMenu);  // Inject the rendered HTML

    // Function to add a new menu item
    $('.add-menu-item').on('click', function () {
        var menuItemId = ++itemIdCounter;  // Generate a unique ID for the menu item
        var newMenuItem = `
            <li class="menu-item tw-mb-6">
                <label class="tw-block tw-font-semibold tw-mb-2">Menu Item</label>
                <input type="text" name="menu_items[${menuItemId}][menu_item]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" placeholder="Enter Menu Item">

                <label class="tw-block tw-font-semibold tw-mb-2">Message</label>
                <textarea name="menu_items[${menuItemId}][message]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" placeholder="Enter Menu Message"></textarea>

                <input type="hidden" name="menu_items[${menuItemId}][menu_item_id]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" value="${menuItemId}" readonly>

                <input type="hidden" name="menu_items[${menuItemId}][menu_item_parent_id]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" value="0" readonly>

                <button type="button" class="btn btn-primary tw-bg-blue-500 tw-text-white tw-rounded-md tw-py-1 tw-px-4 add-submenu" data-item-id="${menuItemId}">Add Submenu</button>

                <ul class="submenu-container"></ul>
            </li>
        `;
        $('.menu-tree').append(newMenuItem);
    });

    // Function to add a submenu under a menu item
    $(document).on('click', '.add-submenu', function () {
        var parentItemId = $(this).data('item-id');  // Get the parent menu's item ID
        var submenuItemId = ++itemIdCounter;  // Generate a unique ID for the submenu item

        var newSubmenuItem = `
            <li class="submenu-item tw-ml-6 tw-mb-4">
                <label class="tw-block tw-font-semibold tw-mb-2">Submenu Item</label>
                <input type="text" name="menu_items[${submenuItemId}][menu_item]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" placeholder="Enter Submenu Item">

                <label class="tw-block tw-font-semibold tw-mb-2">Message</label>
                <textarea name="menu_items[${submenuItemId}][message]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" placeholder="Enter Submenu Message"></textarea>

                <input type="hidden" name="menu_items[${submenuItemId}][menu_item_id]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" value="${submenuItemId}" readonly>

                <input type="hidden" name="menu_items[${submenuItemId}][menu_item_parent_id]" class="tw-w-full tw-mb-2 tw-rounded-md tw-border-gray-300" value="${parentItemId}" readonly>

                <button type="button" class="btn btn-primary tw-bg-blue-500 tw-text-white tw-rounded-md tw-py-1 tw-px-4 add-submenu" data-item-id="${submenuItemId}">Add Submenu</button>

                <ul class="submenu-container"></ul>
            </li>
        `;
        $(this).siblings('.submenu-container').append(newSubmenuItem);
    });
});
</script>

<script>

function previewMedia() {
    const fileInput = document.getElementById('filename');
    const previewContainer = document.getElementById('media-preview');
    const file = fileInput.files[0];
    
    // Clear previous preview
    previewContainer.innerHTML = '';

    if (file) {
        const fileType = file.type;
        const reader = new FileReader();

        reader.onload = function (e) {
            let mediaElement;

            // Create appropriate media element based on file type
            if (fileType.startsWith('image/')) {
                mediaElement = document.createElement('img');
                mediaElement.src = e.target.result;
                mediaElement.style.maxWidth = '100%';
                mediaElement.style.borderRadius = '8px';
            } else if (fileType.startsWith('video/')) {
                mediaElement = document.createElement('video');
                mediaElement.src = e.target.result;
                mediaElement.controls = true;
                mediaElement.style.maxWidth = '100%';
                mediaElement.style.borderRadius = '8px';
            } else if (fileType.startsWith('audio/')) {
                mediaElement = document.createElement('audio');
                mediaElement.src = e.target.result;
                mediaElement.controls = true;
                mediaElement.style.width = '100%';
            } else {
                mediaElement = document.createElement('p');
                mediaElement.textContent = 'File preview not available for this type.';
            }

            previewContainer.appendChild(mediaElement);
        };

        reader.readAsDataURL(file);
    }
}

    // Function to toggle bot sections based on the selected bot type
    function handleBotTypeChange() {
        var botType = $('#bot_type').val();
        // Hide all sections initially
        $('.message-bot-section, .template-bot-section, .media-bot-section, .interactive-buttons-bot-section, .location-bot-section, .list-bot-section, .quick-reply-bot-section, .sticker-bot-section, .contact-bot-section, .poll-bot-section, .menu-bot-section, .flow-bot-section').addClass('hide');

        // Show the appropriate section based on bot type
        switch (botType) {
            case '1': $('.message-bot-section').removeClass('hide'); break;
            case '2': $('.template-bot-section').removeClass('hide'); break;
            case '3': $('.menu-bot-section').removeClass('hide'); break;
            case '4': $('.flow-bot-section').removeClass('hide'); break;
            case '5': $('.media-bot-section').removeClass('hide'); break;
            case '6': $('.location-bot-section').removeClass('hide'); break;
            case '7': $('.message-bot-section').removeClass('hide');$('.interactive-buttons-bot-section').removeClass('hide');$('.hdft').removeClass('hide'); break;
            case '8': $('.list-bot-section').removeClass('hide'); break;
            case '9': $('.quick-reply-bot-section').removeClass('hide'); break;
            case '10': $('.sticker-bot-section').removeClass('hide'); break;
            case '11': $('.contact-bot-section').removeClass('hide'); break;
            case '12': $('.poll-bot-section').removeClass('hide'); break;
        }
    }

    $(document).ready(function() {
        handleBotTypeChange();
        $('#template_id').trigger('change');

        // Toggle sections on bot type change
        $('#bot_type').change(function() {
            handleBotTypeChange();
        });

       // Example 1: Load templates dynamically based on the bot type

        // Example 2: Preview media file before upload for Media Bots
        $('#media_file').change(function() {
            var file = this.files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                $('.previewImage').html('<img src="' + e.target.result + '" class="tw-max-w-full tw-h-auto" />');
            };
            reader.readAsDataURL(file);
        });

        // Example 3: Limit the number of poll options for Poll Bots
        $('.poll-bot-section').on('input', '.poll_option', function() {
            var filledOptions = $('.poll_option').filter(function() {
                return this.value.trim() !== "";
            }).length;

            if (filledOptions >= 5) {
                $('.poll_option').not(':filled').attr('disabled', true);
            } else {
                $('.poll_option').removeAttr('disabled');
            }
        });

        // Example 5: Auto-generate button IDs for Interactive Buttons Bots
        $('.interactive-buttons-bot-section').on('input', '.button', function() {
            var buttonIndex = $(this).closest('.tw-w-1/3').index() + 1;
            var buttonText = $(this).val().toLowerCase().replace(/\s+/g, '_');
            $('#button' + buttonIndex + '_id').val('btn_' + buttonText);
        });

        // Validation rules
        appValidateForm($('#whatsapp_bot_form'), {
            name: "required",
            trigger: "required",
        });
    });
</script>
