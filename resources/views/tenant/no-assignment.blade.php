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
                    
                    <h3 class="card-title">No Unit Assignment Found</h3>
                    <p class="card-text text-muted">
                        You don't have any unit assignment at the moment. Please contact your landlord to get assigned to a unit.
                    </p>
                    
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">What happens next?</h6>
                            <ul class="mb-0 text-start">
                                <li>Your landlord will assign you to a specific unit</li>
                                <li>You'll receive access to your tenant dashboard</li>
                                <li>You'll need to upload required documents</li>
                                <li>Once documents are verified, your assignment will be activated</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="javascript:void(0)" class="btn btn-primary" onclick="alert('Please contact your landlord directly or use the Messages feature once you have a unit assignment.')">
                            <i class="mdi mdi-message me-1"></i> Contact Landlord
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