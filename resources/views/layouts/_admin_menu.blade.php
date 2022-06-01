<!-- <ul class="nav navbar-nav">
    <li><a href="{{ url('admin/posts') }}">Posts</a></li>
    <li><a href="{{ url('admin/categories') }}">Categories</a></li>
    <li><a href="{{ url('admin/comments') }}">Comments</a></li>
    <li><a href="{{ url('admin/tags') }}">Tags</a></li>

    @if (Auth::user()->is_admin)
        <li><a href="{{ url('admin/users') }}">Users</a></li>
    @endif
</ul> -->
<div class="well sidebar-nav">
<ul class="nav nav-list">
                        <!-- <li class="nav-header"><i class="icon-wrench"></i>Venture Administration</li>
                        <li>
                            <a id="ctl00_lnkAddVenture" class="dropdown-toggle" href="{{ url('admin/ventures/amazon-s3-settings') }}">Amazon S3 Settings </a>
                        </li> -->
                        <!-- <li>
                            <a id="ctl00_lnkAddEngine" class="dropdown-toggle" href="ventures/manage-ui">UI Management</a>
                        </li>
                        <li>
                            <a id="ctl00_lnkAddRule" class="dropdown-toggle" href="ventures/manage-url-redirection">URL Redirection</a>
                        </li> -->


                        <li class="nav-header"><i class="icon-wrench"></i>Product's Administration</li>
                        <li>
                            <a id="ctl00_lnkAddSection" class="dropdown-toggle" href="{{ url('admin/products/manage-families') }}">Manage Product Families</a>
                        </li>
                        <li>
                            <a id="ctl00_lnkAddProduct" class="dropdown-toggle" href="{{ url('admin/products/manage-allproducts') }}">Manage Products</a>
                        </li>
                        <li>
                            <a id="ctl00_HyperLink1" class="dropdown-toggle" href="{{ url('admin/products/manage-folders') }}">Manage Folders</a>
                        </li>

                        <!--
                        <li class="nav-header"><i class="icon-wrench"></i>SEO Management</li>
                        <li>
                            <a id="ctl00_lnkApiHomePage" class="dropdown-toggle" href="seo-management/homepage">Home Page- SEO</a>
                        </li>
                        <li>
                            <a id="ctl00_lnkProductHomePage" class="dropdown-toggle" href="seo-management/family">Product's Family- SEO</a>
                        </li>
                        <li>
                            <a id="ctl00_HyperLink3" class="dropdown-toggle" href="seo-management/products">Product- SEO</a>
                        </li> -->



                        <li class="nav-header"><i class="icon-wrench"></i>File Administration</li>
                        <li>
                            <a id="ctl00_lnkGenerateDoc" class="dropdown-toggle" href="{{ url('admin/ventures/file/upload') }}">Upload File </a>
                        </li>
                        <li>
                            <a id="ctl00_HyperLink2" class="dropdown-toggle" href="{{ url('admin/ventures/file/manage-files') }}">View All Files </a>
                        </li>
                        @if (Auth::user()->is_admin)
                        <!-- <li class="nav-header"><i class="icon-wrench"></i>System Administration</li>
                        <li>
                            <a id="ctl00_HyperLink4" class="dropdown-toggle" href="administration/manage-roles">Manage Roles </a>
                        </li>
                        <li>
                            <a id="ctl00_HyperLink5" class="dropdown-toggle" href="administration/manage-users">Manage Users</a>
                        </li> -->
                        @endif
                    </ul>
</div>