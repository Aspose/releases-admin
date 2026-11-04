@extends('layouts.app')

@section('content')

{{-- Compliance report publishing moved to the Tech Compliance Group's service. This page
     only tells people where to publish now: no form, no script, and nothing is sent from it. --}}
<div class="page-header">
    <h1>Compliance Reports have moved</h1>
</div>

<div class="alert alert-info">
    Compliance report pages and files for Aspose and GroupDocs products are now published through
    the Compliance Reports service of the Tech Compliance Group. Uploads on this site are closed.
</div>

<h4>How to obtain the Product Publisher role</h4>
<ol>
    <li>
        Sign in once at <a href="https://compliance.procurize.com">https://compliance.procurize.com</a>
        with your company account (Aspose AD). Every company employee can sign in; signing in creates
        your record in the service.
    </li>
    <li>
        Request publishing permissions for your products: comment on the Compliance Reports announcement
        post on the company blog, or write to Zakhar Shelkovnikov (<a href="mailto:zakhar.shelkovnikov@aspose.com">zakhar.shelkovnikov@aspose.com</a>), giving the full product names, for
        example Aspose.Words for .NET, Aspose.PDF for Java.
    </li>
</ol>

<p>
    Once the role is granted, the upload and publishing screens and the REST API guide become available.
    If publishing will run from CI/CD, create a personal API key in the API keys section.
</p>

<h4>Documentation</h4>
<ul>
    <li>Dashboard User Guide: <a href="https://gitlab.recruitize.ai/krsk-procurize/compliance-reports-generator/-/blob/master/docs/dashboard-user-guide/01-content.md">https://gitlab.recruitize.ai/krsk-procurize/compliance-reports-generator/-/blob/master/docs/dashboard-user-guide/01-content.md</a></li>
    <li>REST API Publishing Guide: <a href="https://compliance.procurize.com/publisher/api-guide">https://compliance.procurize.com/publisher/api-guide</a> (visible with the Product Publisher role; not to new users with the Viewer role, which is expected)</li>
    <li>Full REST API Reference: <a href="https://compliance.procurize.com/api/docs/index.html">https://compliance.procurize.com/api/docs/index.html</a></li>
</ul>

<p>
    For any questions, please contact Zakhar Shelkovnikov, <a href="mailto:zakhar.shelkovnikov@aspose.com">zakhar.shelkovnikov@aspose.com</a>, Tech Compliance Group.
</p>

@endsection
