<?php

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    // Logout
    Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

	// Searchbox on header
	Route::get('/search', 'SearchController@search')->name('searchbox');

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
    Route::get('/detect/datatbleJson', 'DetectController@datatbleJson')->name('detect.datatable');
    Route::get('/detect/create', 'DetectController@create')->name('detect.create');
    Route::get('/getMasterDocs', 'DetectController@getMasterDocs')->name('detect.master-docs');
    Route::get('/detect/result/{id}', 'DetectController@result')->name('detect.result');
    Route::post('/detect', 'DetectController@store')->name('detect.store');
});

