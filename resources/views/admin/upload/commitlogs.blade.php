@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>{{ $title }}</h1>
</div>
<div>
    <table cellspacing="0" rules="all" class="table table-bordered" border="1" id="ctl00_ContentPlaceHolder1_grdResultDetails" style="border-collapse:collapse;">
        <tbody>
            <tr>
                <th scope="col">File id</th>
                <th scope="col">Log</th>
                <th scope="col">Date</th>
            </tr>
            
            @if(!empty($logs))
                @foreach($logs as $log)
                <tr>
                    <td>{{ $log->release_id }}</td>
                    <td>{{ $log->log }}</td>
                    <td>{{ $log->created_at }}</td>
                </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
@endsection