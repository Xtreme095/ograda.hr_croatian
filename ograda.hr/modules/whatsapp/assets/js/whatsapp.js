"use strict";

let whatsapp_tribute;
var header_data = '';
var body_data = '';
var footer_data = '';

function whatsapp_refreshTribute() {
    whatsapp_tribute.attach(document.querySelectorAll(".mentionable"));
}

function updatePreviewSection(response) {
    $('.variableDetails').removeClass('hide');
        $('#preview_message').show();

    var content = (/\S/.test(response.view) !== false) ? response.view : '<div class="alert alert-danger">Currently, the variable is not available for this template.</div>';
    $('.variables').html(content);
    $('.selectpicker').selectpicker('refresh');

    let preview_data = `
        <strong class='header_data'>${response.header_data ?? ''}</strong><br><br>
        <p class='body_data'>${response.body_data ?? ''}</p><br>
        <span class="text-muted tw-text-xs footer_data">${response.footer_data ?? ''}</span>
    `;
    let button_data = '';
    if (response.button_data && response.button_data.buttons) {
        $.each(response.button_data.buttons, function(index, val) {
            button_data += `<button class="btn btn-default btn-lg btn-block wtc_button">${val.text}</button>`;
        });
    }
    $('.previewBtn').html(button_data);
    $('.previewImage').html('<div id="header_image"></div>');
    $('.previewmsg').html(preview_data);

    // Update global data
    header_data = response.header_data ?? '';
    body_data = response.body_data ?? '';
    footer_data = response.footer_data ?? '';

    // Trigger input events
    $('.header_input, .body_input, .footer_input').trigger('input').trigger('change');
}

function getContextFromUrl() {
    var currentUrl = window.location.href;
    
   
    // Check if the URL contains 'campaigns' or 'bots'
    if (currentUrl.includes('/whatsapp/campaigns/')) {
        return 'campaign';
    } else if (currentUrl.includes('/whatsapp/bots/')) {
        return 'bot';
    } else {
        return 'unknown';
    }
}

$(function () {
    whatsapp_loadData();
});

function whatsapp_loadData() {
    init_selectpicker();

    // Load merge field
    var fields = _.filter(merge_fields, function (num) {
        return (
            typeof num["leads"] != "undefined" || typeof num["other"] != "undefined" || typeof num["client"] != "undefined"
        );
    });

    var rel_type = $('#rel_type').val();

    if (rel_type == 'leads') {
        rel_type = 'leads';
    } else if (rel_type == 'contacts') {
        rel_type = 'client';
    } else {
        rel_type = 'other';
    }

    var selected_index = _.findIndex(fields, function (data) {
        return _.allKeys(data)[0] == rel_type;
    });

    var options = [];

    if (fields[selected_index]) {
        fields[selected_index][rel_type].forEach((field) => {
            if (field.name != "") {
                options.push({ key: field.name, value: field.key });
            }
        });
    }
    if (rel_type != 'other') {
        fields[2]['other'].forEach((field) => {
            if (field.name != "") {
                options.push({ key: field.name, value: field.key });
            }
        });
    }

    whatsapp_tribute = new Tribute({
        values: options,
        selectClass: "highlights",
    });
    whatsapp_tribute.detach(document.querySelectorAll(".mentionable"));
    whatsapp_tribute.attach(document.querySelectorAll(".mentionable"));
}

$(document).on('input change', '.header_input, .body_input, .footer_input', function () {
    var inputType = null;

    inputType = $(this).hasClass('header_input') ? 'header' :
        $(this).hasClass('body_input') ? 'body' :
            $(this).hasClass('footer_input') ? 'footer' :
                null;

    // Proceed only if inputType is found
    if (inputType) {
        var stringValue = $(this).attr('name');
        // Use a regular expression to extract the number inside the first square bracket
        var match = stringValue.match(/\[(\d+)\]/);
        var key = parseInt(match[1]);
        var value = $(this).val();

        var typeMap = {
            'header': {
                data: header_data,
                selector: '.header'
            },
            'body': {
                data: body_data,
                selector: '.body'
            },
            'footer': {
                data: footer_data,
                selector: '.footer'
            }
        };

        var dataInfo = typeMap[inputType];

        var regex = /{{\d+}}/g; // Regular expression to match '{{' followed by one or more digits and then '}}'
        var matches = dataInfo.data.match(regex);

        var count = matches ? matches.length : 0;

        for (let params = 1; params <= count; params++) {
            dataInfo.data = dataInfo.data.replace("{{" + params + "}}", ($(dataInfo.selector + '\\[' + params + '\\]').val() != "") ? $(dataInfo.selector + '\\[' + params + '\\]').val() : `{{${params}}}`)
        }

        $('.' + inputType + '_data').text(dataInfo.data);
    }
    whatsapp_refreshTribute();
});

$(document).on('change', '#template_id', function () {
    var templateId = $(this).val();
    var context = getContextFromUrl();
    var requestUrl;

    if (context === 'campaign') {
        requestUrl = `${admin_url}whatsapp/campaigns/get_template_map`;
    } else if (context === 'bot') {
        requestUrl = `${admin_url}whatsapp/bots/get_template_map`;
    } else {
        console.error('Unknown context');
        return;
    }

    console.log('Sending AJAX request to:', requestUrl);
    console.log('Template ID:', templateId);

    $.ajax({
        url: requestUrl,
        type: 'POST',
        dataType: 'json',
        data: { 'template_id': templateId },
    })
    .done(function (response) {
        console.log('AJAX response received:', response);
        updatePreviewSection(response);
    })
    .fail(function (jqXHR, textStatus, errorThrown) {
        console.error('AJAX request failed:', textStatus, errorThrown);
        console.log('Response text:', jqXHR.responseText);
    });
});

$(document).on('change', '.header_image', function (event) {
    var imageAttachment = event.target.files[0];
    const maxAllowedSize = $('#maxFileSize').val() * 1024 * 1024;
    var imagePreview = $('#image_url').val() ? $('#image_url').val() : (imageAttachment ? URL.createObjectURL(imageAttachment) : '');
    var imagesSize = imageAttachment ? imageAttachment.size : 0;

    if (imagesSize > maxAllowedSize) {
        alert_float('danger', `Max file size upload ${$('#maxFileSize').val()} (MB)`);
        $('#header_image').empty();
        $(this).val('');
        return;
    }
    if (imagePreview) {
        $('#header_image').html(`<img src="${imagePreview}" class="wtc_image">`);
    }
});

$(document).on('change', '#bot_file', function(event) {
    var imageAttachment = event.target.files[0];
    const maxAllowedSize = $('#maxFileSize').val() * 1024 * 1024;
    var imagesSize = imageAttachment ? imageAttachment.size : 0;

    if (imagesSize > maxAllowedSize) {
        alert_float('danger', `Max file size upload ${$('#maxFileSize').val()} (MB)`);
        $('#bot_file').empty();
        $(this).val('');
        return;
    }
});
