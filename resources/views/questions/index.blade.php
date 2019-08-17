@extends('layouts.app')

@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h2>All Questions</h2>
                        <div class="ml-auto">
                            <a href="{{ route('questions.create') }}" class="btn btn-outline-secondary">
                                Ask Question
                            </a>
                        </div>
                    </div>
                </div>
                @include('layouts._message')
                <div class="card-body">

                    @forelse($questions as $question)
                    <div class="media">
                        <div class="d-flex flex-column counters">
                            <div class="vote">
                                <strong>{{ $question->votes_count }}</strong> {{ str_plural('vote', $question->votes_count) }}
                            </div>
                            <div class="status {{ $question->status }}">
                                <strong>{{ $question->answers_count }}</strong> {{ str_plural('answer', $question->answers_count) }}
                            </div>
                            <div class="view">
                                {{ $question->views ." ". str_plural('view', $question->views) }}
                            </div>
                        </div>
                        <div class="media-body">
                            <div class="d-flex line-items-center">
                                <h3 class="mt-0">
                                    <a href="{{ $question->url }}">{{ $question->title }}</a>
                                </h3>
                                <div class="ml-auto">
                                @can('update', $question)
                                        <a href="{{ route('questions.edit', $question->id) }}"
                                            class="btn btn-outline-info btn-sm">Edit</a>
                                @endcan
                                @can('delete', $question)
                                        <form class="form-delete" method="post" action="{{ route('questions.destroy', $question->id) }}">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                @endcan
                                </div>
                            </div>

                            <p class="lead">
                                Asked by
                                <a href="{{ $question->user->url }}">{{ $question->user->name }}</a>
                                <span class="text-muted">
                                    {{ $question->created_date }}
                                </span>
                            </p>
                            <div class="excerpt">
                                {{ $question->excerpt }}
                            </div>
                        </div>
                    </div>
                    <hr>
                    @empty
                    <div class="alert alert-warning">
                          <strong>Sorry</strong> There are no questions available.
                    </div>
                    @endforelse
                </div>
                <div class="card-footer">
                    {{ $questions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
