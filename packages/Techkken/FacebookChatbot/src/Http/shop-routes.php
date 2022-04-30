<?php



Route::match(['get', 'post'], '/facebookchatbot', 'Techkken\FacebookChatbot\Http\Controllers\Shop\FacebookChatbotController@index');

Route::group([
    'prefix'     => 'facebookchatbot',
    'middleware' => ['web', 'theme', 'locale', 'currency']
], function () {


    Route::get('/addresses/create', 'Techkken\FacebookChatbot\Http\Controllers\Shop\FacebookChatbotAddressController@create')->defaults('_config', [
        'view' => 'facebookchatbot::shop.customers.account.address.create'
    ])->name('facebookchatbot.customer.address.create');
    
    Route::post('/addresses/create', 'Techkken\FacebookChatbot\Http\Controllers\Shop\FacebookChatbotAddressController@store')->defaults('_config', [
        'redirect' => 'customer.address.index'
    ])->name('facebookchatbot.customer.address.store');
    //Change redirect



});