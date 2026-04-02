@extends('layouts.app')

@section('title', $title.' | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">HRVision Module</p>
            <h1>{{ $title }}</h1>
            <p class="muted-text">{{ $description }}</p>
        </div>
    </section>

    <section class="content-card">
        <p class="muted-text">
            This Blade page route is ready. The full {{ strtolower($title) }} UI can be built here next.
        </p>
    </section>
@endsection
