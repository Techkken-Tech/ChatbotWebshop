
@extends('facebookchatbot::shop.customers.account.index')

@section('page_title')
    {{ __('shop::app.customer.account.address.create.page-title') }}
@endsection

@section('page-detail-wrapper')
    <div class="account-head mb-15">
        <span class="back-icon"><a href="{{ route('customer.account.index') }}"><i class="icon icon-menu-back"></i></a></span>
        <span class="account-heading">{{ __('shop::app.customer.account.address.create.title') }}</span>
        <span></span>
    </div>

    {!! view_render_event('bagisto.shop.customers.account.address.create.before') !!}

        <form method="post" action="{{ route('facebookchatbot.customer.address.store') }}" @submit.prevent="onSubmit">

            <div class="account-table-content">
                @csrf

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.before') !!}

                <div class="control-group" :class="[errors.has('company_name') ? 'has-error' : '']">
                    <label for="company_name">{{ __('shop::app.customer.account.address.create.company_name') }}</label>
                    <input type="text" class="control" name="company_name" value="{{ old('company_name') }}" data-vv-as="&quot;{{ __('shop::app.customer.account.address.create.company_name') }}&quot;">
                    <span class="control-error" v-if="errors.has('company_name')" v-text="errors.first('company_name')"></span>
                </div>

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.company_name.after') !!}

                <div class="control-group" :class="[errors.has('first_name') ? 'has-error' : '']">
                    <label for="first_name" class="mandatory">{{ __('shop::app.customer.account.address.create.first_name') }}</label>
                    <input type="text" class="control" name="first_name"  v-validate="'required'" data-vv-as="&quot;{{ __('shop::app.customer.account.address.create.first_name') }}&quot;">
                    <span class="control-error" v-if="errors.has('first_name')" v-text="errors.first('first_name')"></span>
                </div>

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.first_name.after') !!}

                <div class="control-group" :class="[errors.has('last_name') ? 'has-error' : '']">
                    <label for="last_name" class="mandatory">{{ __('shop::app.customer.account.address.create.last_name') }}</label>
                    <input type="text" class="control" name="last_name" v-validate="'required'" data-vv-as="&quot;{{ __('shop::app.customer.account.address.create.last_name') }}&quot;">
                    <span class="control-error" v-if="errors.has('last_name')" v-text="errors.first('last_name')"></span>
                </div>

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.last_name.after') !!}

                <div class="control-group" :class="[errors.has('vat_id') ? 'has-error' : '']">
                    <label for="vat_id">{{ __('shop::app.customer.account.address.create.vat_id') }}
                        <span class="help-note">{{ __('shop::app.customer.account.address.create.vat_help_note') }}</span>
                    </label>
                    <input type="text" class="control" name="vat_id" value="{{ old('vat_id') }}" v-validate="" data-vv-as="&quot;{{ __('shop::app.customer.account.address.create.vat_id') }}&quot;">
                    <span class="control-error" v-if="errors.has('vat_id')" v-text="errors.first('vat_id')"></span>
                </div>

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.vat_id.after') !!}

                @php
                    $addresses = explode(PHP_EOL, (old('address1') ?? ''));
                @endphp

                <div class="control-group" :class="[errors.has('address1[]') ? 'has-error' : '']">
                    <label for="address_0" class="mandatory">{{ __('shop::app.customer.account.address.create.street-address') }}</label>
                    <input type="text" class="control" name="address1[]" id="address_0" value="{{ $addresses[0] ?: '' }}" v-validate="'required'" data-vv-as="&quot;{{ __('shop::app.customer.account.address.create.street-address') }}&quot;">
                    <span class="control-error" v-if="errors.has('address1[]')" v-text="errors.first('address1[]')"></span>
                </div>

                @if (core()->getConfigData('customer.settings.address.street_lines') && core()->getConfigData('customer.settings.address.street_lines') > 1)
                    @for ($i = 1; $i < core()->getConfigData('customer.settings.address.street_lines'); $i++)
                        <div class="control-group" style="margin-top: -25px;">
                            <input type="text" class="control" name="address1[{{ $i }}]" id="address_{{ $i }}" value="{{ $addresses[$i] ?? '' }}">
                        </div>
                    @endfor
                @endif

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.street-address.after') !!}

                @include ('shop::customers.account.address.country-state', ['countryCode' => old('country'), 'stateCode' => old('state')])

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.country-state.after') !!}

                <div class="control-group" :class="[errors.has('city') ? 'has-error' : '']">
                    <label for="city" class="mandatory">{{ __('shop::app.customer.account.address.create.city') }}</label>
                    <input type="text" class="control" name="city" value="{{ old('city') }}" v-validate="'required'" data-vv-as="&quot;{{ __('shop::app.customer.account.address.create.city') }}&quot;">
                    <span class="control-error" v-if="errors.has('city')" v-text="errors.first('city')"></span>
                </div>

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.city.after') !!}

                <div class="control-group" :class="[errors.has('postcode') ? 'has-error' : '']">
                    <label for="postcode" class="mandatory">{{ __('shop::app.customer.account.address.create.postcode') }}</label>
                    <input type="text" class="control" name="postcode" value="{{ old('postcode') }}" v-validate="'required'" data-vv-as="&quot;{{ __('shop::app.customer.account.address.create.postcode') }}&quot;">
                    <span class="control-error" v-if="errors.has('postcode')" v-text="errors.first('postcode')"></span>
                </div>

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.postcode.after') !!}

                <div class="control-group" :class="[errors.has('phone') ? 'has-error' : '']">
                    <label for="phone" class="mandatory">{{ __('shop::app.customer.account.address.create.phone') }}</label>
                    <input type="text" class="control" name="phone" value="{{ old('phone') }}" v-validate="'required'" data-vv-as="&quot;{{ __('shop::app.customer.account.address.create.phone') }}&quot;">
                    <span class="control-error" v-if="errors.has('phone')" v-text="errors.first('phone')"></span>
                </div>

                {!! view_render_event('bagisto.shop.customers.account.address.create_form_controls.after') !!}

                <div class="control-group d-flex">
                    <input type="checkbox" id="default_address" class="w-auto" name="default_address" {{ old('default_address') ? 'checked' : '' }}>

                    <label class="checkbox-view" for="default_address"></label>

                    {{ __('shop::app.customer.account.address.default-address') }}
                </div>

                <div class="button-group">
                    <button class="theme-btn" type="submit">
                        {{ __('shop::app.customer.account.address.create.submit') }}
                    </button>
                </div>
            </div>
        </form>

    {!! view_render_event('bagisto.shop.customers.account.address.create.after') !!}
