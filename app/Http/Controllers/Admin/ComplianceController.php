<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ComplianceController handles compliance upload, navigation file management,
 * and integrates optimal UX for generating missing _index.md files.
 */
class ComplianceController extends Controller
{
    /**
     * Show the compliance upload form with populated dropdowns.
     */
    public function showForm()
    {
        $data = $this->GetDropDownContent();
        $DropDownContent = [];
        $current_child_products = []; // Always empty on initial load

        foreach ($data as $family) {
            $DropDownContent[] = [
                'text' => $family['text'],
                'url'  => $family['url'],
            ];
        }

        return view('admin.upload.compliance', compact('DropDownContent', 'current_child_products'));
    }

    /**
     * Determines the current venture prefix based on the Hugo site URL.
     *
     * This venture prefix is used to:
     * - Generate unique markdown slugs like 'aspose-cloud-words-net'
     * - Load the correct venture-specific template (e.g. 'aspose-cloud-net-compliance-template.md')
     * - Construct accurate forum links (e.g. 'https://forum.aspose.cloud.com')
     * - Resolve third-party license files in venture-specific paths
     *
     * Detection priority:
     *   1. Use the 'hugositeurl' field from DB (AmazonS3Setting) if present
     *   2. Fall back to 'HUGOSITEURL' from .env if DB field is missing
     *
     * Returns:
     *   One of the predefined venture prefixes like:
     *     - 'aspose'
     *     - 'aspose-cloud'
     *     - 'aspose-app'
     *     - 'groupdocs'
     *     - 'groupdocs-cloud'
     *     - 'conholdate'
     *
     * Throws:
     *   RuntimeException if the hostname doesn't match a known venture
     */
    private function getVenturePrefix(): string
    {
        // Step 1: Load the Hugo site URL from DB or fallback to .env
        $settings = \App\Models\AmazonS3Setting::where('id', 1)->first();

        $host = $settings && $settings->hugositeurl
            ? parse_url($settings->hugositeurl, PHP_URL_HOST)
            : parse_url(env('HUGOSITEURL'), PHP_URL_HOST);

        $host = strtolower($host); // Normalize for safe comparisons

        // Step 2: Define exact mapping from hostnames to venture prefixes
        $map = [
            'releases.aspose.com'        => 'aspose',
            'releases.aspose.cloud'      => 'aspose-cloud',
            'releases.aspose.app'        => 'aspose-app',
            'releases.aspose.ai'         => 'aspose-ai',
            'releases.aspose.net'         => 'aspose-net',
            'releases.aspose.org'         => 'aspose-org',

            'releases.groupdocs.com'     => 'groupdocs',
            'releases.groupdocs.cloud'   => 'groupdocs-cloud',
            'releases.groupdocs.app'   => 'groupdocs-app',
            'releases.groupdocs.ai'   => 'groupdocs-ai',

            'releases.conholdate.com'  => 'conholdate',
            'releases.conholdate.cloud'  => 'conholdate-cloud',
        ];

        // Step 3: Return the corresponding venture prefix if matched
        if (isset($map[$host])) {
            return $map[$host];
        }

        // Step 4: Fail loudly if unknown host — avoids generating invalid slugs/paths
        throw new \RuntimeException("Unable to determine venture prefix for host: {$host}. Please check DB or .env HUGOSITEURL.");
    }




