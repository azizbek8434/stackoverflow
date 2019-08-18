<div class="media post">
    @include('shared._vote',[
        'model' => $answer
    ])
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
                <user-info :model="{{ $answer }}" label="Answered"></user-info>
            </div>
        </div>
    </div>
</div>