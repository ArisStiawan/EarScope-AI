@extends('admin.layouts.admin')

@section('admin-content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Tambah Dokter Baru</h1>
    <p class="text-gray-600 mt-2">Isi formulir di bawah ini untuk menambahkan dokter baru</p>
</div>

<div class="bg-white rounded-lg shadow-md p-6">
    @include('admin.doctors.partials.form', [
        'action' => route('admin.doctors.store'),
        'method' => 'POST',
        'edit' => false,
        'doctor' => null,
        'submit_label' => 'Tambah Dokter'
    ])
</div>
@endsection
