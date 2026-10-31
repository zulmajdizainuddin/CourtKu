<?php

namespace App\Http\Controllers\V1\Admin;

use App\Http\Controllers\Controller;
use RuntimeException;

/**
 * POST /api/v1/admin/test-error: fails on purpose, so the admin can check that error alerts work on the
 * live site (docs/deploy.md, "Error alerts"). The error should show up in Sentry within a minute.
 * Admin only, behind two-factor login, like every admin route.
 */
class TestErrorController extends Controller
{
    public function __invoke(): never
    {
        throw new RuntimeException('Test error from the CourtKu admin page. If you can read this in Sentry, error alerts work.');
    }
}
