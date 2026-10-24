<style type="text/css">
    #id_card::after {
        content: "";
        width: 100%;
        height: 100%;
        position: absolute;
        opacity: 0.2;
        top: 0;
        background-image: url('{{ get_logo() }}') !important;
        background-repeat: no-repeat !important;
        background-size: 100px auto !important;
        background-position: center !important;
    }

    @php
        // Converts one Theme Designer slot into a CSS background value -
        // either a solid color or a two-color gradient, whichever the
        // admin chose. Shared logic for every slot below.
        $themeBackground = function (string $key, string $default1, string $default2) {
            $type = get_option($key.'_type') ?: 'solid';
            $color1 = get_option($key.'_color') ?: $default1;
            $color2 = get_option($key.'_color_2') ?: $default2;

            return $type === 'gradient'
                ? "linear-gradient(135deg, {$color1}, {$color2})"
                : $color1;
        };

        $themePrimary = $themeBackground('theme_primary', '#1b2a4a', '#101a2e');
        $themeSecondary = $themeBackground('theme_secondary', '#b08d57', '#96723f');
        $themeHeader = $themeBackground('theme_header', '#ffffff', '#f5f8fa');
        $themeFooter = $themeBackground('theme_footer', '#101a2e', '#1b2a4a');
        $themeButton = $themeBackground('theme_button', '#b08d57', '#96723f');
        $themeMenu = $themeBackground('theme_menu', '#ffffff', '#f5f8fa');
        $themeSubmenu = $themeBackground('theme_submenu', '#1b2a4a', '#101a2e');
        $themeText = get_option('theme_text_color') ?: '#181C32';
        $themeHover = get_option('theme_hover_color') ?: '#b08d57';

        // Bootstrap's own RGB channel variables (--bs-primary-rgb etc.) are
        // needed for its opacity-based utilities (bg-primary/10 and
        // similar) to keep working correctly with a custom color.
        $hexToRgb = function (string $hex) {
            $hex = ltrim($hex, '#');
            if (strlen($hex) !== 6) {
                return '27,42,74';
            }

            return implode(',', array_map(fn ($c) => hexdec($c), str_split($hex, 2)));
        };
        $primarySolid = get_option('theme_primary_color') ?: '#1b2a4a';
        $secondarySolid = get_option('theme_secondary_color') ?: '#b08d57';
    @endphp

    /* =========================================================
       THEME DESIGNER — applies the admin's chosen colors across
       both the public website's own stylesheet AND this dashboard.
       Previous selectors here (.sidebar .nav li, .metismenu li,
       [data-background-color]) were written for a different sidebar
       template and never matched this dashboard's actual markup
       (.menu-item / .menu-link / .aside) — corrected below.
    ========================================================= */
    :root {
        --bs-primary: {{ $primarySolid }};
        --bs-primary-rgb: {{ $hexToRgb($primarySolid) }};
        --bs-secondary: {{ $secondarySolid }};
        --bs-secondary-rgb: {{ $hexToRgb($secondarySolid) }};
        --bs-link-color: {{ $themeHover }};
        --bs-body-color: {{ $themeText }};
    }

    /* Sidebar / menu background */
    .aside, #kt_aside {
        background: {{ $themeMenu }} !important;
    }

    .menu-link {
        color: {{ $themeText }} !important;
    }

    .menu-link:hover,
    .menu-link.active {
        background: {{ $themeSubmenu }} !important;
        color: #fff !important;
    }

    .menu-link:hover .menu-title,
    .menu-link.active .menu-title,
    .menu-link:hover i,
    .menu-link.active i {
        color: #fff !important;
    }

    /* Header (topbar) */
    #kt_header, .header {
        background: {{ $themeHeader }} !important;
    }

    /* Footer */
    #kt_footer, .footer {
        background: {{ $themeFooter }} !important;
    }

    /* Buttons */
    .btn-primary {
        background: {{ $themeButton }} !important;
        border-color: transparent !important;
    }

    a {
        color: {{ $themeHover }};
    }

    a:hover {
        color: {{ $themeHover }};
        filter: brightness(0.85);
    }

    {!! get_option('custom_backend_css') !!} .div_one {
        position: relative;

    }

    .div_one::after {
        content: '';
        position: absolute;
        width: 2px;
        height: 45px;
        background-color: #fff;
        top: 10px;
        right: 0px;
    }

    .institute_and_year_div {
        background-color: #000;
        color: #fff;
        display: flex;
    }

    .institute_and_year_child_div {
        margin: 0;
        padding: 10px 5px;
        margin: 0;
        padding: 10px 5px;
    }

    .institute_div_heading {
        font-size: 12px;
        font-weight: 400;
        text-transform: uppercase;
        margin: 0;
        padding: 5px;
        font-size: 12px;
        font-weight: 400;
        text-transform: uppercase;
        margin: 0;
        padding: 5px;
    }

    .institute_div_span {
        padding: 0 5px;
        color: #29B2CA;
    }

    .divPaddingMargin {
        margin: 10px 0;
        padding: 0;
    }

    .institute_logo_img {
        width: 180px;
        height: 160px;
        border-radius: 5px;
        margin: 15px 0;
    }

    .institute_second_div_one {
        background-color: #00B09B !important;
        color: #fff !important;
        text-align: center;
    }

    .institute_second_div_two {
        background-color: #F06048 !important;
        color: #fff !important;
        text-align: center;
    }

    .institute_second_div_three {
        background-color: #1E7FFD !important;
        color: #fff !important;
        text-align: center;
    }

    .institute_second_div_four {
        background-color: #044BA5 !important;
        color: #fff !important;
        text-align: center;
    }

    .institute_second_div_one i,
    .institute_second_div_two i,
    .institute_second_div_three i,
    .institute_second_div_four i {
        font-size: 24px;
    }

    .institute_third_div img {
        max-width: 100%;
        max-height: 140px;
        border-radius: 5px;
        margin-top: 15px;
    }

    .institute_third_div .heading {
        margin: 0;
        padding: 0;
        margin: 10px 0;
    }

    .institute_third_div ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .institute_third_div ul li {
        margin-top: 5px;
    }

    .institute_third_div .right_div {
        background-color: #fff;
        width: 160px;
        height: 140px;
        border-radius: 5px;
        margin-top: 15px;
    }

    .institute_third_div .heading_four {
        padding-top: 30px;
        margin: 0;
        color: #000
    }

    .institute_third_div .heading_five {
        color: black;
        padding: 0;
    }

    .aside-custom-sidebar {
        background-color: #f4f3ef !important;
        box-shadow: none;
        border: none;
        padding: 0px 10px 0px 10px !important;
        margin-bottom: 0px !important;
    }

    /* Default styles */
    div.dt-container div.dt-length label {
        display: inline-block !important;
        float: left !important;
    }

    div.dt-container div.dt-search {
        display: inline-block !important;
        float: right !important;
    }

    /* Media query for screens less than or equal to 668px */
    @media (max-width: 668px) {
        div.dt-container div.dt-length label {
            float: none !important;
        }

        div.dt-container div.dt-search {
            float: none !important;
            margin-top: 10px;
        }
    }

    .modal-content {
        border: 1px solid var(--kt-modal-border-color) !important;
    }

    .select2-selection {
        height: 43px !important;
    }

    .card .card-header {
        padding: 0px !important;
        margin-bottom: 15px !important;
        min-height: 30px;
    }

    .card .card-header .card-title,
    .card .card-header .card-title .card-label {
        font-weight: 600 !important;
    }

    .swal2-popup .swal2-title {
        color: #fff !important;
    }

    @media (min-width: 300px) and (max-width: 667px) {
        .custom-margin {
            margin-top: 15px;
            text-align: center;
        }
    }

    .edit-icon svg path {
        fill: #007BFF;
        /* Blue color for edit icon */
    }

    .delete-icon svg path {
        fill: #FF0000;
        /* Red color for delete icon */
    }

    #User-table_wrapper .dt-length label {
        display: none !important;
    }

    .font_size_medium {
        font-size: 24px
    }

    .margin_top_24 {
        margin-top: 24px;
    }

    .icon_size {
        font-size: 62px;
    }

    .file_name {
        display: block;
        margin: 15px 0;
    }

    .span_m_p {
        display: block;
        margin: 15px 0;
    }

    .w_150 {
        width: 150px;
    }

    .frontend-visit-btn {
        height: 30px;
        min-width: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 2px;
        border-radius: 10px;
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        color: #fff !important;
        text-decoration: none;
        font-size: 18px;
        font-weight: 600;
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.25);
        transition: all 0.3s ease;
    }

    .frontend-visit-btn i {
        font-size: 18px;
    }

    /* MOBILE HIDE */

    @media (max-width: 676px) {
        .only_pc_device {
            display: none !important;
        }

    }
