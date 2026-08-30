@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<h3>Dashboard</h3>
<hr>

{{-- 🔥 ROLE BASED ALERT --}}
@role('superadmin')
    <div class="alert alert-primary">Super Admin Dashboard</div>
@endrole

@role('admin.')
    <div class="alert alert-success">Admin Dashboard</div>
@endrole

@role('receptionist')
    <div class="alert alert-warning">Reception Dashboard</div>
@endrole


{{-- 🔥 COMMON CARDS (ALL ROLES) --}}
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4">

    {{-- ONLY ADMIN + SUPERADMIN --}}
    @hasanyrole('admin.|superadmin')
    <div class="col">
        <div class="card radius-10 border-start border-4 border-info">
            <div class="card-body">
                <p>Total Orders</p>
                <h4 class="text-info">4805</h4>
            </div>
        </div>
    </div>
    @endhasanyrole


    {{-- ONLY RECEPTION --}}
    @role('receptionist')
    <div class="col">
        <div class="card radius-10 border-start border-4 border-warning">
            <div class="card-body">
                <p>Visitors Today</p>
                <h4 class="text-warning">120</h4>
            </div>
        </div>
    </div>
    @endrole


    {{-- COMMON --}}
    <div class="col">
        <div class="card radius-10 border-start border-4 border-success">
            <div class="card-body">
                <p>System Status</p>
                <h4 class="text-success">Active</h4>
            </div>
        </div>
    </div>

</div>

@endsection