    /**
     * Handle POST form submission for compliance upload.
     * Ensures all files match the selected product/version using canonical product/platform slugs.
     * Dynamically adapts to the venture (aspose, aspose-cloud, groupdocs-cloud, etc.)
     * based on the detected Hugo site URL.
     *
     * @param Request $request HTTP POST request containing product, version, year, files, and sections.
     * @return \Illuminate\Http\RedirectResponse Redirects back with success or error messages.
     */
    public function uploadComplianceAPI(Request $request)
    {
        // 1. Validate form inputs: product, version, year, and file presence
        $request->validate([
            'product'   => 'required',
            'version'   => 'required',
            'year'      => 'required',
            'files.*'   => 'required|file',
        ]);

        // 2. Extract the 'product' value (could be a slug or full URL)
        $rawProduct = $request->input('product');
        $product = Str::startsWith($rawProduct, 'http') ?
            trim(parse_url($rawProduct, PHP_URL_PATH), '/') :
            trim($rawProduct, '/');

        // 3. Clean up version and year strings
        $version = trim($request->input('version'), '/');
        $year    = trim($request->input('year'), '/');

        // 4. Read checkbox values (SBOM, Security, License)
        $selectedSections = $request->input('sections', []);

        // 5. Parse product family (e.g. 'words') from product string
        $productFamilySlug = $this->getProductFamilyFromUrl($product);

        // 6. Parse platform (e.g. 'java', 'net-core') from product string
        $platformSlug = $this->getPlatformSlugFromUrl($product);

        // 7. Dynamically determine the venture prefix (e.g. aspose, aspose-cloud, groupdocs)
        $venturePrefix = $this->getVenturePrefix();

        // 8. Compose the expected product identifier used in filenames
        // Example: groupdocs-cloud-words-java
        $expectedProductIdentifier = "{$venturePrefix}-{$productFamilySlug}-{$platformSlug}";

        // 9. Define S3 folder structure for compliance markdown
        $familyFolder    = "compliance-reports/{$productFamilySlug}/{$platformSlug}/";
        $yearFolder      = $familyFolder . "{$year}/";
        $familyIndexPath = $familyFolder . '_index.md';
        $yearIndexPath   = $yearFolder . '_index.md';

        // 10. Ensure navigation _index.md files exist or create them if folders are missing
        $missingIndexes = [];
        $familyExists = Storage::exists($familyFolder);
        $yearExists   = Storage::exists($yearFolder);

        if (!$familyExists || !$yearExists) {
            // Auto-create _index.md files if missing
            $this->ensureComplianceIndexes($productFamilySlug, $platformSlug, $year, Auth::user()->email ?? null);
        } else {
            // Otherwise, record any missing _index.md files and block upload
            if (!Storage::exists($familyIndexPath)) $missingIndexes[] = $familyIndexPath;
            if (!Storage::exists($yearIndexPath))   $missingIndexes[] = $yearIndexPath;
        }

        // 11. Prevent upload if navigation files are missing
        if (!empty($missingIndexes)) {
            return redirect()->back()
                ->with('error', 'Required _index.md files are missing in existing S3 folders: ' . implode(', ', $missingIndexes) .
                    '. Please generate them first using the "Generate _index.md" button below the form.')
                ->with('show_generate_index_button', true);
        }

        // 12. Compose the full S3 destination path
        $path = "compliance-reports/{$productFamilySlug}/{$platformSlug}/{$year}/{$version}/";

        // 13. Load bucket name from Laravel config (fallback to .env works too)
        $bucket = config('filesystems.disks.s3.bucket');

        // 14. Ensure files are present
        if (!$request->hasFile('files')) {
            return redirect()->back()->with('error', 'No files received');
        }

        // 15. Validate each uploaded filename against expected product and version
        foreach ($request->file('files') as $file) {
            $fileName = strtolower($file->getClientOriginalName());

            // File must match both expected slug and version (dot or dash format)
            if (
                !Str::contains($fileName, $expectedProductIdentifier) ||
                (!Str::contains($fileName, $version) && !Str::contains($fileName, str_replace('.', '-', $version)))
            ) {
                $expectedSlug = "{$productFamilySlug}-{$platformSlug}";
                $exampleFileName = "{$venturePrefix}-{$expectedSlug}-{$version}_cwe-top-25-2024.htm";

                return redirect()->back()
                    ->with('error', "All uploaded files must match the selected product slug [{$expectedSlug}] and version [{$version}]. File '{$file->getClientOriginalName()}' does not match. Example of a valid filename: '{$exampleFileName}'. Please check your files and try again.");
            }
        }

        // 16. Upload files to S3 with user attribution as metadata
        $uploadedFiles = [];
        $s3Client = Storage::disk('s3')->getDriver()->getAdapter()->getClient();
        foreach ($request->file('files') as $file) {
            if ($file->isValid()) {
                $filename = $file->getClientOriginalName();
                $key = $path . $filename;
                $stream = fopen($file->getRealPath(), 'r');
                $s3Client->putObject([
                    'Bucket'      => $bucket,
                    'Key'         => $key,
                    'Body'        => $stream,
                    'ContentType' => $file->getMimeType(),
                    'Metadata'    => [
                        'uploader-email' => Auth::user()->email,
                        'uploader-name'  => Auth::user()->name,
                    ],
                ]);
                $uploadedFiles[] = $filename;
            }
        }

        // 17. Generate the final markdown file from uploaded reports and store in S3
        $this->saveComplianceMarkdown(
            $product,
            $version,
            $year,
            $selectedSections,
            Auth::user()->email
        );

        // 18. Redirect to the form with success details
        return redirect()->back()
            ->with('success', "Compliance files uploaded successfully.<br><b>Uploaded to:</b> <code>s3://{$bucket}/{$path}</code>")
            ->with('uploaded_files', $uploadedFiles)
            ->with('uploaded_s3_path', $path)
            ->with('uploaded_s3_bucket', $bucket);
    }