@endsection

<script>
      var APP_ID = "283285947221513"; // the bot platform for workplace
      // var APP_ID = "853697284761471"; // the bot platform for messenger
      var psid = "unknown"; 
      
      // include facebook messenger extensions
      (function (d, s, id) {
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) { return; }
      js = d.createElement(s); js.id = id;
      js.src = "https://connect.facebook.net/en_US/messenger.Extensions.js";
      fjs.parentNode.insertBefore(js, fjs);
      }(document, "script", "Messenger"));
      
      // extensions are loaded
      window.extAsyncInit = function () {
      
        var isSupported = MessengerExtensions.isInExtension();
      
        // get the user context from the extensions
        MessengerExtensions.getContext(APP_ID,
            function success(result){
                console.log(result);
                window.testing = result;
                // get the page scope id for the person
                psid = result.psid;
                if (psid === "") {
                    psid = "ERROR, blank from fb";
                }
                $("#psid").html(psid);
                alert("PSID found: '"+psid+"'");
            },
            function error(result){
                alert("Messenger Extensions are not supported in this webview. Code: " + JSON.stringify(result));
            }
        );
      };
      
    //   $(function() {
    //     // when form is submitted
    //     $("#example_form").submit(function(e) {
    //         e.preventDefault();
    //         var text = $("#text").val();
    //         if (text) {
      
    //             // post it to the server
    //             $.post( "post.php", {
    //                 psid: psid,
    //                 text: text,
    //                 switch: $("#switch").is(":checked")
    //             }, function(data) {
    //                 if (data.success) {
    //                     alert("Success, the form has been submitted")
                        
    //                     // close the webview now
    //                     closeWebview();
    //                 }
    //                 else {
    //                     alert(data.error);
    //                 }   
    //             }, "json")
    //             .fail(function(e) {
    //                 alert( "There was an error submitting your form, please try again later" );
    //                 alert(e.responseText);
    //                 console.log(e);
    //             })
    //             .always(function() {
      
    //             });
      
    //             return true;
    //         } else {
    //             return false;
    //         }
    //     })
    //   })
      
      // There is an easy method to close the webview from javascript
      function closeWebview() {
        MessengerExtensions.requestCloseBrowser(function success() {
            // webview closed
        }, function error(err) {
        });
      };
      
    </script>