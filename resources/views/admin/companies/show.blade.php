@extends('admin.layouts.app')

@section('title', $company->company_name . ' | ' . ($siteName ?? 'Servx Motors'))
@section('page_title', $company->company_name)

@section('content')
    <livewire:admin.company-details :company="$company" />
@endsection
