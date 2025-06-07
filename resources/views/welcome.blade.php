@extends('layouts.app')

@section('content')
    <livewire:navigation-menu />
    @include('partials.hero-section')
    {{-- Anda bisa tambahkan bagian "Premium Collection" di sini juga jika mau --}}
    @include('partials.subscription-section')
    @include('partials.footer-section')
@endsection