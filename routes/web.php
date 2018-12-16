<?php

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    // Logout
    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

    // Home
    Route::get('/', 'HomeController@index')->name('home');

    // Category
    Route::resource('/category', 'CategoryController');
    Route::get('/getJSONCategory', 'CategoryController@getJSON')->name('category.json');

    // Master Docs
    Route::resource('/all-documents', 'MasterDocsController');
    Route::get('/JSONDatatable', 'MasterDocsController@JSONDatatable')->name('all-documents.json');

    // Detect
    Route::get('/detect', 'DetectController@index')->name('detect.index');
    Route::get('/getMasterDocs', 'DetectController@getMasterDocs')->name('detect.master-docs');
    Route::post('/detecting', 'DetectController@upload')->name('detect.upload');
});

