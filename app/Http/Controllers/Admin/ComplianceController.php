<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Compliance report publishing moved to the Tech Compliance Group's Compliance
 * Reports service. This controller now only shows the page that says where to
 * publish; it reads nothing and writes nothing.
 *
 * The upload, index and page generation code that used to live here is in the
 * repository history, before the commit that replaced this file.
 */
class ComplianceController extends Controller
{
    /**
     * Show the notice page. The name stays from the old upload form so that the
     * route line keeps working. The page needs no data, so it no longer fetches
     * the site's index.json first, and it cannot fail when that fetch does.
     */
    public function showForm()
    {
        return view('admin.upload.compliance');
    }
}
