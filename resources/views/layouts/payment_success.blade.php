@extends('layouts.master')

@section('title', 'Satın Alma Başarılı')

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <i class="fas fa-sim-card me-2"></i>eSIM Market
        </a>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="text-success fw-semibold">🎉 Satın Alma Başarılı</h5>
                    <p class="text-muted">QR Kodunuz:</p>
                    <img src="{{ request()->query('qr') }}" class="img-fluid mt-3 mb-4" style="max-width: 200px;">
                    <div class="alert alert-info">
                        <strong>Paket Bilgisi:</strong> {{ request()->query('package') }}
                    </div>
                    <a href="/esim" class="btn btn-outline-primary">
                        Yeni eSIM Satın Al
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection