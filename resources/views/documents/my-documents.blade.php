@extends('layouts.app')

@section('title', 'My Documents | HRVision')

@section('content')
    <section class="page-head">
        <div>
            <p class="small-label">Document Management</p>
            <h1>My Documents</h1>
            <p class="muted-text">View and download your personal documents.</p>
        </div>
    </section>

    <section class="content-card">
        @if(!$employee)
            <p class="muted-text">No employee profile found for your account.</p>
        @elseif($documents->count() === 0)
            <p class="muted-text">No documents uploaded yet.</p>
        @else
            <div class="employee-list">
                @foreach($documents as $document)
                    <article class="employee-card">
                        <div class="employee-top">
                            <div>
                                <h2>{{ $document->title }}</h2>
                                <p>{{ $document->type }}</p>
                            </div>
                        </div>

                        <div class="employee-details">
                            <p><strong>Uploaded:</strong> {{ $document->uploaded_at?->format('Y-m-d H:i') }}</p>
                        </div>

                        <a href="{{ route('blade.documents.download', $document) }}" class="main-button button-link">Download</a>
                    </article>
                @endforeach
            </div>

            <div class="pagination-wrap">
                {{ $documents->links() }}
            </div>
        @endif
    </section>
@endsection
