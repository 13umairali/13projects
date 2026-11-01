(function ($) {
    "use strict"

    $(document).ready(function () {


        /* Bottom Fixed */
        const $bottomFixedCard = $('.job-bottom-fixed-card');
        const $bottomFixedProgress = $('.job-bottom-fixed-card__progress');

        $(document).scroll(function () {
            const scrollTop = $(this).scrollTop();

            if (scrollTop > 500) {
                $bottomFixedCard.addClass('show');
            } else {
                $bottomFixedCard.removeClass('show');
            }

            updateProgressBar()
        });


        function updateProgressBar() {
            const scrollTop = $(window).scrollTop();
            const scrollHeight = $(document).height();
            const clientHeight = $(window).height();

            // Calculate the scroll percentage
            const scrollPercentage = (scrollTop / (scrollHeight - clientHeight)) * 100;

            // Update the width of the progress bar
            $bottomFixedProgress.find('.progress-line').css('width', scrollPercentage + '%');
        }
    })

    /**
     * Bottom Actions
     * */
    $('body').on('click', '.js-scroll-to-job-apply-btn', function (e) {
        e.preventDefault();

        const $container = $('.js-apply-for-job-actions-card');

        $('html, body').animate({
            scrollTop: $container.offset().top - 150
        }, 500);
    })

    /**
     * Share Modal
     * */
    $('body').on('click', '.js-share-job', function (e) {
        e.preventDefault();

        const path = $(this).attr("data-path");

        handleBasicModal(path, shareLang, function (result, $body, $footer) {
            $footer.addClass('d-none')
        }, '', '40rem')
    });

    /**
     * Report Modal
     * */
    $('body').on('click', '.js-report-job', function (e) {
        e.preventDefault();

        const path = $(this).attr("data-path");

        handleBasicModal(path, reportJobLang, function (result, $body, $footer) {
            const footerHtml = `<div class="d-flex align-items-center justify-content-end">
                <button type="button" class="js-submit-job-report btn btn-primary">${reportLang}</button>
            </div>`;
            $footer.html(footerHtml);
        }, '', '40rem')
    });

    $('body').on('click', '.js-submit-job-report', function (e) {
        e.preventDefault();

        const $this = $(this);
        const $form = $this.closest('.js-custom-modal').find('.js-job-report-form')
        const path = $form.attr("action");

        handleSendRequestItemForm($form, $this, path)
    })

    /**
     * Favorite
     * */
    $('body').on('click', '#favoriteToggle', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $this = $(this);
        const $icons = $this.find('.icons');
        const href = $this.attr('href');

        if ($this.hasClass('text-primary')) {
            $this.removeClass('text-primary').addClass('text-gray-500');
            $icons.removeClass('text-primary').addClass('text-gray-500');
        } else {
            $this.removeClass('text-gray-500').addClass('text-primary');
            $icons.removeClass('text-gray-500').addClass('text-primary');
        }

        $.get(href, function (result) {

        });
    });

    /**
     * job demo modal
     * */
    var courseDemoVideoPlayer;

    $('body').on('click', '#jobDemoVideoBtn', function (e) {
        e.preventDefault();

        if (courseDemoVideoPlayer !== undefined) {
            courseDemoVideoPlayer.stop();
        }

        let path = $(this).attr('data-video-path');
        let source = $(this).attr('data-video-source');
        let thumbnail = $(this).attr('data-thumbnail');
        const height = $(window).width() > 991 ? 480 : 264;

        const videoTagId = 'demoVideoPlayer';
        const {html, options} = makeVideoPlayerHtml(path, source, height, videoTagId, thumbnail);

        /*const footer = `<div class="d-flex align-items-center justify-content-end">
                <button type="button" class="close-swl btn btn-transparent mr-16">${closeLang}</button>
        </div>`;*/


        const body = makeModalHtml(jobDemoLang, closeIcon, html, '&nbsp;')

        Swal.fire({
            html: body,
            showCancelButton: false,
            showConfirmButton: false,
            width: '48rem',
            didOpen: function () {
                const $videoTagEl = $(`#${videoTagId}`);

                if ($videoTagEl.length) {
                    courseDemoVideoPlayer = new Plyr(`#${videoTagId}`, options);
                }
            },
        })
    });

})(jQuery)
