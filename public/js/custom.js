/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */

"use strict";
// for pos system
var session_key = $(location).attr("href").split('/').pop();
//

$(function () {
    if ($('.custom-scroll').length) {
        $(".custom-scroll").niceScroll();
        $(".custom-scroll-horizontal").niceScroll();
    }


    // loadConfirm();


});

//  document.querySelectorAll('table').forEach((table) => {

//      new simpleDatatables.DataTable(table, {
//         perPage: 1000000,
//         paging: true,
//         perPageSelect: false,
//     });
//  });

$(document).ready(function () {

    if ($(".datatable").length > 0) {
        const dataTable = new simpleDatatables.DataTable(".datatable", {
            perPage: 50,
            paging: true,
            perPageSelect: false,

        });
    }

    select2();
    summernote();
    daterange();
    // loadConfirm();
});


function daterange() {
    if ($("#pc-daterangepicker-1").length > 0) {
        document.querySelector("#pc-daterangepicker-1").flatpickr({
            mode: "range"
        });
    }
}


function select2() {
    if ($(".select2").length > 0) {
        $($(".select2")).each(function (index, element) {
            var id = $(element).attr('id');
            var multipleCancelButton = new Choices(
                '#' + id, {
                removeItemButton: true,
            }
            );
        });

    }

}

function show_toastr(type, message) {

    var f = document.getElementById('liveToast');
    var a = new bootstrap.Toast(f).show();
    if (type == 'success') {
        $('#liveToast').addClass('bg-primary');
    } else {
        $('#liveToast').addClass('bg-danger');
    }
    $('#liveToast .toast-body').html(message);
}

function closeActiveBootstrapModal() {
    var $modal = $('.modal.show').last();
    if ($modal.length && window.bootstrap) {
        var instance = bootstrap.Modal.getInstance($modal[0]) || new bootstrap.Modal($modal[0]);
        instance.hide();
    } else {
        $('.modal.show').modal('hide');
    }
}

function ajaxModalForm(options) {
    var settings = $.extend({
        formSelector: '.ajax-modal-form',
        submitText: 'Processing...',
        onSuccess: null,
        onError: null,
        closeOnSuccess: true,
        showToast: true
    }, options || {});

    $(document).off('submit.ajaxModalForm', settings.formSelector).on('submit.ajaxModalForm', settings.formSelector, function (e) {
        e.preventDefault();

        var $form = $(this);
        var requestKey = ($form.attr('method') || 'POST') + ':' + ($form.attr('action') || '');
        window.ajaxModalFormLocks = window.ajaxModalFormLocks || {};

        if (window.ajaxModalFormLocks[requestKey]) {
            if (typeof show_toastr === 'function') {
                show_toastr('error', 'Request already processing. Please wait.', 'error');
            }
            return false;
        }

        if ($form.data('processing')) {
            return false;
        }

        if (this.checkValidity && !this.checkValidity()) {
            this.reportValidity();
            return false;
        }

        var formData = new FormData(this);
        var $submit = $form.find('[type="submit"]');
        var originalText = $submit.val() || $submit.text();
        var unlockTimer = null;

        function unlockSubmit() {
            $form.data('processing', false);
            $form.find('[type="submit"]').prop('disabled', false);
            if ($submit.is('input')) {
                $submit.val(originalText);
            } else {
                $submit.text(originalText);
            }
        }

        $form.data('processing', true);
        window.ajaxModalFormLocks[requestKey] = true;
        $form.find('[type="submit"]').prop('disabled', true);
        if ($submit.is('input')) {
            $submit.val(settings.submitText);
        } else {
            $submit.text(settings.submitText);
        }

        unlockTimer = setTimeout(function () {
            if ($form.data('processing')) {
                if ($submit.is('input')) {
                    $submit.val('Still processing...');
                } else {
                    $submit.text('Still processing...');
                }
            }
        }, 5000);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            url: $form.attr('action'),
            type: $form.attr('method') || 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response && response.success === false) {
                    handleAjaxModalFormError($form, { responseJSON: response }, settings);
                    return;
                }

                if (typeof settings.onSuccess === 'function') {
                    settings.onSuccess(response, $form);
                }

                if (settings.closeOnSuccess) {
                    closeActiveBootstrapModal();
                }

                if (settings.showToast && response && response.message && typeof show_toastr === 'function') {
                    show_toastr('success', response.message, 'success');
                }
            },
            error: function (xhr) {
                handleAjaxModalFormError($form, xhr, settings);
            },
            complete: function () {
                clearTimeout(unlockTimer);
                delete window.ajaxModalFormLocks[requestKey];
                unlockSubmit();
            }
        });
    });
}

