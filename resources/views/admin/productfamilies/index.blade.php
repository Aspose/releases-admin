@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }}</h1>
</div>

@if(Session::has('info'))
    <div class="alert alert-info">
        {{Session::get('info')}}
    </div>
@endif
@if(Session::has('success'))
    <div class="alert alert-success">
        {{Session::get('success')}}
    </div>
@endif

<form name="aspnetForm" method="post" enctype="multipart/form-data" action="/admin/products/manage-families" id="aspnetForm" class="form-horizontal">
<input name="_token" type="hidden" value="{{ csrf_token() }}" />
    <div class="control-group">
        <span id="metatitleID" class="control-label">Meta Title:</span>
        <div class="controls">
            <input name="metatitle" type="text" style="width:550px;" id="metatitle" class="input-xlarge" value="{{ Request::old('metatitle') }}">
            <span id="Validatetxtmetatitle" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>

    <div class="control-group">
        <span id="metadescriptionID" class="control-label">Meta Description:</span>
        <div class="controls">
            <input name="metadescription" type="text" style="width:550px;" id="metadescription" class="input-xlarge" value="{{ Request::old('metadescription') }}">
            <span id="Validatemetadescriptione" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>

    <div class="control-group">
        <span id="KeywordsID" class="control-label">Keywords:</span>
        <div class="controls">
            <input name="Keywords" type="text" style="width:550px;" id="Keywords" class="input-xlarge" value="{{ Request::old('Keywords') }}">
            <span id="ValidateKeywords" style="color:Red;visibility:hidden;">* Required</span>
        </div>
    </div>

    <div class="control-group">
        <span id="txtFamilyNameid" class="control-label">Product Family:</span>
        <div class="controls">
            <input name="FamilyName" type="text" style="width:550px;" id="FamilyName" class="input-xlarge" value="{{ Request::old('FamilyName') }}" >
            <span id="ValidatetxtFamilyName" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('FamilyName') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="foldernameID" class="control-label">SEO Friendly Key / Folder Name:</span>
        <div class="controls">
            <input name="foldername" type="text" maxlength="50" id="foldername" class="input-xlarge" value="{{ Request::old('foldername') }}" >
            <span id="ValidatortxtKey" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('foldername') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="txtForumLinkID" class="control-label">Forum Link:</span>
        <div class="controls">
            <input name="txtForumLink" type="text" maxlength="200" id="txtForumLink" class="input-xlarge" value="{{ Request::old('txtForumLink') }}" style="width:550px;" value="https://forum.aspose.com/c/neo">
            <p style="color:Red;"> {{ $errors->first('txtForumLink') }} </p>
        </div>
        
    </div>

    <div class="control-group">
        <span id="githubimageID" class="control-label">GitHub Image Link</span>
        <div class="controls">
            <input name="githubimage" type="text" maxlength="200" id="githubimage" class="input-xlarge"  value="{{ Request::old('githubimage') }}" style="width:550px;" value="https://forum.aspose.com/c/3d">
            <p style="color:Red;"> {{ $errors->first('githubimage') }} </p>
        </div>
    </div>

    <div class="control-group">
        <span id="SortOrderID" class="control-label">Sort Order / Weight</span>
        <div class="controls">
            <input name="SortOrder" type="text" maxlength="2" id="SortOrder" class="input-xlarge" value="{{ Request::old('SortOrder') }}" style="width:50px;" >
            <span id="ValidatetxtSortOrder" style="color:Red;visibility:hidden;">* Required</span>
            <p style="color:Red;"> {{ $errors->first('SortOrder') }} </p>
        </div>
    </div>
    <div class="control-group alert-info">





    </div>
    <div class="form-actions">
        <input type="submit" name="addnewpf" value="Add New Product Family"  id="addnewpf" class="btn btn-success btn-large">


    </div>

    <div style="display: none;">
        <table cellspacing="0" rules="all" class="table table-bordered" border="1" id="ctl00_ContentPlaceHolder1_grdResultDetails" style="border-collapse:collapse;">
            <tbody>
                <tr>
                    <th scope="col">Product Family Name</th>
                    <th scope="col">SEO Friendly KEY</th>
                    <th scope="col">Sort Order</th>
                    <th scope="col">Forum Link</th>
                    <th scope="col">&nbsp;</th>
                </tr>
                <tr>
                    <td>Aspose.Total Product Family</td>
                    <td>total</td>
                    <td>0</td>
                    <td>https://forum.aspose.com/c/total</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl02$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl02_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Words Product Family</td>
                    <td>words</td>
                    <td>1</td>
                    <td>https://forum.aspose.com/c/words</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl03$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl03_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Cells Product Family</td>
                    <td>cells</td>
                    <td>2</td>
                    <td>https://forum.aspose.com/c/cells</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl04$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl04_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.PDF Product Family</td>
                    <td>pdf</td>
                    <td>3</td>
                    <td>https://forum.aspose.com/c/pdf</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl05$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl05_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Slides Product Family</td>
                    <td>slides</td>
                    <td>4</td>
                    <td>https://forum.aspose.com/c/slides</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl06$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl06_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Email Product Family</td>
                    <td>email</td>
                    <td>5</td>
                    <td>https://forum.aspose.com/c/email</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl07$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl07_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.BarCode Product Family</td>
                    <td>barcode</td>
                    <td>6</td>
                    <td>https://forum.aspose.com/c/barcode</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl08$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl08_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Imaging Product Family</td>
                    <td>imaging</td>
                    <td>7</td>
                    <td>https://forum.aspose.com/c/imaging</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl09$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl09_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Tasks Product Family</td>
                    <td>tasks</td>
                    <td>8</td>
                    <td>https://forum.aspose.com/c/tasks</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl10$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl10_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.OCR Product Family</td>
                    <td>ocr</td>
                    <td>9</td>
                    <td>https://forum.aspose.com/c/ocr</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl11$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl11_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Diagram Product Family</td>
                    <td>diagram</td>
                    <td>10</td>
                    <td>https://forum.aspose.com/c/diagram</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl12$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl12_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Note Product Family</td>
                    <td>note</td>
                    <td>11</td>
                    <td>https://forum.aspose.com/c/note</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl13$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl13_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.3D Product Family</td>
                    <td>3d</td>
                    <td>12</td>
                    <td>https://forum.aspose.com/c/3d</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl14$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl14_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.CAD Product Family</td>
                    <td>cad</td>
                    <td>13</td>
                    <td>https://forum.aspose.com/c/cad</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl15$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl15_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.HTML Product Family</td>
                    <td>html</td>
                    <td>14</td>
                    <td>https://forum.aspose.com/c/html</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl16$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl16_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.GIS Product Family</td>
                    <td>gis</td>
                    <td>15</td>
                    <td>https://forum.aspose.com/c/gis</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl17$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl17_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.PSD Product Family</td>
                    <td>psd</td>
                    <td>16</td>
                    <td>https://forum.aspose.com/c/psd</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl18$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl18_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.XPS Product Family</td>
                    <td>xps</td>
                    <td>17</td>
                    <td>https://forum.aspose.com/c/xps</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl19$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl19_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.EPS Product Family</td>
                    <td>eps</td>
                    <td>18</td>
                    <td>https://forum.aspose.com/c/eps</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl20$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl20_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.ZIP Product Family</td>
                    <td>zip</td>
                    <td>19</td>
                    <td>https://forum.aspose.com/c/zip</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl21$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl21_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.OMR Product Family</td>
                    <td>omr</td>
                    <td>20</td>
                    <td>https://forum.aspose.com/c/omr</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl22$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl22_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Page Product Family</td>
                    <td>page</td>
                    <td>21</td>
                    <td>https://forum.aspose.com/c/page</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl23$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl23_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.PUB Product Family</td>
                    <td>pub</td>
                    <td>22</td>
                    <td>https://forum.aspose.com/c/pub</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl24$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl24_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.SVG Product Family</td>
                    <td>svg</td>
                    <td>23</td>
                    <td>https://forum.aspose.com/c/svg</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl25$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl25_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Finance Product Family</td>
                    <td>finance</td>
                    <td>24</td>
                    <td>https://forum.aspose.com/c/finance</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl26$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl26_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Drawing Product Family</td>
                    <td>drawing</td>
                    <td>25</td>
                    <td>https://forum.aspose.com/c/drawing</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl27$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl27_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.Font Product Family</td>
                    <td>font</td>
                    <td>26</td>
                    <td>https://forum.aspose.com/c/font</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl28$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl28_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Aspose.TeX Product Family</td>
                    <td>tex</td>
                    <td>27</td>
                    <td>https://forum.aspose.com/c/tex</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl29$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl29_lbnView" class="label label-success">

                    </td>
                </tr>
                <tr>
                    <td>Corporate</td>
                    <td>corporate</td>
                    <td>49</td>
                    <td>https://forum.aspose.com</td>
                    <td>
                        <input type="submit" name="ctl00$ContentPlaceHolder1$grdResultDetails$ctl30$lbnView" value="Edit" id="ctl00_ContentPlaceHolder1_grdResultDetails_ctl30_lbnView" class="label label-success">

                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</form>

</div>
@endsection