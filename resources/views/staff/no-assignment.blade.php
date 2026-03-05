@extends('layouts.staff-app')

@section('title', 'No Active Assignment')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Staff Dashboard</li>
                    </ol>
                </div>
                <h4 class="page-title">No Active Assignment</h4>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="mdi mdi-account-clock text-muted" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h3 class="text-muted mb-3">No Active Assignment Found</h3>
                    
                    <p class="text-muted mb-4">
                        You don't have any active unit assignments at the moment. 
                        Please contact your landlord to get assigned to a unit.
                    </p>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="mdi mdi-information me-1"></i>
                            What happens when you get assigned?
                        </h6>
                        <ul class="mb-0 text-start">
                            <li>You'll be able to view your assigned unit details</li>
                            <li>Access maintenance requests from tenants</li>
                            <li>Update work progress and status</li>
                            <li>Communicate with landlords and tenants</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="#" class="btn btn-primary me-2">
                            <i class="mdi mdi-message me-1"></i>
                            Contact Landlord
                        </a>
                        <a href="#" class="btn btn-outline-secondary">
                            <i class="mdi mdi-account-edit me-1"></i>
                            Update Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 