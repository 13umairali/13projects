$(function() {
    'use strict';
    tippy('.tip');
    $('#generate_key').on("click", function(e) {
        e.preventDefault();
        $.ajax({
            type: 'get',
            url: '/setup/getNewAppKey',
            success: function (data) {
                $('#app_key').val(data);

            }
        });
    });
});

$(function() {
    'use strict';
    $("form").attr('novalidate', 'novalidate');
    $(document).on('click', '#testdb', function(e) {
        $('#testdb').html('Testing... <i class="fa fa-spinner faa-spin animated "></i>');
        $('#testdb').removeClass('btn-success').removeClass('btn-danger').addClass('btn-dark');
        e.preventDefault();
        $.ajax({
            type: 'post',
            url: '/setup/testDB',
            data: $('#dbform').serialize(),
            success: function (data) {
                if(data.hasOwnProperty('Error'))
                {
                    $('#errormsg').html(data.Error);
                    $('#errormsg').addClass('danger').addClass('alert-danger');
                    $('#testdb').removeClass('btn-dark').addClass('btn-danger');
                    $('#testdb').html('Test Connection <i class="fa fa-times "></i>');
                    $('.next_step').removeClass('d-block').addClass('d-none');
                }else
                {
                    $('#errormsg').html(data.Success);
                    $('#errormsg').addClass('success').addClass('alert-success');
                    $('#testdb').removeClass('btn-dark').addClass('btn-success');
                    $('#testdb').html('Test Connection <i class="fa fa-check-circle-o "></i>');
                    $('.next_step').removeClass('d-none').addClass('d-block');
                }
               
                $('#errormsg').addClass('text-white').addClass('p-1');
            },
            statusCode: {
                500: function() {
                    $('#testdb').removeClass('btn-dark').addClass('btn-danger');
                    $('#testdb').html('Test Connection <i class="fa fa-times "></i>');
                    $('#errormsg').html("Coul not connect to database");
                    $('#errormsg').addClass('danger').addClass('alert-danger');
                    $('#errormsg').addClass('text-danger').addClass('p-1');
                }
            }
        });
    });
});

$(function() {
    'use strict';
    $('#lastStep').on("click", function(e) {
        $('.loader').removeClass('d-none').addClass('d-block');
        $('#content').removeClass('d-block').addClass('d-none');
    });
});


$(function() {
    'use strict';
    $('#lastStep').on("click", function(e) {
        $('.loader').removeClass('d-none').addClass('d-block');
        $('#content').removeClass('d-block').addClass('d-none');
    });
});

$(function() {
    'use strict';
    $('#update_db').on("click", function(e) {
        $('.loader').removeClass('d-none').addClass('d-block');
        $('#update_db').removeClass('d-block').addClass('d-none');
        // e.preventDefault();
        // $.ajax({
        //     type: 'post',
        //     url: '/update/lastStep',
        //     // headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        // });
    });
});
/* Copy-to-clipboard for the finish page credential rows */
$(function() {
    'use strict';
    function fallbackCopy(text, cb) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch (e) {}
        document.body.removeChild(ta);
        cb();
    }
    $(document).on('click', '.copy-btn', function() {
        var btn = $(this);
        var target = document.getElementById(btn.data('copy-target'));
        if (!target) return;
        var text = target.textContent.trim();
        var done = function() {
            var label = btn.find('span');
            var original = label.text();
            btn.addClass('copied');
            label.text('Copied');
            setTimeout(function() {
                btn.removeClass('copied');
                label.text(original);
            }, 1500);
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(function() { fallbackCopy(text, done); });
        } else {
            fallbackCopy(text, done);
        }
    });
});
