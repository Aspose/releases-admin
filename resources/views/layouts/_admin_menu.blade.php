<!-- <ul class="nav navbar-nav">
    <li><a href="{{ url('admin/posts') }}">Posts</a></li>
    <li><a href="{{ url('admin/categories') }}">Categories</a></li>
    <li><a href="{{ url('admin/comments') }}">Comments</a></li>
    <li><a href="{{ url('admin/tags') }}">Tags</a></li>

    @if (Auth::user()->is_admin)
        <li><a href="{{ url('admin/users') }}">Users</a></li>
    @endif
</ul> -->
<style>
    .custom-padding {
        padding-left: 25px;
    }
</style>
<div class="well sidebar-nav">
<ul class="nav nav-list">
                         <!--<li class="nav-header"><i class="icon-wrench"></i>Venture Administration</li>
                        <li>
                            <a id="ctl00_lnkAddVenture" class="dropdown-toggle" href="{{ url('admin/ventures/amazon-s3-settings') }}">Hugo SiteUrl </a>
                        </li>-->
                        <!-- <li>
                            <a id="ctl00_lnkAddEngine" class="dropdown-toggle" href="ventures/manage-ui">UI Management</a>
                        </li>
                        <li>
                            <a id="ctl00_lnkAddRule" class="dropdown-toggle" href="ventures/manage-url-redirection">URL Redirection</a>
                        </li> -->


                        <li class="nav-header"><i class="icon-wrench"></i>Product’s Administration</li>
                        <li class="custom-padding">
                            <a id="ctl00_lnkAddSection" class="dropdown-toggle" href="{{ url('admin/products/manage-families') }}">Add New Product Family</a>
                        </li>
                        <li class="custom-padding">
                            <a id="ctl00_lnkAddProduct" class="dropdown-toggle" href="{{ url('admin/products/manage-allproducts') }}">Add New Product</a>
                        </li>
                        <!-- <li>
                            <a id="ctl00_HyperLink1" class="dropdown-toggle" href="{{ url('admin/products/manage-folders') }}">Manage Folders</a>
                        </li> -->

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



                        <li class="nav-header"><i class="icon-wrench"></i>File/Release Adminstration</li>
                        <li class="custom-padding">
                            <a id="ctl00_lnkGenerateDoc" class="dropdown-toggle" href="{{ url('admin/ventures/file/upload') }}">Upload New Release/File </a>
                        </li>
                        <li class="custom-padding">
                            <a id="ctl00_HyperLink2" class="dropdown-toggle" href="{{ url('admin/ventures/file/manage-files') }}">View All Releases/Files </a>
                        </li>
                        <li class="custom-padding">
                        <a id="ctl00_lnkComplianceUpload" class="dropdown-toggle" href="{{ url('admin/ventures/file/compliance') }}">Upload Compliance Reports</a>
                        </li>

                        @if (Auth::user()->is_admin == 1 || Auth::user()->is_admin == 2)
                         <li class="nav-header"><i class="icon-wrench"></i>System Administration</li>
                        <!-- <li class="custom-padding">
                            <a id="ctl00_HyperLink4" class="dropdown-toggle" href="administration/manage-roles">Manage Roles </a>
                        </li> -->
                        <li class="custom-padding">
                            <a id="ctl00_HyperLink5" class="dropdown-toggle" href="{{ url('admin/manage-users') }}">Manage Users</a>
                        </li>
                        @endif
                        @if (Auth::user()->is_admin == 1)
                         <li class="nav-header"><i class="icon-wrench"></i>Manage Total.Net Release </li>
                        <li class="custom-padding">
                            <a id="ctl00_HyperLink5" class="dropdown-toggle" href="{{ url('admin/manage-total-net-release') }}">Manage Total.Net</a>
                        </li>
                        <!--
                        <li class="custom-padding">
                            <a id="ctl00_HyperLink5" class="dropdown-toggle" href="{{ url('admin/manage-total-net-release?family=java') }}">Manage Total.Java</a>
                        </li>
                      -->
                        <li class="custom-padding">
                            <a id="ctl00_HyperLink5" class="dropdown-toggle" href="{{ url('admin/manage-total-net-release?family=cpp') }}">Manage Total.C++</a>
                        </li>
                        <li class="nav-header"><i class="icon-wrench"></i>Hugo SiteUrl</li>
                        <li class="custom-padding">
                            <a id="ctl00_lnkAddVenture" class="dropdown-toggle" href="{{ url('admin/ventures/amazon-s3-settings') }}">Hugo SiteUrl </a>
                        </li>
                        <li class="nav-header"><i class="icon-wrench"></i>Env Info</li>
                        <li class="custom-padding"><small> DB:  <?php echo env('DB_DATABASE')?></small></li>
                        <li class="custom-padding"><small> Repo:  <?php echo env('GIT_REPO')?></small></li>
                        <li class="custom-padding"><small> Branch:  <?php echo env('GIT_BRANCH')?></small></li>
                        <li class="custom-padding"><small> Bucket:  <?php echo env('AWS_BUCKET')?></small></li>
                        <hr>

                        <li class="custom-padding"><small> Translation:</small></li>
                        <li class="custom-padding"><small> MULTILINGUAL:  <?php echo env('MULTILINGUAL')?></small></li>
                        <li class="custom-padding"><small> SPREADSHEETID:  <?php echo env('SPREADSHEETID')?></small></li>
                        <li class="custom-padding"><small> SPREADSHEETIDMANUAL:  <?php echo env('SPREADSHEETIDMANUAL')?></small></li>
                        @endif
                    </ul>
</div>
