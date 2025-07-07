@extends('layouts.app')

@section('content')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="page-header">
    <h1>Upload Compliance Reports</h1>
</div>

@include('flash::message')

@if(Session::has('error'))
<div class="alert alert-danger">{{ Session::get('error') }}</div>
@endif

@if(Session::has('success'))
<div class="alert alert-success">
    {!! Session::get('success') !!} {{-- Allows HTML in message --}}
    @if(Session::has('uploaded_files'))
    <ul class="mt-2 mb-0">
        @foreach(Session::get('uploaded_files') as $file)
        <li>{{ $file }}</li>
        @endforeach
    </ul>
    @endif
</div>
@endif

<form method="POST" action="/admin/ventures/file/upload-compliance" enctype="multipart/form-data" class="form-horizontal" id="compliance-upload-form">
    @csrf

    {{-- Product Family Dropdown --}}
    <div class="control-group">
        <span class="control-label">Product Family:</span>
        <div class="controls">
            <select name="productfamily" onchange="getchildnodesforcompliance(this, 'product');" id="productfamily">
                <option value="">-- Select Product Family --</option>
                @foreach($DropDownContent as $family)
                <option value="{{ $family['url'] }}">{{ $family['text'] }}</option>
                @endforeach
            </select>
            <p class="text-danger">{{ $errors->first('productfamily') }}</p>
        </div>
    </div>

    {{-- Product Dropdown (initially disabled) --}}
    <div class="control-group">
        <span class="control-label">Product:</span>
        <div class="controls">
            <select name="product" id="product" required disabled>
                @foreach($current_child_products as $name => $url)
                <option value="{{ $url }}">{{ $name }}</option>
                @endforeach
            </select>
            <p class="text-danger">{{ $errors->first('product') }}</p>
        </div>
    </div>

    <input type="hidden" name="platform" id="platform" value="">

    {{-- Version Input (initially disabled) --}}
    <div class="control-group">
        <span class="control-label">Version:</span>
        <div class="controls">
            <input type="text" id="version" name="version" value="{{ old('version') }}" required disabled>
            <p class="text-danger">{{ $errors->first('version') }}</p>
        </div>
    </div>

    {{-- Year Input (initially disabled) --}}
    <div class="control-group">
        <span class="control-label">Year:</span>
        <div class="controls">
            <input type="text" id="year" name="year" value="{{ old('year') }}" required disabled>
            <p class="text-danger">{{ $errors->first('year') }}</p>
        </div>
    </div>

    {{-- File Upload (initially disabled) --}}
    <div class="control-group">
        <span class="control-label">Upload Files:</span>
        <div class="controls">
            <input type="file" name="files[]" id="files" multiple required disabled>
            <p class="text-danger">{{ $errors->first('files') }}</p>
        </div>
    </div>

    {{-- Section Checkboxes (initially disabled) --}}
    <div class="control-group">
        <div class="controls" id="section-checkboxes">
            <label class="checkbox-inline">
                <input type="checkbox" name="sections[]" value="license" style="vertical-align: middle;">
                Show EULA &amp; Third-Party License Disclosure (if available)
            </label>
            <p class="text-danger">{{ $errors->first('sections') }}</p>
        </div>
    </div>

    <div class="form-actions">
        {{-- Error placeholder for compliance checks --}}
        <div id="compliance-check-error" class="alert alert-danger" style="display:none;"></div>
        <button type="submit" class="btn btn-success" id="uploadBtn">Upload Compliance Files</button>
    </div>
</form>

@if(Session::has('show_generate_index_button'))
<div class="mt-4 mb-3" id="generate-index-container">
    <h4>
        If you see an error about missing <code>_index.md</code> files, use the button below:
    </h4>
    <button id="generate-indexes-btn" class="btn btn-info" type="button">
        Generate _index.md for Selected Product & Year
    </button>
    <span id="generate-indexes-status" style="margin-left:1em;"></span>
</div>
@endif

