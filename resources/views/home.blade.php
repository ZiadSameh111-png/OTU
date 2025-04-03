@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <h4 class="text-white mb-4">Welcome, {{ Auth::user()->name }}!</h4>
                    <p class="text-white-50">You are logged into the Smart University System.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-4 mb-3">
                            <div class="card bg-primary">
                                <div class="card-body text-white">
                                    <h5 class="card-title">My Courses</h5>
                                    <p class="card-text">View and manage your courses</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-info">
                                <div class="card-body text-white">
                                    <h5 class="card-title">Assignments</h5>
                                    <p class="card-text">Check your pending assignments</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card bg-success">
                                <div class="card-body text-white">
                                    <h5 class="card-title">Progress</h5>
                                    <p class="card-text">Track your academic progress</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