    /**
     * Extracts the product family slug from a product URL.
     * E.g., "words/python-net" returns "words", "cells/python-net" returns "cells".
     *
     * @param string $productUrl e.g. "words/python-net"
     * @return string product family slug (first URL segment)
     */
    private function getProductFamilyFromUrl(string $productUrl): string
    {
        $segments = explode('/', $productUrl);
        return strtolower($segments[0] ?? '');
    }

    /**
     * Extracts the platform slug from a product URL.
     * Takes all URL segments after the first (product family) joined by hyphens.
     * E.g.:
     * - "words/python-net" → "python-net"
     * - "cells/python-net" → "python-net"
     * - "pdf/pythonnet"    → "pythonnet"
     *
     * @param string $productUrl e.g. "words/python-net"
     * @return string platform slug (all but first segment joined by dash)
     */
    private function getPlatformSlugFromUrl(string $productUrl): string
    {
        $segments = explode('/', $productUrl);
        if (count($segments) <= 1) {
            return '';  // No platform segment found
        }
        $platformSegments = array_slice($segments, 1);
        return strtolower(implode('-', $platformSegments));
    }

    /**
     * Normalize product title to the short slug used in third-party license filenames.
     * - Removes filler words like 'for', 'via'
     * - Converts known product/platform terms into canonical forms:
     *   * "node js", "node-js" => "nodejs"
     *   * "c++" => "cpp"
     * - Replaces spaces, dots, underscores with dashes
     * - Collapses multiple dashes to single
     * - Trims trailing dashes
     *
     * @param string $productTitle
     * @return string
     */
    private function getThirdPartyLicenseSlug(string $productTitle): string
    {
        $slug = strtolower($productTitle);

        \Log::info("Initial slug: {$slug}");

        // Remove filler words 'for' and 'via'
        $slug = preg_replace('/\b(for|via)\b/', '', $slug);
        \Log::info("After removing filler words: {$slug}");

        // Normalize nodejs variants: nodejs, node-js, node_js, node.js
        $slug = preg_replace('/node[\-_.]?js/', 'nodejs', $slug);
        \Log::info("After nodejs normalization: {$slug}");

        // Normalize c++ to cpp
        $slug = preg_replace('/c\+\+/', 'cpp', $slug);
        \Log::info("After c++ normalization: {$slug}");

        // Replace dots, spaces, underscores with dash
        $slug = preg_replace('/[.\s_]+/', '-', $slug);
        \Log::info("After replacing spaces/dots/underscores: {$slug}");

        // Collapse multiple dashes
        $slug = preg_replace('/-+/', '-', $slug);
        \Log::info("After collapsing dashes: {$slug}");

        // Trim dashes
        $slug = trim($slug, '-');
        \Log::info("After trimming dashes: {$slug}");

        return $slug;
    }







