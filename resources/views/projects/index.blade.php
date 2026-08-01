@extends('layouts.app-modules')

@section('title', 'Daftar Permohonan Saya')

@section('content')
<project-index-page
    :user="{{ json_encode(Auth::user()) }}"
    user-role="{{ Auth::user()->getRoleNames()->first() ?? 'pemohon' }}"
    csrf-token="{{ csrf_token() }}"
    :projects="{{ json_encode($projects) }}"
    :document-types="{{ json_encode($documentTypes) }}"
    :query-params="{{ json_encode(request()->all()) }}"
></project-index-page>
@endsection
