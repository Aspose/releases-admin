@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">

        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2>
                        {{ $title }}
                    </h2>
                    @if(!$progress)
                    <div style="padding: 15px;">
                        <input type="hidden" id="totalfiles">
                        <input type="hidden" id="zipfolderpath_fullpath" value="{{ $zipfolderpath_fullpath }}">
                        <input type="hidden" id="zipfolderpath" value="{{ $zipfolderpath }}">

                        <input type="text" style="margin-bottom: 0;" name="new_zip_file_name" id="new_zip_file_name" placeholder="Zip File Name"> <button href="?generate=true" class="btn" id="getallnetdll" onclick='getMessage()'> Generate Zip File</button>
                        <a style="float:right;" href="/admin/manage-total-net-release/uploadfilemanual?path=<?php echo $zipfolderpath; ?>" class="btn"> Upload Missing Files</a>
                    </div>

                    @endif
                </div>

                <div class="panel-body">
                    @if(!$progress)
                    <div class="progress" id="progressdownload" style="display: none;">
                        <div class="bar" id="progressdownloadbar">Downloading <span id="process_data">0</span> - <span id="total_data">0</span></div>
                    </div>
                    <div class="progress" id="progresscompress" style="display: none;">
                        <div class="bar" id="compressbar" style="width: 10%;"><span id="process_data_compress">Generating Zip ....</span></div>
                    </div>
                    <div class="progress" id="progresszipupload" style="display: none;">
                        <div class="bar" id="zipuploadbar" style="width: 10%;"><span id="process_data_upload">Uploading Zip ....</span></div>
                    </div>
                    <p style="color:red;" id="zip_filepath"></p>
                    <p style="color:red;" id="s3filelink"></p>
                    <p style="color:red;" id="s3filelink_info"></p>
                    @endif

                    <table class="table">
                        <thead>
                            <tr>
                                <td><input type="checkbox" id="selectall" onclick='toggle(this)'></td>
                                <th>id</th>
                                <th>Product</th>
                                <th>link</th>
                                <th>File Path</th>
                                <th>filename</th>
                                <th>filetitle</th>
                                <th>filesize</th>

                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($netrelease as $single)
                            <tr>
                                <td><input type="checkbox" value="{{ $single->id }}" class="selectid" id="selectedopt" name="selectedopt"></td>
                                <td>{{ $single->id }}</td>
                                <td>{{ $single->product }}</td>
                                <td>{{ $single->folder_link }}</td>
                                <td>{{ $single->s3_path }}</td>
                                <td>{{ $single->filename }}</td>
                                <td>{{ $single->filetitle }}</td>
                                <td>{{ $single->filesize }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2">No release found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
<script>
    function toggle(source) {
        checkboxes = document.getElementsByName('selectedopt');
        for (var i = 0, n = checkboxes.length; i < n; i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    function getMessage() {
        var newzipname = jQuery('#new_zip_file_name').val()
        var zipfolderpath_fullpath = jQuery('#zipfolderpath_fullpath').val();
        var zipfolderpath = jQuery('#zipfolderpath').val();

        var checkboxValues = [];
        $('input[name=selectedopt]:checked').map(function() {
            checkboxValues.push($(this).val());
        });
        console.log(checkboxValues.length);
        if (typeof checkboxValues !== 'undefined' && checkboxValues.length > 0) {
            jQuery('#totalfiles').val(checkboxValues.length); // set total count
            $('#progressdownload').css('display', 'block');
            clear_timer = setInterval(get_import_data, 20000);
            $('#total_data').text(checkboxValues.length);
            if (newzipname != '' && newzipname.length > 2) {
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ url('/admin/downloadandcompress') }}",
                    data: {
                        ids: checkboxValues,
                        zipfolderpath_fullpath: zipfolderpath_fullpath,
                        zipfolderpath: zipfolderpath,
                        newzipname: jQuery('#new_zip_file_name').val(),
                    },
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        //console.log("=========" + response + " --------");

                        $('#progressdownloadbar').css('background', 'green');
                        $('#progresscompress').css('display', 'block');
                        if (response.srcfile != '') {
                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                url: "{{ url('/admin/compressfiles') }}",
                                data: {
                                    srcfile: response.srcfile,
                                    zipfolderpath: zipfolderpath,
                                    newzipname: response.zipname,
                                },
                                type: 'POST',
                                dataType: 'json',
                                success: function(responsecompress) {
                                    $('#compressbar').css('width', '100%');
                                    $('#compressbar').css('background', 'green');
                                    $('#zip_filepath').text(responsecompress.zip_file);
                                    $('#process_data_compress').text("Generating Zip Completed");

                                    $('#progresszipupload').css('display', 'block');

                                    console.log("=========" + responsecompress.zip_file + " --------")
                                    console.log("=========" + responsecompress.download_path + " --------");

                                    $.ajax({
                                        headers: {
                                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                        },
                                        url: "{{ url('/admin/uploadziptos3') }}",
                                        data: {
                                            srcfile: responsecompress.download_path,
                                            zipfolderpath_fullpath: jQuery('#zipfolderpath_fullpath').val()
                                        },
                                        type: 'POST',
                                        dataType: 'json',
                                        success: function(responsecupload) {
                                            //$('#compressbar').css('width', '100%');
                                            //$('#compressbar').css('background', 'green');
                                            //$('#zip_filepath').text(responsecompress.zip_file);

                                            
                                            $('#zipuploadbar').css('width', '100%');
                                            $('#zipuploadbar').css('background', 'green');
                                            $('#process_data_upload').text("Uploading Zip Completed");
                                            $('#s3filelink').text(responsecupload.s3_file_link);
                                            $('#s3filelink_info').text(responsecupload.bashresponse);
                                            
                                            console.log("=========" + responsecupload.s3_file_link + " --------")
                                            console.log("=========" + responsecupload.bashresponse + " --------")
                                        }
                                    });
                                }
                            });
                        }
                    }
                });
            } else {
                alert('Please set zip name')
            }
        } else {
            alert('Please select Releases')
        }

    }

    function get_import_data() {
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: "{{ url('/admin/progressdownload') }}",
            data: {
                zipfolderpath_fullpath: jQuery('#zipfolderpath_fullpath').val(),
                zipfolderpath: jQuery('#zipfolderpath').val(),
                newzipname: jQuery('#new_zip_file_name').val(),
            },
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                var totalfiles = $('#totalfiles').val();
                var width = Math.round((response.downloaded / totalfiles) * 100);
                $('#process_data').text(response.downloaded);
                $('#progressdownloadbar').css('width', width + '%');
                if (width >= 100) {
                    clearInterval(clear_timer);
                }
            }
        })
    }
</script>
@endsection