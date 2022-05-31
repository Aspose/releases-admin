@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }}</h1>
</div>

@include('flash::message')
@if(Session::has('error'))
    <div class="alert alert-danger">
        {{Session::get('error')}}
    </div>
@endif
@if(Session::has('success'))
    <div class="alert alert-success">
        {{Session::get('success')}}
    </div>
@endif

<div class="form-horizontal">
    <input name="_token" type="hidden" value="{{ csrf_token() }}" />
    <div class="control-group">
    
        <span id="productfamily-span" class="control-label">Product Family:</span>
        <div class="controls">
            <select name="productfamily" onchange="getchildnodes(this, 'product');" id="productfamily">
                <option selected value="">-- Select Product Family --</option>
                    @foreach($DropDownContent as $key => $single)
                        <option value="{{ $single['url'] }}" {{ ( $single['url'] == $familySelected) ? 'selected' : '' }}>{{ $single['text'] }}</option>
                    @endforeach
            </select>
            <span style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('productfamily') }} </p>
        </div>
    </div>

    <div class="control-group">
    
        <span id="product-span" class="control-label">Product:</span>
        <div class="controls">
            <select name="product" onchange="getchildnodes(this, 'folder');" id="product">
                @foreach($current_child_products as $name => $url)
                    <option value="{{ $url }}" {{ ( $url == $productSelected) ? 'selected' : '' }} >{{ $name }}</option>
                @endforeach
            </select>
            <span style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('product') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="folder-span" class="control-label" >Select a Folder </span>
            
        <div class="controls">
            <select name="folder" id="folder" onchange="getreleases();">
            <option> Select </option>
                @foreach($folders as $key => $value)
                    <option value="{{ $value }}" {{ ( $folderSelected == $value) ? 'selected' : '' }} > {{ $key }} </option>
                @endforeach
            </select>
            <span style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('folder') }} </p>
        </div>
    </div>

    <div>
        <table cellspacing="0" rules="all" class="table table-bordered" border="1" id="ctl00_ContentPlaceHolder1_grdResultDetails" style="border-collapse:collapse;">
            <tbody>
                <tr>
                    <th scope="col">File Title</th>
                    <th scope="col">Folder</th>
                    <th scope="col">Size</th>
                    <th scope="col">Date Uploaded</th>
                    <th scope="col">Author</th>
                    <th scope="col">&nbsp;</th>
                    <th scope="col">&nbsp;</th>
                    <th scope="col">&nbsp;</th>
                    <th scope="col">&nbsp;</th>
                    <th scope="col">&nbsp;</th>
                </tr>
                @if(!empty($releases))
                    @foreach($releases as $release)
                    <tr>

                        <td>{{ $release->filetitle }}</td>
                        <td>{{ $folderSelected }}</td>
                        <td>{{ $release->filesize }}</td>
                        <td><?php echo date('Y-m-d', strtotime($release->date_added)); ?></td>
                        <td>{{ $release->posted_by }}</td>
                        <td>
                                <?php if (str_starts_with($release->folder_link, '/')) { ?>
                                    <a target="_blank" href="<?php echo $hugositeurl . '' . $release->folder_link ?>">View File</a>
                                <?php }else{ ?>    
                                    <a target="_blank" href="<?php echo $hugositeurl . '/' . $release->folder_link ?>">View File</a>
                                <?php } ?>  
                        </td>
                        <td>
                            <?php if(!empty($release->release_notes_url)){ ?>
                                <a href="<?php echo $release->release_notes_url ?>">View Release Notes</a>
                            <?php } ?>
                        </td>
                        <td><a href="edit/{{ $release->id }}">Edit</a></td>
                        <td><a href="{{ $release->s3_path }}">Download</a></td>
                    </tr>
                @endforeach
                
                @endif
            </tbody>
        </table>
        @if(!empty($releases))
        {{ $releases->withQueryString()->links() }}
        @endif
    </div>
</div>

<script>
    function getchildnodes(node, childtype) {
        console.log(node.value);
        $.ajax({
            url: "{{ route('admin.getchildnodes')}}",
            type: 'POST',
            data: {
                'id': node.value,
                'childtype': childtype,
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                //if (response == 1) {
                var $select = $('#' + childtype);
                $select.find('option').remove();

                var listitems = '';
                if (childtype != 'folder') {
                    //getreleases();
                }
                listitems += "<option value=''> </option>";
                $.each(response, function(key, value) {
                    listitems += "<option value='" + value + "'>" + key + "</option>";
                });

                
                $select.append(listitems);

                if (childtype == 'folder') {
                    getreleases();
                }
                //}
            }
        });
    }
    function getreleases(){
        console.log('KKKKKK')
        url = '/admin/ventures/file/manage-files';

        var productfamily = $('select[name=\'productfamily\']').attr('value');

        if (productfamily != '*') {
            url += '?filter_productfamily=' + encodeURIComponent(productfamily);
        }


        /*  product  */
        var product = $('select[name=\'product\']').attr('value');
        if (product) {
            url += '&filter_product=' + encodeURIComponent(product);
        }
        /*  /product  */

        /*  folder  */
        var folder = $('select[name=\'folder\']').attr('value');
        if (folder) {
            url += '&filter_folder=' + encodeURIComponent(folder);
        }else{
            url += '&filter_folder=' + encodeURIComponent('new-releases');
        }
        /*  /folder  */

        console.log(location)
        location = url;
    }
</script>
</div>
@endsection