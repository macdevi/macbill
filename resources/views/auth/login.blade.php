@extends('layouts.neo')

@section('title','Login MacBilling')

@section('content')
<div class="auth-wrap">
    <div class="auth-box">
        <div class="auth-logo">M</div>

        <div class="auth-title">
            <h1>MacBilling</h1>
            <p>Masuk ke sistem billing ISP</p>
        </div>

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <form class="form-card" method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="field">
                <label>Username atau Email</label>
                <input class="input" name="login" value="{{ old('login') }}" placeholder="admin / kasir / teknisi" autofocus>
            </div>

            <div class="field">
                <label>Password</label>
                <input class="input" type="password" name="password" placeholder="password">
            </div>

            <button class="btn" type="submit">Masuk</button>
        </form>
    </div>
</div>
@endsection
