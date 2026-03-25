@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Most Visited Pages</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Page URL</th>
                <th>Visits</th>
            </tr>
        </thead>
        <tbody>
            @foreach($insights as $row)
            <tr>
                <td>{{ $row->url }}</td>
                <td>{{ $row->visits }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
