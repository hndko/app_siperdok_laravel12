@extends('layouts.app-modules')

@section('title', 'Detail Permohonan: ' . $project->project_number)

@section('content')
<project-show-page
    :user="{{ json_encode(Auth::user()) }}"
    user-role="{{ Auth::user()->getRoleNames()->first() ?? 'pemohon' }}"
    csrf-token="{{ csrf_token() }}"
    :project="{{ json_encode($project) }}"
></project-show-page>
@endsection
