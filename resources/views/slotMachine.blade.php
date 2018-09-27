@extends('layouts.app')

@section('content')
    <iframe src="/machine" scrolling="no" width="100%" height="200px" id="machine" ></iframe>
    <div id="machine"></div>

    <script>   
        $('#machine').load('/machine #machine');
    </script>
    <style>

    </style>
@endsection