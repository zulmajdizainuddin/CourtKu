<?php

use App\Http\Controllers\DocsController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

// The API has no web pages; the Next.js app is the website.
Route::get('/', fn () => ['name' => 'CourtKu API', 'docs' => '/docs']);

// The API documentation (ADR-035). The names are the ones Scribe's page links to.
// No session: the docs need no login and no database, so they still open when the database is off.
Route::withoutMiddleware([StartSession::class, ShareErrorsFromSession::class, PreventRequestForgery::class])->group(function () {
    Route::get('/docs', [DocsController::class, 'page'])->name('scribe');
    Route::get('/docs.postman', [DocsController::class, 'postman'])->name('scribe.postman');
    Route::get('/docs.openapi', [DocsController::class, 'openapi'])->name('scribe.openapi');
});
