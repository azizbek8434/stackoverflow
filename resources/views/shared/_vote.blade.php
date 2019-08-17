@if($model instanceof App\Question)
    @php
        $name = 'question';
        $firstUriSegment = 'questions';
    @endphp
@elseif($model instanceof App\Answer)
    @php
        $name = 'answer';
        $firstUriSegment = 'answers';
    @endphp
@endif
@php
    $formId = $name .'-'.$model->id;
    $formAction = "/{$firstUriSegment}/{$model->id}/vote"
@endphp

<div class="d-flex flex-column vote-controls">
    <a title="This question is useful" class="vote-up {{ Auth::guest() ? 'off' : '' }}"
        onclick="event.preventDefault(); document.getElementById('vote-up-{{ $formId }}').submit();">
        <i class="fas fa-caret-up fa-3x"></i>
    </a>
    <form id="vote-up-{{ $formId }}" method="POST" action="{{ $formAction }}" style="display:none;">
        @csrf
        <input type="hidden" name="vote" value="1">
    </form>
    <span class="vote-count">{{ $model->votes_count }}</span>
    <a title="This question is not useful" class="vote-down {{ Auth::guest() ? 'off' : '' }}"
        onclick="event.preventDefault(); document.getElementById('vote-down-{{ $formId }}').submit();">
        <i class="fas fa-caret-down fa-3x"></i>
    </a>
    <form id="vote-down-{{ $formId }}" method="POST" action="{{ $formAction }}" style="display:none;">
        @csrf
        <input type="hidden" name="vote" value="-1">
    </form>
    @if($model instanceof App\Question)
        @include('shared._favorite',[
    'model' => $model
    ])
    @elseif($model instanceof App\Answer)
        @include('shared._accept',[
    'model' => $model
    ])
    @endif
</div>