function handleAjaxModalFormError($form, xhr, settings) {
    var message = 'Something went wrong. Please check the form and try again.';

    if (xhr.responseJSON) {
        if (xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        if (xhr.responseJSON.errors) {
            message = Object.values(xhr.responseJSON.errors).flat().join('<br>');
        }
    }

    if (typeof settings.onError === 'function') {
        settings.onError(message, xhr, $form);
        return;
    }

    if (typeof show_toastr === 'function') {
        show_toastr('error', $('<div>').html(message).text(), 'error');
    } else if (window.Swal) {
        Swal.fire({ icon: 'error', title: 'Error', html: message });
    } else {
        alert($('<div>').html(message).text());
    }
}

$(document).on('click', 'a[data-ajax-popup="true"], button[data-ajax-popup="true"], div[data-ajax-popup="true"]', function () {

    var data = {};
    var title = $(this).data("title");

    if (title == undefined || title === '') {
        title = $(this).data("bs-title");
    }

    if (title == undefined || title === '') {
        title = $(this).data("bs-original-title");
    }

    if (title == undefined || title === '') {
        title = $(this).data("original-title");
    }

    if (title == undefined || title === '') {
        var toggleTitle = $(this).data("bs-toggle");
        title = (toggleTitle != undefined && toggleTitle !== 'tooltip') ? toggleTitle : '';
    }

    $('#commonModal .modal-dialog').removeClass('modal-sm modal-md modal-lg modal-xl modal-fullscreen');
    var rawSize = $(this).data('size');
    var size = (!rawSize) ? 'md' : String(rawSize);
    var modalSizeClass = size.indexOf('modal-') === 0 ? size : 'modal-' + size;

    var url = $(this).data('url');
    $("#commonModal .modal-title").html(title);
    $("#commonModal .modal-dialog").addClass(modalSizeClass);

    if ($('#vc_name_hidden').length > 0) {
        data['vc_name'] = $('#vc_name_hidden').val();
    }
    if ($('#warehouse_name_hidden').length > 0) {
        data['warehouse_name'] = $('#warehouse_name_hidden').val();
    }
    if ($('#discount_hidden').length > 0) {
        data['discount'] = $('#discount_hidden').val();
    }
    $.ajax({
        url: url,
        data: data,
        success: function (data) {
            $('#commonModal .body').html(data);
            $("#commonModal").modal('show');
            // daterange_set();
            taskCheckbox();
            common_bind("#commonModal");
            commonLoader();

        },
        error: function (data) {
            data = data.responseJSON;
            show_toastr('Error', data.error, 'error')
        }
    });

});


function arrayToJson(form) {
    var data = $(form).serializeArray();
    var indexed_array = {};

    $.map(data, function (n, i) {
        indexed_array[n['name']] = n['value'];
    });

    return indexed_array;
}


function common_bind() {
    select2();
}


function taskCheckbox() {
    var checked = 0;
    var count = 0;
    var percentage = 0;

    count = $("#check-list input[type=checkbox]").length;
    checked = $("#check-list input[type=checkbox]:checked").length;
    percentage = parseInt(((checked / count) * 100), 10);
    if (isNaN(percentage)) {
        percentage = 0;
    }
    $(".custom-label").text(percentage + "%");
    $('#taskProgress').css('width', percentage + '%');


    $('#taskProgress').removeClass('bg-warning');
    $('#taskProgress').removeClass('bg-primary');
    $('#taskProgress').removeClass('bg-success');
    $('#taskProgress').removeClass('bg-danger');

    if (percentage <= 15) {
        $('#taskProgress').addClass('bg-danger');
    } else if (percentage > 15 && percentage <= 33) {
        $('#taskProgress').addClass('bg-warning');
    } else if (percentage > 33 && percentage <= 70) {
        $('#taskProgress').addClass('bg-primary');
    } else {
        $('#taskProgress').addClass('bg-success');
    }
}


function commonLoader() {
    $('[data-bs-toggle="tooltip"]').tooltip();
    if ($('[data-toggle="tags"]').length > 0) {
        $('[data-toggle="tags"]').tagsinput({ tagClass: "badge badge-primary" });
    }


    // $(function(){
    //
    //     var dtToday = new Date();
    //
    //     var month = dtToday.getMonth() + 1;
    //     var day = dtToday.getDate();
    //     var year = dtToday.getFullYear();
    //     if(month < 10)
    //         month = '0' + month.toString();
    //     if(day < 10)
    //         day = '0' + day.toString();
    //
    //     var maxDate = year + '-' + month + '-' + day;
    //
    //     $("input[type='date']").attr('max', maxDate);
    // });

    var e = $(".scrollbar-inner");
    e.length && e.scrollbar().scrollLock()

    var e1 = $(".custom-input-file");
    e1.length && e1.each(function () {
        var e1 = $(this);
        e1.on("change", function (t) {
            !function (e, t, a) {
                var n, o = e.next("label"), i = o.html();
                t && t.files.length > 1 ? n = (t.getAttribute("data-multiple-caption") || "").replace("{count}", t.files.length) : a.target.value && (n = a.target.value.split("\\").pop()), n ? o.find("span").html(n) : o.html(i)
            }(e1, this, t)
        }), e1.on("focus", function () {
            !function (e) {
                e.addClass("has-focus")
            }(e1)
        }).on("blur", function () {
            !function (e) {
                e.removeClass("has-focus")
            }(e1)
        })
    })

    // var e2 = $('[data-toggle="autosize"]');
    // e2.length && autosize(e2);


    if ($(".jscolor").length) {
        jscolor.installByClassName("jscolor");
    }
    summernote();
    // for Choose file
    $(document).on('change', 'input[type=file]', function () {
        var fileclass = $(this).attr('data-filename');
        var finalname = $(this).val().split('\\').pop();
        $('.' + fileclass).html(finalname);
    });
}


function summernote() {
    if ($(".summernote-simple").length) {
        $('.summernote-simple').summernote({
            dialogsInBody: !0,
            minHeight: 200,
            maxHeight: 300,
            toolbar: [
                ['style', ['style']],
                ["font", ["bold", "italic", "underline", "clear", "strikethrough"]],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ["para", ["ul", "ol", "paragraph"]],
            ],

        });
        $('.dropdown-toggle').dropdown();
    }

    if ($(".summernote-simple-2").length) {
        $('.summernote-simple-2').summernote({
            dialogsInBody: !0,
            minHeight: 200,
            maxHeight: 300,
            toolbar: [
                ['style', ['style']],
                ["font", ["bold", "italic", "underline", "clear", "strikethrough"]],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ["para", ["ul", "ol", "paragraph"]],
            ],

        });
    }

}



$(document).on("click", '.bs-pass-para', function () {
    var form = $(this).closest("form");
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "This action can not be undone. Do you want to continue?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {

            form.submit();

        } else if (
            result.dismiss === Swal.DismissReason.cancel
        ) {
        }
    })
});

