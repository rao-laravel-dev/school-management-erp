{{-- Create/Edit dono ka common JS (teacher form jaisa). <script> ke andar include hota hai --}}

    // Pehle error wala field: #moreDetails kholo, scroll + focus (page load aur AJAX 422 dono)
    function focusFirstError() {
        let firstError = $('.is-invalid').first();
        if (!firstError.length) return;
        let scrollToError = function() {
            $('html, body').animate({ scrollTop: Math.max(firstError.offset().top - 120, 0) }, 300);
            firstError.trigger('focus');
        };
        let errorPanel = firstError.closest('.collapse');
        if (errorPanel.length && !errorPanel.hasClass('show')) {
            errorPanel.one('shown.bs.collapse', scrollToError);
            bootstrap.Collapse.getOrCreateInstance(errorPanel[0]).show();
        } else {
            scrollToError();
        }
    }

    // Image Preview Function
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#imagePreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    $(document).ready(function() {
        // Real-time error removal on input / change
        $(document).on('input change', '.is-invalid', function() {
            let field = $(this);
            field.removeClass('is-invalid');
            field.nextAll('.invalid-feedback').first().remove();
            field.closest('.input-group').nextAll('.invalid-feedback').first().remove();
        });
        // Radio (bank_type) ka error group ke end par hota hai
        $(document).on('change', 'input[name="bank_type"]', function() {
            $('#bank_type_group .invalid-feedback').remove();
        });

        $('#librarian_id_field').css({
            'color': '#00bc2f',
            'font-weight': 'bold',
            'font-size': '15px'
        });

        // Bank Type & Wallet Toggle Logic
        function handleBankTypeChange() {
            var selectedType = $('input[name="bank_type"]:checked').val();
            if (selectedType === 'microfinance_wallet') {
                $('.commercial-field').slideUp();
                $('#acc_no_label').text('Mobile / Wallet Number *');
            } else {
                $('.commercial-field').slideDown();
                $('#acc_no_label').text('Bank Account Number *');
            }
        }
        handleBankTypeChange();
        $('input[name="bank_type"]').change(handleBankTypeChange);

        // Password eye toggle
        $('.toggle-password').on('click', function() {
            var target = $('#' + $(this).data('target'));
            var icon = $(this).find('i');

            if (target.attr('type') === 'password') {
                target.attr('type', 'text');
                icon.removeClass('bx-hide').addClass('bx-show');
            } else {
                target.attr('type', 'password');
                icon.removeClass('bx-show').addClass('bx-hide');
            }
        });
    });
