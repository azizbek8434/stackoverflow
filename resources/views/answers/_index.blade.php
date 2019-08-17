<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            @include('layouts._message')
            <div class="card-body">
                <div class="card-title">
                    <h2>{{ $answersCount ." ". str_plural("Answer", $answersCount) }}</h2>
                </div>
                <hr>
                @foreach ($answers as $answer)
                <div class="media">
                    <div class="d-flex flex-column vote-controls">
                        <a title="This answer is useful" class="vote-up
                        {{ Auth::guest() ? 'off' : '' }}" 
                        onclick="event.preventDefault(); document.getElementById('vote-up-answer-{{ $answer->id }}').submit();">
                            <i class="fas fa-caret-up fa-3x"></i>
                        </a>
                        <form id="vote-up-answer-{{ $answer->id }}" method="POST" action="/answers/{{ $answer->id }}/vote" style="display:none;">
                            @csrf
                            <input type="hidden" name="vote" value="1">
                        </form>
                        <span class="vote-count">{{ $answer->votes_count }}</span>
                        <a title="This answer is not useful" class="vote-down
                        {{ Auth::guest() ? 'off' : '' }}" 
                        onclick="event.preventDefault(); document.getElementById('vote-down-answer-{{ $answer->id }}').submit();">
                            <i class="fas fa-caret-down fa-3x"></i>
                        </a>
                        <form id="vote-down-answer-{{ $answer->id }}" method="POST" action="/answers/{{ $answer->id }}/vote" style="display:none;">
                            @csrf
                            <input type="hidden" name="vote" value="-1">
                        </form>
                        @can('accept', $answer)
                        <a title="Mark this answer as best answer" class="{{ $answer->status }} mt-2"
                            onclick="event.preventDefault(); document.getElementById('accept-answer-{{ $answer->id }}').submit();">
                            <i class="fas fa-check fa-2x"></i>
                        </a>
                        <form id="accept-answer-{{ $answer->id }}" method="POST"
                            action="{{ route('answers.accept', $answer->id) }}" style="display:none;">
                            @csrf
                        </form>
                        @else
                            @if($answer->is_best)
                                <a title="The question owner accepted this answer as best answer" class="{{ $answer->status }} mt-2">
                                    <i class="fas fa-check fa-2x"></i>
                                </a>
                            @endif
                        @endcan
                    </div>
                    <div class="media-body">
                        {!! $answer->body_html !!}
                        <div class="row d-flex">
                            <div class="col-4 justify-content-center align-self-end">
                                <div class="ml-auto">
                                    @can('update', $answer)
                                    <a href="{{ route('questions.answers.edit', [$question->id, $answer->id]) }}"
                                        class="btn btn-outline-info btn-sm">Edit</a>
                                    @endcan
                                    @can('delete', $answer)
                                    <form class="form-delete" method="post"
                                        action="{{ route('questions.answers.destroy', [$question->id, $answer->id]) }}">
                                        @method('DELETE')
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-4"></div>
                            <div class="col-4 justify-content-center align-self-end">
                                @include('shared._author', [
                                    'model' => $answer,
                                    'label' => 'Answered'
                                ])
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                @endforeach
            </div>
        </div>
    </div>
</div>