//only pos system delete button
$(document).on("click", '.bs-pass-para-pos', function () {

    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        buttonsStyling: false
    })
    swalWithBootstrapButtons.fire({
        title: 'Are you sure?',
        text: "This action can not be undone. Do you want to continue?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes',
        cancelButtonText: 'No',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {

            document.getElementById($(this).data('confirm-yes')).submit();

        } else if (
            result.dismiss === Swal.DismissReason.cancel
        ) {
        }
    })
});


function postAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var jdata = { _token: token };

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: 'POST',
        url: url,
        data: jdata,
        success: function (data) {
            if (typeof (data) === 'object') {
                cb(data);
            } else {
                cb(data);
            }
        },
    });
}

//end only pos system delete button


function deleteAjax(url, data, cb) {
    var token = $('meta[name="csrf-token"]').attr('content');
    var jdata = { _token: token };

    for (var k in data) {
        jdata[k] = data[k];
    }

    $.ajax({
        type: 'DELETE',
        url: url,
        data: jdata,
        success: function (data) {
            if (typeof (data) === 'object') {
                cb(data);
            } else {
                cb(data);
            }
        },
    });
}

function ajaxDeleteForm(options) {
    var settings = $.extend({
        selector: '.ajax-delete',
        formIdAttribute: 'form-id',
        confirmAttribute: 'confirm',
        defaultConfirm: 'Are you sure?',
        processingClass: 'disabled',
        showToast: true,
        onSuccess: null,
        onError: null
    }, options || {});

    $(document).off('click.ajaxDeleteForm', settings.selector).on('click.ajaxDeleteForm', settings.selector, function (e) {
        e.preventDefault();

        var $button = $(this);
        var formId = $button.data(settings.formIdAttribute);
        var $form = formId ? $('#' + formId) : $button.closest('form');
        var confirmMessage = $button.data(settings.confirmAttribute) || settings.defaultConfirm;

        if (!$form.length) {
            if (typeof show_toastr === 'function') {
                show_toastr('error', 'Delete form not found.', 'error');
            }
            return false;
        }

        if (confirmMessage && !confirm(confirmMessage)) {
            return false;
        }

        if ($button.data('processing')) {
            return false;
        }

        $button.data('processing', true).addClass(settings.processingClass);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            url: $form.attr('action'),
            type: $form.attr('method') || 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function (response) {
                if (response && response.success === false) {
                    handleAjaxDeleteFormError(response.message, response, $button, settings);
                    return;
                }

                if (typeof settings.onSuccess === 'function') {
                    settings.onSuccess(response, $button, $form);
                }

                if (settings.showToast && response && response.message && typeof show_toastr === 'function') {
                    show_toastr('success', response.message, 'success');
                }
            },
            error: function (xhr) {
                var response = xhr.responseJSON || {};
                handleAjaxDeleteFormError(response.message || 'Something went wrong.', response, $button, settings);
            },
            complete: function () {
                $button.data('processing', false).removeClass(settings.processingClass);
            }
        });
    });
}

