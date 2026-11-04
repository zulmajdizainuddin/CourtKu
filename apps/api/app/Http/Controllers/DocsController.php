<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The API documentation at /docs (ADR-035), written by Scribe (`composer docs`) into
 * resources/views/scribe, public/vendor/scribe and resources/docs. Scribe itself is only a
 * development tool, so CourtKu serves the pages itself.
 */
class DocsController extends Controller
{
    /**
     * The docs page needs its own scripts and styles, and a few from public CDNs, which the API's
     * strict rule (SecurityHeaders: nothing may load) would block. Still no framing, forms or plugins.
     */
    private const PAGE_POLICY = "default-src 'none'; "
        ."script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com; "
        ."style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://unpkg.com; "
        ."font-src https://fonts.gstatic.com; img-src 'self' data:; "
        ."base-uri 'none'; form-action 'none'; frame-ancestors 'none'";

    public function page(): Response
    {
        return response()->view('scribe.index')->header('Content-Security-Policy', self::PAGE_POLICY);
    }

    /** The Postman collection, to try the API in Postman. */
    public function postman(): BinaryFileResponse
    {
        return response()->file(resource_path('docs/collection.json'), ['Content-Type' => 'application/json']);
    }

    /** The OpenAPI description, for tools that read one. */
    public function openapi(): BinaryFileResponse
    {
        return response()->file(resource_path('docs/openapi.yaml'), ['Content-Type' => 'application/yaml']);
    }
}
