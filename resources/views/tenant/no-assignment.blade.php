@extends('layouts.app')

@section('title', 'No Assignment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
                <h4 class="page-title">Welcome, {{ auth()->user()->name }}!</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="mdi mdi-home-outline" style="font-size: 4rem; color: #6c757d;"></i>
                    </div>
                    
                    <h3 class="card-title">Verify your profile first</h3>
                    <p class="card-text text-muted">
                        Before you can be matched to a unit, upload your identification and supporting documents so we can confirm you are a legitimate prospect. You do not need a unit assignment to complete this step.
                    </p>
                    
                    <div class="mt-4">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading mb-2"><i class="mdi mdi-file-document-outline me-1"></i> Start here: required documents</h6>
                            <p class="mb-2 small mb-0 text-start">Upload government ID and any documents your landlord requires (e.g. proof of income). A landlord can only confidently assign you after this information is on file.</p>
                        </div>
                        <div class="alert alert-info">
                            <h6 class="alert-heading">What happens next?</h6>
                            <ol class="mb-0 text-start ps-3">
                                <li><strong>Upload your documents</strong> using the button below (or <strong>Upload Documents</strong> in the sidebar)</li>
                                <li>Your landlord or admin reviews and verifies your files</li>
                                <li>You are assigned to a unit when there is a match</li>
                                <li>Your tenant dashboard updates with assignment details once you are linked to a unit</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('tenant.upload-documents') }}" class="btn btn-primary btn-lg">
                            <i class="mdi mdi-upload me-1"></i> Upload documents now
                        </a>
                        <a href="javascript:void(0)" class="btn btn-outline-primary ms-2" onclick="alert('After you have uploaded documents, contact your landlord or use Messages if you already know them.')">
                            <i class="mdi mdi-message me-1"></i> Contact landlord
                        </a>
                        <a href="{{ route('logout') }}" class="btn btn-outline-secondary ms-2" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="mdi mdi-logout me-1"></i> Logout
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 