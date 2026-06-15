@extends('admin.layouts.admin')

@section('admin-content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Edit Dokter</h1>
    <p class="text-gray-600 mt-2">Update informasi dokter di bawah</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    @include('admin.doctors.partials.form', [
        'action' => route('admin.doctors.update', $doctor->id),
        'method' => 'PATCH',
        'edit' => true,
        'doctor' => $doctor,
        'submit_label' => 'Update Dokter'
    ])
</div>
@endsection
