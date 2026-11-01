(function ($) {
    "use strict";


    /* ===========
    * Contact Info
    * ======== */

    $('body').on('click', '.js-view-employer-contact', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $this = $(this);
        const title = $this.attr('data-title');
        const path = $this.attr('href');

        handleBasicModal(path, title, function (result, $body, $footer) {
            $footer.remove();

        }, '', '514px')
    });

    /* ===========
    * Approve/Reject Request
    * ======== */
    $('body').on('click', '.js-job-request-moderate-action', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $this = $(this);
        const title = $this.attr('data-title');
        const path = $this.attr('href');

        handleBasicModal(path, title, function (result, $body, $footer) {
            const footerHtml = `<div class="d-flex align-items-center justify-content-end gap-12">
                    <button type="button" class="js-moderate-job-request-submit-btn btn font-14 ${(result.type === 'approve' ? 'btn-primary' : 'btn-danger')}">${result.btn_text}</button>
                </div>`;
            $footer.html(footerHtml);

            const $select2 = $body.find('.select2');
            handleSelect2($select2);

        }, '', '514px')
    });

    $('body').on('click', '.js-moderate-job-request-submit-btn', function (e) {
        e.preventDefault();

        const $this = $(this);
        const $form = $this.closest('.js-custom-modal').find('.js-moderate-job-request-form');
        const path = $form.attr('action');

        handleSendRequestItemForm($form, $this, path)
    });


    /* ===========
    * Pin
    * ======== */

    $('body').on('click', '.js-pin-job-request', function (e) {
        e.preventDefault();

        const $this = $(this);
        const path = $this.attr('data-path');

        if ($this.hasClass('text-warning')) {
            $this.removeClass('text-warning').addClass('text-gray-500');
        } else {
            $this.removeClass('text-gray-500').addClass('text-warning');
        }

        $.get(path, function (result) {
            if (result.code === 200) {
                showToast('success', result.title, result.msg)
            }
        }).fail(function () {
            showToast('error', oopsLang, somethingWentWrongLang)
        })
    });


})(jQuery);
