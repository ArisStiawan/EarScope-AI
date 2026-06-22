@extends('admin.layouts.admin')

@section('admin-content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Edit Doctor</h1>
    <p class="text-gray-600 mt-2">Update doctor information below</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    @include('admin.doctors.partials.form', [
        'action' => route('admin.doctors.update', $doctor->id),
        'method' => 'PATCH',
        'edit' => true,
        'doctor' => $doctor,
<<<<<<< HEAD
        'submit_label' => 'Update Doctor'
=======
        'submit_label' => 'Update Data Dokter'
>>>>>>> b339378d2604097b8b045d1e0843952bead91c98
    ])
</div>
@endsection