    /**
     * Normalize a product name for compliance file naming convention.
     * e.g., "Aspose.Note for .NET" → "aspose-note-for-net"
     */
    private function normalizeProductName($productName)
    {
        $p = strtolower($productName);
        $p = str_replace(['(', ')'], '', $p);
        $p = preg_replace('/[ .]+/', '-', $p);      // spaces or dots → dash
        $p = preg_replace('/-+/', '-', $p);         // multiple dashes → single dash
        $p = trim($p, '-');
        return $p;
    }

    /**
     * AJAX endpoint for "Generate _index.md" button.
     * Can be called on demand if user is blocked by missing navigation files.
     */
    public function ajaxGenerateIndexes(Request $request)
    {
        // Normalize and parse product
        $productInput = trim($request->input('product'), '/');
        $year = trim($request->input('year'), '/');
        if (Str::startsWith($productInput, 'http')) {
            $parsed = parse_url($productInput, PHP_URL_PATH);
            $product = trim($parsed, '/');
        } else {
            $product = $productInput;
        }

        if (!$product || !$year || strpos($product, '/') === false) {
            return response()->json(['success' => false, 'message' => 'Invalid product or year.'], 400);
        }
        [$productSlug, $platform] = explode('/', $product);

        // Generate navigation files if needed
        $this->ensureComplianceIndexes($productSlug, $platform, $year, Auth::user()->email ?? null);

        return response()->json(['success' => true, 'message' => '_index.md files generated successfully.']);
    }

    /**
     * AJAX endpoint for checking if required compliance files exist for the selected product and venture.
     * Verifies:
     * - Existence of compliance template
     * - Existence of third-party license file (if selected)
     * 
     * Supports dynamic ventures like:
     * - aspose, aspose-cloud, aspose-ai, groupdocs, groupdocs-cloud, etc.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajaxCheckComplianceRequirements(Request $request)
    {
        // 1. Normalize product string (slug or full URL)
        $rawProduct = $request->input('product');
        $product = Str::startsWith($rawProduct, 'http') ?
            trim(parse_url($rawProduct, PHP_URL_PATH), '/') :
            trim($rawProduct, '/');

        // 2. Validate product structure: must contain platform part (e.g. 'words/java')
        if (!$product || strpos($product, '/') === false) {
            return response()->json([
                'templateExists' => false,
                'licenseExists' => false,
                'error' => 'Platform segment missing from product value. Please select a valid product.',
                'expectedTemplateFile' => null,
                'expectedLicenseFile' => null
            ], 400);
        }

        // 3. Get selected checkbox sections (e.g., license, sbom)
        $sections = $request->input('sections', []);

        // 4. Split into product family and platform (defensively)
        $productParts = explode('/', $product);
        $productFamilySlug = $productParts[0];
        $platform = $productParts[1] ?? '';

        if (empty($platform)) {
            return response()->json([
                'templateExists' => false,
                'licenseExists' => false,
                'expectedTemplateFile' => null,
                'expectedLicenseFile' => null,
                'error' => 'Platform segment missing from product value. Please select a valid product.'
            ], 400);
        }

        // 5. Dynamically detect the venture prefix from current host
        $venturePrefix = $this->getVenturePrefix(); // e.g. aspose, groupdocs-cloud

        // 6. Build expected template file path based on venture and platform
        // Example: compliance-reports/compliance-reports-templates/groupdocs-cloud-java-compliance-template.md
        $templateKey = "compliance-reports/compliance-reports-templates/{$venturePrefix}-{$platform}-compliance-template.md";
        $templateExists = Storage::exists($templateKey);

        // 7. Conditionally build license file path (only if "license" checkbox selected)
        $licenseKey = null;
        $licenseExists = true;
        if (in_array('license', $sections)) {
            // Example: compliance-reports/third-party-licenses/java/third-party-licenses-groupdocs-cloud-words-java.pdf
            $licenseKey = "compliance-reports/third-party-licenses/{$platform}/third-party-licenses-{$venturePrefix}-{$productFamilySlug}-{$platform}.pdf";
            $licenseExists = Storage::exists($licenseKey);
        }

        // 8. Return final result to frontend including file paths and statuses
        return response()->json([
            'templateExists'        => $templateExists,
            'licenseExists'         => $licenseExists,
            'expectedTemplateFile'  => $templateKey,
            'expectedLicenseFile'   => $licenseKey,
        ]);
    }



    // --- Helper and navigation functions (with comments) ---

    /**
     * Loads dropdown content (mock-index.json; replace with live source if needed)
     */
    private function GetDropDownContent()
    {
        $amazon_s3_settings = \App\Models\AmazonS3Setting::where('id', 1)->first();
        $hugositeurl = $amazon_s3_settings && $amazon_s3_settings->hugositeurl
            ? rtrim($amazon_s3_settings->hugositeurl, '/')
            : rtrim(env('HUGOSITEURL'), '/');

        if (!$hugositeurl) {
            abort(500, 'HUGOSITEURL is not configured in database or .env.');
        }

        $indexJsonUrl = $hugositeurl . '/index.json?Return_content=' . time();

        $json = @file_get_contents($indexJsonUrl);
        if ($json === false) {
            abort(500, 'Could not load index.json from ' . $indexJsonUrl);
        }

        $data = json_decode($json, true);
        if (!$data) {
            abort(500, 'Malformed index.json!');
        }

        return $data;
    }


