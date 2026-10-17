<?php

use Illuminate\Support\Facades\Route;

// The API has no web pages; the Next.js app is the website.
Route::get('/', fn () => ['name' => 'CourtKu API', 'docs' => '/docs']);
