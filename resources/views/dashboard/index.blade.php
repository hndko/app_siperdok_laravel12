@extends('layouts.app-modules')

@section('title', 'Dashboard Monitoring')

@section('content')
<dashboard-page
    :user="{{ json_encode(Auth::user()) }}"
    user-role="{{ Auth::user()->getRoleNames()->first() ?? 'pemohon' }}"
    csrf-token="{{ csrf_token() }}"
    :total-projects="{{ $totalProjects }}"
    :approved-count="{{ $approvedCount }}"
    :revision-count="{{ $revisionCount }}"
    :rejected-count="{{ $rejectedCount }}"
    :pending-count="{{ $pendingCount }}"
    :draft-count="{{ $draftCount }}"
    :recent-projects="{{ json_encode($recentProjects) }}"
    :chart-labels="{{ json_encode($chartLabels) }}"
    :chart-values="{{ json_encode($chartValues) }}"
    :status-counts="{{ json_encode($statusCounts) }}"
></dashboard-page>
@endsection