    /**
     * Returns ALL child nodes (products) under a given family (for compliance dropdown).
     */
    public function getchildnodesforcompliance(Request $request)
    {
        $DropDownContent = $this->GetDropDownContent();
        $familyUrl = $request->id;
        $result = [];
        foreach ($DropDownContent as $family) {
            if ($family['url'] === $familyUrl && !empty($family['nodes'])) {
                foreach ($family['nodes'] as $child) {
                    $result[$child['text']] = $child['url'];
                }
                break;
            }
        }
        return $result;
    }

    /**
     * Looks up forum slug from product URL for support links.
     */
    private function getForumSlugFromUrl($productUrl)
    {
        $segments = explode('/', $productUrl);
        return $segments[0] ?? 'total';
    }


    /**
     * Resolves readable product name from dropdown content.
     */
    private function getProductTitleFromUrl($productUrl)
    {
        $data = $this->GetDropDownContent();
        foreach ($data as $family) {
            if (!empty($family['nodes'])) {
                foreach ($family['nodes'] as $child) {
                    $relative = trim(parse_url($child['url'], PHP_URL_PATH), '/');
                    if ($relative === $productUrl) {
                        return $child['text'];
                    }
                }
            }
        }
        return 'Unknown Product';
    }

    /**
     * Creates navigation _index.md files at the product and year folder levels,
     * and injects uploader attribution into YAML for Lambda/GitHub commits.
     *
     * @param string $productSlug E.g., 'total'
     * @param string $platform    E.g., 'java'
     * @param string $year        E.g., '2025'
     * @param string|null $uploaderEmail (Optional) The email to embed for attribution
     */
    private function ensureComplianceIndexes($productSlug, $platform, $year, $uploaderEmail = null)
    {
        // Build the folder paths for the product and the specific year
        $familyPath = "compliance-reports/{$productSlug}/{$platform}/";
        $yearPath   = $familyPath . "{$year}/";

        // Get product name for pretty front matter
        $productName = $this->getProductTitleFromUrl("{$productSlug}/{$platform}");
        $familyWeight = 99;
        $yearWeight   = $this->getYearWeight($year);

        // --- Product family-level _index.md ---
        $familyIndexPath = $familyPath . '_index.md';
        if (!Storage::exists($familyIndexPath)) {
            // Start YAML front matter block
            $content = "---\n";
            $content .= "id: \"compliance-reports\"\n";
            $content .= "linktitle: \"Compliance Reports\"\n";
            $content .= "title: \"Compliance Reports\"\n";
            $content .= "productName: \"{$productName}\"\n";
            $content .= "weight: {$familyWeight}\n";
            $content .= "description: \"Explore {$productName} compliance reports featuring SonarQube security analysis, SBOMs in CycloneDX and SPDX formats, and vulnerability assessments based on CWE Top 25 and OWASP Top 10—designed to support secure {$platform} development and regulatory transparency.\"\n";
            $content .= "type: \"repository\"\n";
            $content .= "layout: \"releases\"\n";
            $content .= "hideChildren: false\n";
            $content .= "toc: false\n";
            $content .= "family_listing_page_title: \"Compliance Reports\"\n";
            // Inject uploader attribution if available (for Lambda commit message)
            if ($uploaderEmail) {
                $content .= "committed_by: \"{$uploaderEmail}\"\n";
            }
            // End YAML block and add newline
            $content .= "---\n\n";
            // Write the file to S3/local storage
            Storage::put($familyIndexPath, $content);
        }

        // --- Year-level _index.md ---
        $yearIndexPath = $yearPath . '_index.md';
        if (!Storage::exists($yearIndexPath)) {
            // Start YAML front matter block
            $content = "---\n";
            $content .= "id: \"compliance-reports-{$year}\"\n";
            $content .= "linktitle: \"{$year}\"\n";
            $content .= "title: \"Compliance Reports - {$year}\"\n";
            $content .= "productName: \"{$productName}\"\n";
            $content .= "weight: {$yearWeight}\n";
            $content .= "description: \"Explore {$productName} compliance reports for the year {$year}, featuring SonarQube security analysis, SBOMs in CycloneDX and SPDX formats, and vulnerability assessments based on CWE Top 25 and OWASP Top 10—designed to support secure {$platform} development and regulatory transparency.\"\n";
            $content .= "type: \"repository\"\n";
            $content .= "layout: \"releases\"\n";
            $content .= "hideChildren: false\n";
            $content .= "toc: false\n";
            $content .= "family_listing_page_title: \"Compliance Reports - {$year}\"\n";
            // Inject uploader attribution if available
            if ($uploaderEmail) {
                $content .= "committed_by: \"{$uploaderEmail}\"\n";
            }
            // End YAML block and add newline
            $content .= "---\n\n";
            Storage::put($yearIndexPath, $content);
        }
    }



