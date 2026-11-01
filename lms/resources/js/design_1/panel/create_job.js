(function ($) {
    "use strict";

    // =========
    // Actions
    // ======

    $('body').on('click', '#sendForReview', function (e) {
        e.preventDefault();
        const $this = $(this);
        const $form = $this.closest('form');

        $this.addClass('loadingbar').prop('disabled', true);

        $form.find('#forDraft').val(0);
        $form.trigger('submit');
    });

    $('body').on('click', '#saveAsDraft', function (e) {
        e.preventDefault();
        const $this = $(this);
        const $form = $this.closest('form');

        $this.addClass('loadingbar').prop('disabled', true);

        $form.find('#forDraft').val(1);
        $form.trigger('submit');
    });

    $('body').on('click', '#getNextStep', function (e) {
        e.preventDefault();
        const $this = $(this);
        const $form = $this.closest('form');

        $this.addClass('loadingbar').prop('disabled', true);

        $form.find('#forDraft').val(1);
        $form.find('#getNext').val(1);
        $form.trigger('submit');
    });

    $('body').on('click', '.js-get-next-step', function (e) {
        e.preventDefault();
        const $this = $(this);
        const $form = $this.closest('form');

        if (!$this.hasClass('active')) {
            $this.addClass('loadingbar').prop('disabled', true);
            const step = $this.attr('data-step');

            $form.find('#getStep').val(step);
            $form.find('#forDraft').val(1);
            $form.find('#getNext').val(1);

            $form.trigger('submit');
        }
    });


    // =========
    // Job Categories
    // ======

    function handleCategoryFilters(path) {
        const $categoriesFiltersContainer = $('#categoriesFiltersContainer');
        const $categoriesFiltersCard = $('#categoriesFiltersCard');

        const loadingHtml = `<div class="js-loading-card d-flex align-items-center justify-content-center w-100 my-40">
                    <img src="/assets/design_1/img/loading.svg" width="56" height="56">
                </div>`;

        $categoriesFiltersContainer.removeClass('d-none');
        $categoriesFiltersCard.html(loadingHtml);

        $.get(path, function (result) {
            if (result.code === 200) {
                $categoriesFiltersCard.html(result.html);
            }
        }).fail(function () {
            $categoriesFiltersContainer.addClass('d-none');
            $categoriesFiltersCard.html('');

            showToast('error', oopsLang, somethingWentWrongLang);
        })
    }

    $('body').on('change', '#jobCategories', function (e) {
        e.preventDefault();
        let category_id = this.value;

        const path = `/panel/jobs/filters/get-by-category-id/${category_id}`

        handleCategoryFilters(path);
    });


    // =========
    // Payment Type
    // ======

    $('body').on('change', '.js-job-payment-type-select', function (e) {
        e.preventDefault();
        const value = $(this).val();
        const $fixed = $('.js-fixed-salary-field');
        const $range = $('.js-range-salary-field');

        $fixed.addClass('d-none');
        $range.addClass('d-none');

        if (value === 'fixed_amount') {
            $fixed.removeClass('d-none');
        } else if (value === 'range') {
            $range.removeClass('d-none');
        }
    })


    // =========
    // Extra Contents
    // ======

    $('body').on('click', '.js-save-course-content', function (e) {
        e.preventDefault();
        const $this = $(this);
        const $form = $this.closest('.js-content-form');

        handleSendRequestItemForm($form, $this)
    });


    // =========
    // Items Locale
    // ======

    $('body').on('change', '.js-job-content-locale', function (e) {
        e.preventDefault();

        const $this = $(this);
        const $form = $(this).closest('.js-content-form');
        const locale = $this.val();
        const jobId = $this.attr('data-job-id');
        const item_id = $this.attr('data-id');
        const relation = $this.attr('data-relation');
        let fields = $this.attr('data-fields');
        fields = fields.split(',');


        $this.addClass('loadingbar gray');

        const path = '/panel/jobs/' + jobId + '/getContentItemByLocale';
        const data = {
            item_id,
            locale,
            relation
        };

        $.post(path, data, function (result) {
            if (result && result.item) {
                const item = result.item;

                Object.keys(item).forEach(function (key) {
                    const value = item[key];

                    if ($.inArray(key, fields) !== -1) {
                        let element = $form.find('.js-ajax-' + key);
                        element.val(value);
                    }
                });

                $this.removeClass('loadingbar gray');
            }
        }).fail(err => {
            $this.removeClass('loadingbar gray');
        });
    });


})(jQuery);
