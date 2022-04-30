<?php

Route::group([
        'prefix'        => 'admin/facebookchatbot',
        'middleware'    => ['web', 'admin']
    ], function () {

        Route::get('', 'Techkken\FacebookChatbot\Http\Controllers\Admin\FacebookChatbotController@index')->defaults('_config', [
            'view' => 'facebookchatbot::admin.index',
        ])->name('admin.facebookchatbot.index');

});