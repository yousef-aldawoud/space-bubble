@extends('admin.main-template')
@section('content')
<head><title>Area {{$area->name}}</title></head>
<admin-area id="{{$area->id}}" area-name ="{{$area->name}}"></admin-area>
@endSection