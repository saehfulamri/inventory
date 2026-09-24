@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
    <div class="auth-card">
        <h1 class="auth-card__title">Masuk</h1>

        @if ($errors->any())
            <div class="flash flash--error" role="alert">
                <ul class="flash__list">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="form">
            @csrf

            <div class="form__group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>

            <div class="form__group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">
            </div>

            <div class="form__group">
                <label class="checkbox">
                    <input type="checkbox" name="remember">
                    Ingat saya
                </label>
            </div>

            <button type="submit" class="btn btn--primary btn--block">Masuk</button>
        </form>
    </div>
@endsection