function handleAjaxDeleteFormError(message, response, $button, settings) {
    if (typeof settings.onError === 'function') {
        settings.onError(message, response, $button);
        return;
    }

    if (typeof show_toastr === 'function') {
        show_toastr('error', message || 'Something went wrong.', 'error');
    } else {
        alert(message || 'Something went wrong.');
    }
}

// Google calendar
$(document).on('click', '.local_calender .fc-daygrid-event, .fc-timegrid-event', function (e) {
    // if (!$(this).hasClass('project')) {
    e.preventDefault();
    var event = $(this);
    var title1 = $('.fc-event-title').html();
    var title2 = $(this).data("bs-original-title");
    var title = (title1 != undefined) ? title1 : title2;
    // var size = ($(this).data('size') == '') ? 'md' : $(this).data('size');
    var size = 'md';
    var url = $(this).attr('href');
    $("#commonModal .modal-title").html(title);
    $("#commonModal .modal-dialog").addClass('modal-' + size);
    $.ajax({
        url: url,
        success: function (data) {
            $('#commonModal .body').html(data);
            $("#commonModal").modal('show');
            common_bind();
        },
        error: function (data) {
            data = data.responseJSON;
            toastrs('Error', data.error, 'error')
        }
    });
    // }
});

//date value 4

// $(function(){
//
//     var dtToday = new Date();
//
//     var month = dtToday.getMonth() + 1;
//     var day = dtToday.getDate();
//     var year = dtToday.getFullYear();
//     if(month < 10)
//         month = '0' + month.toString();
//     if(day < 10)
//         day = '0' + day.toString();
//
//     var maxDate = year + '-' + month + '-' + day;
//
//     $("input[type='date']").attr('max', maxDate);
// });

function addCommas(num) {
    var number = parseFloat(num).toFixed(2).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
    return ((site_currency_symbol_position == "pre") ? site_currency_symbol : '') + number + ((site_currency_symbol_position == "post") ? site_currency_symbol : '');
}



// PLUS MINUS QUANTITY JS
function wcqib_refresh_quantity_increments() {
    jQuery("div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)").each(function (a, b) {
        var c = jQuery(b);
        c.addClass("buttons_added"),
            c.children().first().before('<input type="button" value="-" class="minus" />'),
            c.children().last().after('<input type="button" value="+" class="plus" />')
    })
}