    /**
     * Returns Hugo navigation weight (newer years have lower weight).
     */
    private function getYearWeight($year)
    {
        $baseYear = 2023;
        $baseWeight = 99;
        $delta = (int)$baseYear - (int)$year;
        return $baseWeight + $delta;
    }



    /**
     * Loads a template markdown file from S3 and replaces placeholder variables.
     * @param string $templateKey S3 key, e.g. "compliance-reports/compliance-reports-templates/aspose-total-net-compliance-template.md"
     * @param array $vars Associative array of placeholder => value
     * @return string Populated markdown content, or empty string if error.
     */
    private function loadAndPopulateTemplateFromS3($templateKey, $vars = [])
    {
        if (!Storage::exists($templateKey)) {
            return '';
        }
        $template = Storage::get($templateKey);

        // Simple variable replacement, e.g. {{product}}, {{version}}, etc.
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }



    /**
     * Generates a compliance markdown (.md) page for Hugo based on uploaded files.
     * Supports multi-venture setup by resolving venture prefix dynamically.
     *
     * @param string $product E.g. 'words/net'
     * @param string $version E.g. '25.6'
     * @param string $year E.g. '2025'
     * @param array $sections Checkbox input from UI (e.g. ['license'])
     * @param string|null $uploaderEmail Optional uploader email for attribution
     */
    private function saveComplianceMarkdown($product, $version, $year, $sections = [], $uploaderEmail = null)
    {
        // --- Extract slugs from product path ---
        [$productSlug, $platform] = explode('/', $product);

        // --- Ensure navigation _index.md files exist ---
        $this->ensureComplianceIndexes($productSlug, $platform, $year, $uploaderEmail);

        // --- Build base paths for markdown and S3 access ---
        $folder = "compliance-reports/{$productSlug}/{$platform}/{$year}/{$version}/";
        $files = Storage::files($folder);
        $relBase = "/" . $folder;

        // --- Categorize uploaded files by function ---
        $fileGroups = ['sbom' => [], 'cwe' => [], 'owasp' => [], 'license' => []];
        foreach ($files as $filePath) {
            $file = basename($filePath);
            $l = strtolower($file);

            // Skip any markdown files (e.g. already existing .md)
            if (Str::endsWith($l, '.md')) continue;

            $ext = pathinfo($file, PATHINFO_EXTENSION);

            // Categorize based on naming patterns
            if (Str::contains($l, 'sbom')) {
                $fileGroups['sbom'][$ext] = $file;
            } elseif (Str::contains($l, 'cwe-top-25')) {
                if (in_array($ext, ['html', 'htm'])) $fileGroups['cwe']['html'] = $file;
                elseif ($ext === 'pdf') $fileGroups['cwe']['pdf'] = $file;
            } elseif (Str::contains($l, 'owasp-top-10')) {
                if (in_array($ext, ['html', 'htm'])) $fileGroups['owasp']['html'] = $file;
                elseif ($ext === 'pdf') $fileGroups['owasp']['pdf'] = $file;
            } elseif (Str::contains($l, 'license')) {
                $fileGroups['license'][$ext] = $file;
            }
        }

        // --- Resolve venture from Hugo site URL ---
        $venturePrefix = $this->getVenturePrefix();

        // --- Find matching release for metadata like weight ---
        $release = \App\Models\Release::where('product', $productSlug)
            ->where('folder', 'new-releases')
            ->where('folder_link', 'like', "%{$platform}%")
            ->where('folder_link', 'like', "%{$version}%")
            ->orderBy('id', 'desc')->first();

        $weight = $release ? $release->weight : 999;
        $title = $this->getProductTitleFromUrl("{$productSlug}/{$platform}") . " {$version}";

        // --- Generate markdown filename (unique per venture) ---
        $slug = strtolower(str_replace(['.', '/', ' '], '-', "{$venturePrefix}-{$productSlug}-for-{$platform}-{$version}-compliance-reports"));

        // --- Forum link adjusted per venture ---
        $forumSlug = $this->getForumSlugFromUrl("{$productSlug}/{$platform}");
        $forumLink = "https://forum.{$venturePrefix}.com/c/{$forumSlug}/";

        // === SBOM SECTION ===
        $sbomSection = "";
        if (!empty($fileGroups['sbom'])) {
            $sbomSection = "### Software Bill of Materials (SBOM)\n\n";
            if (isset($fileGroups['sbom']['json'])) {
                $sbomSection .= '- 🧾 {{< compliance-file relpath="' . $relBase . $fileGroups['sbom']['json'] . '" text="SBOM JSON" >}}' . "\n";
            }
            if (isset($fileGroups['sbom']['xml'])) {
                $sbomSection .= '- 📦 {{< compliance-file relpath="' . $relBase . $fileGroups['sbom']['xml'] . '" text="SBOM XML" >}}' . "\n";
            }
            $sbomSection .= "\n";
        }

        // === CWE & OWASP SECTION ===
        $securitySection = '';
        if (!empty($fileGroups['cwe']) || !empty($fileGroups['owasp'])) {
            $securitySection .= "### Security Weakness Coverage Reports (CWE & OWASP)\n";

            if (!empty($fileGroups['cwe'])) {
                $securitySection .= "- **CWE Top 25 (2024)**:\n";
                if (isset($fileGroups['cwe']['html']))
                    $securitySection .= "  - 🌐 {{< compliance-file relpath=\"" . $relBase . $fileGroups['cwe']['html'] . "\" text=\"HTML\" >}}\n";
                if (isset($fileGroups['cwe']['pdf']))
                    $securitySection .= "  - 📄 {{< compliance-file relpath=\"" . $relBase . $fileGroups['cwe']['pdf'] . "\" text=\"PDF\" >}}\n";
            }

            if (!empty($fileGroups['owasp'])) {
                $securitySection .= "- **OWASP Top 10 (2017/2021)**:\n";
                if (isset($fileGroups['owasp']['html']))
                    $securitySection .= "  - 🌐 {{< compliance-file relpath=\"" . $relBase . $fileGroups['owasp']['html'] . "\" text=\"HTML\" >}}\n";
                if (isset($fileGroups['owasp']['pdf']))
                    $securitySection .= "  - 📄 {{< compliance-file relpath=\"" . $relBase . $fileGroups['owasp']['pdf'] . "\" text=\"PDF\" >}}\n";
            }

            $securitySection .= "\n";
        }

        // === LICENSE SECTION (Optional) ===
        $licenseSection = '';
        if (in_array('license', $sections)) {
            $licenseSection .= "### EULA & Third-Party License Disclosure\n\n";

            // Hardcoded EULA link per venture
            $eulaUrl = "https://files.conholdate.app/viewer/view/4Y8UNm7laVFjMAd0r/aspose_end-user-license-agreement_2024-05-16.pdf";
            $licenseSection .= "- [{$venturePrefix} End User License Agreement (EULA)]({$eulaUrl})\n";

            // Dynamically resolved license PDF path
            $licenseFolder = "compliance-reports/third-party-licenses/{$platform}/";
            $expectedLicenseFile = "third-party-licenses-{$venturePrefix}-{$productSlug}-{$platform}.pdf";

            if (Storage::exists($licenseFolder . $expectedLicenseFile)) {
                $relLicensePath = "/{$licenseFolder}{$expectedLicenseFile}";
                $productTitle = $this->getProductTitleFromUrl("{$productSlug}/{$platform}");
                $licenseSection .= "- {{< compliance-file relpath=\"{$relLicensePath}\" text=\"{$productTitle} – Third-Party Licenses\" >}}\n";
            }

            $licenseSection .= "\n";
        }

        // === Load venture-specific template ===
        $templateKey = "compliance-reports/compliance-reports-templates/{$venturePrefix}-{$platform}-compliance-template.md";

        if (!Storage::exists($templateKey)) {
            $md = "# Compliance report template not found for platform '{$platform}'!";
        } else {
            $template = Storage::get($templateKey);
            $vars = [
                '{{ .Slug }}'             => $slug,
                '{{ .ProductTitle }}'     => $this->getProductTitleFromUrl("{$productSlug}/{$platform}"),
                '{{ .Version }}'          => $version,
                '{{ .Weight }}'           => $weight,
                '{{ .UploaderEmail }}'    => $uploaderEmail ?? '',
                '{{ .ForumUrl }}'         => $forumLink,
                '{{ .SBOM_SECTION }}'     => $sbomSection,
                '{{ .SECURITY_SECTION }}' => $securitySection,
                '{{ .LICENSE_SECTION }}'  => $licenseSection,
                '{{ .SecurityGrade }}'    => 'A',
                '{{ .DownloadsBadgeUrl }}' => '',
            ];
            $md = strtr($template, $vars);
        }

        // --- Final write to S3 ---
        $filename = "{$slug}.md";
        Storage::put($folder . $filename, $md);
    }
}
