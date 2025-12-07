@extends('layouts.app')
@section('title', 'Quản lý phòng máy tính')
@yield('css')
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">

@section('content')
<div class="container mt-5">
    <form action="" method="POST">
        @csrf
        <div class="row g-4">
            @for ($i = 1; $i <= 30; $i++)
                <div class="col-md-2">
                    <button type="button" 
                            class="btn btn-primary w-100 py-4 position-relative computer-btn"
                            style="background: linear-gradient(145deg, #2196f3, #1976d2);
                                   border: none;
                                   border-radius: 10px;
                                   box-shadow: 4px 4px 8px rgba(0,0,0,0.1),
                                             -4px -4px 8px rgba(255,255,255,0.1);">
                        <i class="fas fa-desktop mb-2" style="font-size: 24px;"></i>
                        <div class="mt-2">PC {{$i}}</div>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-success">
                            Online
                        </span>
                    </button>
                </div>
            @endfor
        </div>
    </form>
</div>

<style>
.computer-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
}

.computer-btn:active {
    transform: translateY(1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
}

.computer-btn {
    transition: all 0.3s ease;
}
</style>
@endsection