<script>
    /**
     * Dynamically loads products for the selected product family (AJAX).
     * Enables the Product dropdown after a family is selected.
     */
    function getchildnodesforcompliance(node, childtype) {
        $.ajax({
            url: "{{ route('admin.getchildnodesforcompliance') }}",
            type: 'POST',
            data: {
                id: node.value,
                childtype: childtype,
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                var $select = $('#' + childtype);
                $select.empty();
                $select.append("<option value=''>-- Select --</option>");
                $.each(response, function(key, value) {
                    $select.append("<option value='" + value + "'>" + key + "</option>");
                });
                $select.prop('disabled', false);
                // After loading products, trigger compliance check (in case user clicks back and forth)
                checkComplianceRequirements();
            }
        });
    }

    /**
     * Progressive enabling/disabling of controls for foolproof UX.
     */
    function updateFormState() {
        // Product Family
        var familySelected = $('#productfamily').val() !== '';
        // Product
        var productSelected = $('#product').val() !== '' && !$('#product').prop('disabled');
        // Version
        var versionVal = $('#version').val().trim();
        var versionEntered = versionVal !== '' && !$('#version').prop('disabled');
        // Year
        var yearVal = $('#year').val().trim();
        var yearEntered = yearVal !== '' && !$('#year').prop('disabled');

        // Enable/disable product
        $('#product').prop('disabled', !familySelected);

        // Enable/disable version and year together (so year will always be ready for JS autofill)
        $('#version').prop('disabled', !productSelected);
        $('#year').prop('disabled', !productSelected);

        // Enable file upload and section checkboxes only if all above filled
        var enableFiles = familySelected && productSelected && versionEntered && yearEntered;
        $('#files').prop('disabled', !enableFiles);
        $('#section-checkboxes input[type=checkbox]').prop('disabled', !enableFiles);
    }

    $(document).ready(function() {
        // Reset and update state on every parent field change
        $('#productfamily').on('change', function() {
            $('#product').val('').prop('disabled', true);
            $('#version').val('').prop('disabled', true);
            $('#year').val('').prop('disabled', true);
            $('#files').val('').prop('disabled', true);
            $('#section-checkboxes input[type=checkbox]').prop('disabled', true);
            updateFormState();
        });

        $('#product').on('change', function() {
            $('#version').val('').prop('disabled', !($(this).val() !== ''));
            $('#year').val('').prop('disabled', !($(this).val() !== ''));
            $('#files').val('').prop('disabled', true);
            $('#section-checkboxes input[type=checkbox]').prop('disabled', true);
            updateFormState();
            // Run compliance requirements check after product change
            checkComplianceRequirements();
        });

        $('#version').on('input', function() {
            // As user types version, year is enabled and auto-filled
            var version = $(this).val().trim();
            var yearInput = $('#year');
            var majorVersionMatch = version.match(/^(\d{2,4})\./);
            if (majorVersionMatch) {
                let year = parseInt(majorVersionMatch[1], 10);
                if (year < 100) {
                    year = 2000 + year;
                }
                yearInput.val(year);
            }
            yearInput.prop('disabled', false);
            $('#files').val('').prop('disabled', true);
            $('#section-checkboxes input[type=checkbox]').prop('disabled', true);
            updateFormState();
            checkComplianceRequirements();
        });

        $('#year').on('input', function() {
            updateFormState();
            checkComplianceRequirements();
        });

        // Section checkbox change triggers compliance check
        $('#section-checkboxes input[type=checkbox]').on('change', function() {
            checkComplianceRequirements();
        });

        // Initial state
        updateFormState();
        checkComplianceRequirements();
    });

    // Platform field logic (optional legacy)
    $(document).on('change', '#product', function() {
        const productUrl = $(this).val();
        const parts = productUrl.split('/');
        if (parts.length > 1) {
            $('#platform').val(parts[parts.length - 2]);
        }
    });

    // Auto-fill year from version major number, e.g., 25.7 -> 2025
    document.addEventListener('DOMContentLoaded', function() {
        const versionInput = document.getElementById('version');
        const yearInput = document.getElementById('year');
        if (versionInput && yearInput) {
            versionInput.addEventListener('input', function() {
                const version = versionInput.value.trim();
                const majorVersionMatch = version.match(/^(\d{2,4})\./);
                if (majorVersionMatch) {
                    let year = parseInt(majorVersionMatch[1], 10);
                    if (year < 100) {
                        year = 2000 + year;
                    }
                    yearInput.value = year;
                }
                updateFormState();
                checkComplianceRequirements();
            });
        }
    });

    // --- AJAX for "Generate _index.md" button ---
    @if(Session::has('show_generate_index_button'))
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('generate-indexes-btn');
        const status = document.getElementById('generate-indexes-status');
        if (btn) {
            btn.addEventListener('click', function() {
                const product = document.getElementById('product').value;
                const year = document.getElementById('year').value;
                status.innerHTML = '';
                if (!product || !year) {
                    status.innerHTML = "<span class='text-danger'>Please select both Product and Year first.</span>";
                    return;
                }
                status.innerHTML = "Generating...";
                fetch("{{ url('/admin/ventures/file/generate-indexes') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            product: product,
                            year: year
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            status.innerHTML = "<span class='text-success'>" + data.message + "</span>";
                        } else {
                            status.innerHTML = "<span class='text-danger'>" + (data.message || 'Failed!') + "</span>";
                        }
                    })
                    .catch(error => {
                        status.innerHTML = "<span class='text-danger'>Error contacting server.</span>";
                        console.error(error);
                    });
            });
        }
    });
    @endif

    /**
     * Checks if the compliance template and third-party license files exist
     * for the selected product/platform. Shows errors with the actual
     * expected file name/path if anything is missing, and disables upload.
     */
    function checkComplianceRequirements() {
        // Get selected product from dropdown
        var product = $('#product').val();

        // Defensive: If product is empty, hide any error and disable upload button
        if (!product || product === "") {
            $('#compliance-check-error').hide();
            $('#uploadBtn').prop('disabled', true);
            return; // No AJAX call needed if product not selected
        }

        // Get all checked sections (e.g. license checkbox) into an array
        var sections = [];
        $('input[name="sections[]"]:checked').each(function() {
            sections.push($(this).val());
        });

        // AJAX request to backend to check requirements
        $.ajax({
            url: "{{ route('admin.compliance.ajax-check-requirements') }}", // Backend route for this check
            type: 'POST', // Always POST for Laravel route to match
            data: {
                product: product,
                sections: sections,
                _token: '{{ csrf_token() }}'
            },
            // Success callback: handle backend JSON response
            success: function(res) {
                // If compliance template is missing, show exact S3 filename/path
                if (!res.templateExists) {
                    $('#compliance-check-error')
                        .html(
                            '❌ Compliance template is missing for this platform.<br>' +
                            'Expected file: <code>' +
                            (res.expectedTemplateFile ? res.expectedTemplateFile : '(unknown)') +
                            '</code><br>' +
                            'Please contact admin or upload the template before continuing.'
                        )
                        .show();
                    $('#uploadBtn').prop('disabled', true);
                }
                // If third-party license is missing (when required), show the exact filename/path
                else if (!res.licenseExists) {
                    $('#compliance-check-error')
                        .html(
                            '❌ Third-party license PDF is missing for this product/platform.<br>' +
                            'Expected file: <code>' +
                            (res.expectedLicenseFile ? res.expectedLicenseFile : '(unknown)') +
                            '</code><br>' +
                            'Please upload the license file to S3 or contact admin.'
                        )
                        .show();
                    $('#uploadBtn').prop('disabled', true);
                }
                // If all requirements met, hide error and enable upload
                else {
                    $('#compliance-check-error').hide();
                    $('#uploadBtn').prop('disabled', false);
                }
            },
            // Error callback: shows server or network error, with backend message if present
            error: function(xhr) {
                var msg = '⚠️ Error checking compliance requirements. Please try again or contact admin.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                    msg += '<br><small>' + xhr.responseJSON.error + '</small>';
                }
                $('#compliance-check-error')
                    .html(msg)
                    .show();
                $('#uploadBtn').prop('disabled', true);
            }
        });
    }
</script>

@endsection