String.prototype.getDecimals || (String.prototype.getDecimals = function () {
    var a = this,
        b = ("" + a).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
    return b ? Math.max(0, (b[1] ? b[1].length : 0) - (b[2] ? +b[2] : 0)) : 0
}), jQuery(document).ready(function () {
    wcqib_refresh_quantity_increments()
}), jQuery(document).on("updated_wc_div", function () {
    wcqib_refresh_quantity_increments()
}), jQuery(document).on("click", ".plus, .minus", function () {
    var a = jQuery(this).closest(".quantity").find('input[name="quantity"], input[name="quantity[]"]'),
        b = parseFloat(a.val()),
        c = parseFloat(a.attr("max")),
        d = parseFloat(a.attr("min")),
        e = a.attr("step");
    b && "" !== b && "NaN" !== b || (b = 0), "" !== c && "NaN" !== c || (c = ""), "" !== d && "NaN" !== d || (d = 0), "any" !== e && "" !== e && void 0 !== e && "NaN" !== parseFloat(e) || (e = 1), jQuery(this).is(".plus") ? c && b >= c ? a.val(c) : a.val((b + parseFloat(e)).toFixed(e.getDecimals())) : d && b <= d ? a.val(d) : b > 0 && a.val((b - parseFloat(e)).toFixed(e.getDecimals())), a.trigger("change")
});

$(document).on('click', 'input[name="quantity"], input[name="quantity[]"]', function (e) {
    // Allow: backspace, delete, tab, escape, enter and .
    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
        // Allow: Ctrl+A
        (e.keyCode == 65 && e.ctrlKey === true) ||
        // Allow: home, end, left, right
        (e.keyCode >= 35 && e.keyCode <= 39)) {
        // let it happen, don't do anything
        return;
    }
    // Ensure that it is a number and stop the keypress
    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
    }
});


//for ai module
$(document).on('click', 'a[data-ajax-popup-over="true"], button[data-ajax-popup-over="true"], div[data-ajax-popup-over="true"]', function () {
    var validate = $(this).attr('data-validate');
    var id = '';
    if (validate) {
        id = $(validate).val();
    }
    var title_over = $(this).data('title');
    $('#commonModalOver .modal-dialog').removeClass('modal-sm modal-md modal-lg modal-xl modal-fullscreen');
    var rawSizeOver = $(this).data('size');
    var size_over = (!rawSizeOver) ? 'md' : String(rawSizeOver);
    var modalSizeOverClass = size_over.indexOf('modal-') === 0 ? size_over : 'modal-' + size_over;

    var url = $(this).data('url');
    $("#commonModalOver .modal-title").html(title_over);
    $("#commonModalOver .modal-dialog").addClass(modalSizeOverClass);
    $.ajax({
        url: url + '?id=' + id,
        success: function (data) {
            $('#commonModalOver .modal-body').html(data);
            $("#commonModalOver").modal('show');
            taskCheckbox();
        },
        error: function (data) {
            data = data.responseJSON;
            show_toastr('Error', data.error, 'error')
        }
    });
});



//start input serach box
function JsSearchBox() {
    if ($(".js-searchBox").length) {
        $(".js-searchBox").each(function (index) {
            if ($(this).parent().find('.formTextbox').length == 0) {
                $(this).searchBox({ elementWidth: '250' });
            }
        });
    }
}

$(document).ready(function () {
    JsSearchBox();

    function JsSearchBox() {
        if ($(".js-searchBox").length) {
            $(".js-searchBox").each(function (index) {
                if ($(this).parent().find('.formTextbox').length === 0) {
                    $(this).searchBox({ elementWidth: '250' });
                }
            });
        }
    }
});

//end input serach box

window.addEventListener("load", function () {
    const items = document.querySelectorAll(".dropdown-menu .dropdown-item");
    items.forEach(item => {
      item.addEventListener("mouseenter", () => {
        item.style.backgroundColor = "#dbdbdb";
      });
      item.addEventListener("mouseleave", () => {
        item.style.backgroundColor = "";
      });
    });
  });
