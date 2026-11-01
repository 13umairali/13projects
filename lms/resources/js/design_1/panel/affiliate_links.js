(function ($) {
    'use strict';

    var select2Initialized = false;

    function initSelect2() {
        var $select = $('#courseSearchSelect');

        if (select2Initialized) {
            $select.select2('destroy');
        }

        $select.select2({
            dropdownParent: $('.swal2-container'),
            placeholder: $select.attr('data-placeholder'),
            minimumInputLength: 3,
            allowClear: true,
            ajax: {
                url: '/panel/marketing/affiliates/courses/search',
                dataType: 'json',
                type: 'POST',
                quietMillis: 50,
                data: function (params) {
                    return {
                        term: params.term,
                        type: $('#affiliateTypeSelect').val(),
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };
                },
                processResults: function (data) {
                    return {
                        results: $.map(data, function (item) {
                            return {
                                text: item.title,
                                id: item.id
                            };
                        })
                    };
                }
            }
        });
        select2Initialized = true;
    }

    // Open generate link modal
    $('body').on('click', '#generateLinkBtn', function (e) {
        e.preventDefault();

        var path = '/panel/marketing/affiliates/courses/generate-link-modal';

        handleBasicModal(path, generateAffiliateLinkLang, function (result, $body, $footer) {
            // Init select2 after modal content is loaded
            setTimeout(function () {
                initSelect2();
            }, 100);

            // Footer buttons
            var footerHtml = '<div class="d-flex align-items-center justify-content-end">' +
                '<button type="button" class="btn btn-sm btn-gray-200 mr-8 js-close-swal-modal">' + cancelLang + '</button>' +
                '<button type="button" class="btn btn-sm btn-primary js-confirm-generate-link">' + generateLinkLang + '</button>' +
                '</div>';
            $footer.html(footerHtml);
        }, '', '514px');
    });

    // When type changes, reinit select2
    $('body').on('change', '#affiliateTypeSelect', function () {
        if (select2Initialized) {
            $('#courseSearchSelect').val(null).trigger('change');
            initSelect2();
        }
    });

    // Close modal
    $('body').on('click', '.js-close-swal-modal', function () {
        Swal.close();
    });

    // Confirm generate link
    $('body').on('click', '.js-confirm-generate-link', function () {
        var itemId = $('#courseSearchSelect').val();
        if (!itemId) {
            $('#courseSelectError').text(pleaseSelectACourseLang).show().addClass('d-block');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true);

        $.ajax({
            url: '/panel/marketing/affiliates/courses/store',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                item_id: itemId,
                type: $('#affiliateTypeSelect').val()
            },
            success: function (response) {
                if (response.code === 200) {
                    Swal.close();
                    window.location.reload();
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var errorMsg = errors.item_id ? errors.item_id[0] : requestFailedLang;
                    $('#courseSelectError').text(errorMsg).show().addClass('d-block');
                }
                $btn.prop('disabled', false);
            }
        });
    });

    // Copy affiliate link
    $('body').on('click', '.js-copy-affiliate-link', function (e) {
        e.preventDefault();
        var $btn = $(this);
        var link = $btn.attr('data-link');
        
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val(link).select();
        document.execCommand("copy");
        $temp.remove();

        var originalText = $btn.html();
        var successText = (typeof copiedLang !== 'undefined') ? copiedLang : 'Copied';

        $btn.html('<span class="text-success">' + successText + '</span>');

        setTimeout(function() {
            $btn.html(originalText);
        }, 2000);
    });

})(jQuery);