</style>

<script>
    // Fixes the sidebar visually "jumping" up or down when an accordion
    // menu item is opened or closed. This isn't the sidebar actually
    // scrolling to a new position - it's the natural side effect of the
    // list's height changing above/below whatever you clicked, while the
    // browser keeps the scroll container at the same pixel offset. This
    // keeps the item you clicked visually anchored in place instead.
    document.addEventListener('DOMContentLoaded', function () {
        var scrollContainer = document.getElementById('kt_app_sidebar_menu_wrapper');
        if (!scrollContainer) return;

        document.querySelectorAll('#kt_app_sidebar_menu [data-kt-menu-trigger="click"] > .menu-link').forEach(function (link) {
            link.addEventListener('click', function () {
                var clickedItem = link.closest('.menu-item');
                if (!clickedItem) return;

                var beforeTop = clickedItem.getBoundingClientRect().top;

                // The accordion's expand/collapse animates height via CSS
                // transition - wait for it to finish, then correct the
                // scroll position by exactly how much the clicked item
                // moved, so it stays where your eyes already are.
                setTimeout(function () {
                    var afterTop = clickedItem.getBoundingClientRect().top;
                    var delta = afterTop - beforeTop;
                    if (delta !== 0) {
                        scrollContainer.scrollTop += delta;
                    }
                }, 350);
            });
        });
    });
</script>