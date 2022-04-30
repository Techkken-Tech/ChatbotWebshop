@extends('facebookchatbot::shop.layouts.master')

@section('content-wrapper')
    <div class="account-content row no-margin">
        <div class="account-layout right mt10">
            @yield('page-detail-wrapper')
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('vendor/webkul/ui/assets/js/ui.js') }}"></script>
@endpush