<?php

/*Две страницы - ферма и отчеты*/

Route::get('/', 'SheepController@welcome');
Route::get('/report', 'ReportController@show');