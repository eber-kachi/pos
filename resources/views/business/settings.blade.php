@extends('layouts.app')
@section('title', __('business.business_settings'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">@lang('business.business_settings')</h1>
    <br>
    @include('layouts.partials.search_settings')
</section>

<!-- Main content -->
<section class="content business-settings-page">
{!! Form::open(['url' => action([\App\Http\Controllers\BusinessController::class, 'postBusinessSettings']), 'method' => 'post', 'id' => 'bussiness_edit_form',
           'files' => true ]) !!}
    <div class="row">
        <div class="col-xs-12">
       <!--  <pos-tab-container> -->
        {{-- <div class="col-xs-12 pos-tab-container"> --}}
        @component('components.widget', ['class' =>  'pos-tab-container'])
            <div class="business-settings-mobile-nav">
                <button type="button" class="btn btn-default tw-w-full tw-flex tw-items-center tw-justify-between" id="business_settings_menu_toggle" aria-expanded="false" aria-controls="business_settings_menu">
                    <span><i class="fa fa-bars tw-mr-2" aria-hidden="true"></i>Menú de secciones</span>
                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="tw-text-sm tw-font-bold tw-mt-2" id="business_settings_current_section">@lang('business.business')</div>
            </div>
            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu tw-rounded-lg" id="business_settings_menu">
                <div class="list-group">
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base active">@lang('business.business')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.tax') @show_tooltip(__('tooltip.business_tax'))</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.product')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('contact.contact')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.sale')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('sale.pos_sale')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.display_screen')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('purchase.purchases')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.payment')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.dashboard')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('business.system')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.prefixes')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.email_settings')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.sms_settings')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.reward_point_settings')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.modules')</a>
                    <a href="#" class="list-group-item text-center tw-font-bold tw-text-sm md:tw-text-base">@lang('lang_v1.custom_labels')</a>
                </div>
            </div>
            <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                <!-- tab 1 start -->
                @include('business.partials.settings_business')
                <!-- tab 1 end -->
                <!-- tab 2 start -->
                @include('business.partials.settings_tax')
                <!-- tab 2 end -->
                <!-- tab 3 start -->
                @include('business.partials.settings_product')

                @include('business.partials.settings_contact')
                <!-- tab 3 end -->
                <!-- tab 4 start -->
                @include('business.partials.settings_sales')
                @include('business.partials.settings_pos')
                @include('business.partials.settings_display_pos')
                <!-- tab 4 end -->
                <!-- tab 5 start -->
                @include('business.partials.settings_purchase')

                @include('business.partials.settings_payment')
                <!-- tab 5 end -->
                <!-- tab 6 start -->
                @include('business.partials.settings_dashboard')
                <!-- tab 6 end -->
                <!-- tab 7 start -->
                @include('business.partials.settings_system')
                <!-- tab 7 end -->
                <!-- tab 8 start -->
                @include('business.partials.settings_prefixes')
                <!-- tab 8 end -->
                <!-- tab 9 start -->
                @include('business.partials.settings_email')
                <!-- tab 9 end -->
                <!-- tab 10 start -->
                @include('business.partials.settings_sms')
                <!-- tab 10 end -->
                <!-- tab 11 start -->
                @include('business.partials.settings_reward_point')
                <!-- tab 11 end -->
                <!-- tab 12 start -->
                @include('business.partials.settings_modules')
                <!-- tab 12 end -->
                @include('business.partials.settings_custom_labels')
            </div>
        @endcomponent
        {{-- </div> --}}
        <!--  </pos-tab-container> -->
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 text-center">
            <button class="tw-dw-btn tw-dw-btn-error tw-dw-btn-lg tw-text-white" type="submit">@lang('business.update_settings')</button>
        </div>
    </div>
{!! Form::close() !!}
</section>
<!-- /.content -->
@stop
@section('css')
<style>
    .business-settings-mobile-nav {
        display: none;
    }

    @media (max-width: 767px) {
        .business-settings-mobile-nav {
            display: block;
            padding: 8px 8px 0;
        }

        .business-settings-page .pos-tab-menu {
            display: none !important;
        }

        .business-settings-page.settings-menu-open .pos-tab-menu {
            display: block !important;
            float: none !important;
            width: 100% !important;
            padding: 8px !important;
        }

        .business-settings-page.settings-menu-open .pos-tab-menu .list-group-item {
            width: 100%;
            text-align: left !important;
        }

        .business-settings-page.settings-menu-open .pos-tab {
            display: none;
        }

        .business-settings-page .pos-tab {
            float: none !important;
            width: 100% !important;
            padding: 0 8px !important;
        }

        .business-settings-page .pos-tab-content {
            padding: 12px 4px 4px !important;
        }

        .business-settings-page .sms-settings-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            column-gap: 12px;
            margin-right: 0;
            margin-left: 0;
        }

        .business-settings-page .sms-settings-row::before,
        .business-settings-page .sms-settings-row::after {
            display: none;
        }

        .business-settings-page .sms-settings-row > [class*="col-"] {
            float: none;
            width: auto;
            max-width: none;
            padding-right: 0;
            padding-left: 0;
        }

        .business-settings-page .sms-settings-row > .col-xs-3:only-of-type,
        .business-settings-page .sms-settings-row > .col-xs-12 {
            grid-column: 1 / -1;
        }

        .business-settings-page .sms-settings-row > .clearfix {
            display: none;
        }

        .business-settings-page .sms-settings-row > hr {
            grid-column: 1 / -1;
            width: 100%;
        }

        .business-settings-page .shipping-custom-field-group {
            display: block;
        }

        .business-settings-page .shipping-custom-field-group > .form-control {
            display: block;
            float: none;
            width: 100%;
            border-radius: 4px 4px 0 0;
        }

        .business-settings-page .shipping-custom-field-addon {
            display: flex !important;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            width: 100%;
            white-space: normal;
            border-radius: 0 0 4px 4px;
        }

        .business-settings-page .shipping-custom-field-addon label {
            display: block;
            margin: 0;
            line-height: 1.4;
        }
    }
</style>
@endsection

@section('javascript')
<script type="text/javascript">
    __page_leave_confirmation('#bussiness_edit_form');
    $(document).on('ifToggled', '#use_superadmin_settings', function() {
        if ($('#use_superadmin_settings').is(':checked')) {
            $('#toggle_visibility').addClass('hide');
            $('.test_email_btn').addClass('hide');
        } else {
            $('#toggle_visibility').removeClass('hide');
            $('.test_email_btn').removeClass('hide');
        }
    });

    $(document).ready(function(){
        var $settingsTabs = $('.business-settings-page .pos-tab-menu .list-group > a');
        var $settingsPage = $('.business-settings-page');
        var $menuToggle = $('#business_settings_menu_toggle');
        var $currentSection = $('#business_settings_current_section');

        $menuToggle.on('click', function() {
            var isOpen = $settingsPage.toggleClass('settings-menu-open').hasClass('settings-menu-open');
            $(this).attr('aria-expanded', isOpen ? 'true' : 'false');
        });

        $settingsTabs.on('click', function() {
            $currentSection.text($(this).text().trim());
            $settingsPage.removeClass('settings-menu-open');
            $menuToggle.attr('aria-expanded', 'false');
        });

    
        $('#test_email_btn').click( function() {
            var data = {
                mail_driver: $('#mail_driver').val(),
                mail_host: $('#mail_host').val(),
                mail_port: $('#mail_port').val(),
                mail_username: $('#mail_username').val(),
                mail_password: $('#mail_password').val(),
                mail_encryption: $('#mail_encryption').val(),
                mail_from_address: $('#mail_from_address').val(),
                mail_from_name: $('#mail_from_name').val(),
            };
            $.ajax({
                method: 'post',
                data: data,
                url: "{{ action([\App\Http\Controllers\BusinessController::class, 'testEmailConfiguration']) }}",
                dataType: 'json',
                success: function(result) {
                    if (result.success == true) {
                        swal({
                            text: result.msg,
                            icon: 'success'
                        });
                    } else {
                        swal({
                            text: result.msg,
                            icon: 'error'
                        });
                    }
                },
            });
        });

        $('#test_sms_btn').click( function() {
            var test_number = $('#test_number').val();
            if (test_number.trim() == '') {
                toastr.error('{{__("lang_v1.test_number_is_required")}}');
                $('#test_number').focus();

                return false;
            }

            var data = {
                url: $('#sms_settings_url').val(),
                send_to_param_name: $('#send_to_param_name').val(),
                msg_param_name: $('#msg_param_name').val(),
                request_method: $('#request_method').val(),
                param_1: $('#sms_settings_param_key1').val(),
                param_2: $('#sms_settings_param_key2').val(),
                param_3: $('#sms_settings_param_key3').val(),
                param_4: $('#sms_settings_param_key4').val(),
                param_5: $('#sms_settings_param_key5').val(),
                param_6: $('#sms_settings_param_key6').val(),
                param_7: $('#sms_settings_param_key7').val(),
                param_8: $('#sms_settings_param_key8').val(),
                param_9: $('#sms_settings_param_key9').val(),
                param_10: $('#sms_settings_param_key10').val(),

                param_val_1: $('#sms_settings_param_val1').val(),
                param_val_2: $('#sms_settings_param_val2').val(),
                param_val_3: $('#sms_settings_param_val3').val(),
                param_val_4: $('#sms_settings_param_val4').val(),
                param_val_5: $('#sms_settings_param_val5').val(),
                param_val_6: $('#sms_settings_param_val6').val(),
                param_val_7: $('#sms_settings_param_val7').val(),
                param_val_8: $('#sms_settings_param_val8').val(),
                param_val_9: $('#sms_settings_param_val9').val(),
                param_val_10: $('#sms_settings_param_val10').val(),
                test_number: test_number,

                header_1: $('#sms_settings_header_key1').val(),
                header_val_1: $('#sms_settings_header_val1').val(),
                header_2: $('#sms_settings_header_key2').val(),
                header_val_2: $('#sms_settings_header_val2').val(),
                header_3: $('#sms_settings_header_key3').val(),
                header_val_3: $('#sms_settings_header_val3').val(),
                data_parameter_type: $('#data_parameter_type').val(),
            };

            $.ajax({
                method: 'post',
                data: data,
                url: "{{ action([\App\Http\Controllers\BusinessController::class, 'testSmsConfiguration']) }}",
                dataType: 'json',
                success: function(result) {
                    if (result.success == true) {
                        swal({
                            text: result.msg,
                            icon: 'success'
                        });
                    } else {
                        swal({
                            text: result.msg,
                            icon: 'error'
                        });
                    }
                },
            });

        });

        $('select.custom_labels_products').change(function(){
            value = $(this).val();
            textarea = $(this).parents('div.custom_label_product_div').find('div.custom_label_product_dropdown');
            if(value == 'dropdown'){
                textarea.removeClass('hide');
            } else{
                textarea.addClass('hide');
            }
        })

        tinymce.init({
            selector: 'textarea#display_screen_heading',
            height: 250
        });

        $('.carousel_image').fileinput({
            showUpload: true,
            showPreview: true,
            browseLabel: LANG.file_browse_label,
            removeLabel: LANG.remove,
        });
    });
</script>
@endsection
