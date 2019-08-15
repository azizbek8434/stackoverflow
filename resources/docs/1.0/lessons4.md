# Episodes from 31 to 35

- [31-Updating The Answer - Part 1 of 2](#section-1)
- [32-Updating The Answer - Part 2 of 2](#section-2)
- [33-Deleting The Answer - Part 1 of 3](#section-3)
- [34-Deleting The Answer - Part 2 of 3](#section-4)
- [35-Deleting The Answer - Part 3 of 3](#section-5)

<a name="section-1"></a>

## Episode-31 Updating The Answer - Part 1 of 2

`1` - Edit `resources/views/answers/_index.blade.php`

```php
...
<div class="media-body">
{ !! $answer->body_html !! }
    <div class="row d-flex">
        <div class="col-4 justify-content-center align-self-end">
            <div class="ml-auto">
            @ can('update', $answer)
                    <a href="{ { route('questions.answers.edit', [$question->id, $answer->id]) } }"
                        class="btn btn-outline-info btn-sm">Edit</a>
            @ endcan
            @ can('delete', $answer)
                    <form class="form-delete"
                    method="post"
                    action="{ { route('questions.answers.destroy', [$question->id, $answer->id]) } }">
                        @ method('DELETE')
                        @ csrf
                        <button type="submit"
                        class="btn btn-outline-danger btn-sm"
                        onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
            @ endcan
            </div>
        </div> 
        <div class="col-4"></div>
        <div class="col-4 justify-content-center align-self-end">
        <span class="text-muted">Answered: { {  $answer->created_date } }</span>
            <div class="media mt-2">
                <a href="{ { $answer->user->url } }" class="pr-2">
                    <img src="{ { $answer->user->avatar } }" alt="avatar">
                </a>
                <div class="media-body mt-1">
                    <a href="{ { $answer->user->url } }">{ { $answer->user->name } }</a>
                </div>
            </div>
        </div>
    </div>
</div>
...
```

`2` - Create new policy `AnswerPolicy` with model name

```command
php artisan make:policy AnswerPolicy --model=Answer
```

`3` - Edit `app/Policies/AnswerPolicy.php`

```php
...
 public function update(User $user, Answer $answer)
    {
        return $user->id === $answer->user_id;
    }

    public function delete(User $user, Answer $answer)
    {
        return $user->id === $answer->user_id;
    }
...
```

`4` - Edit `app/Providers/AuthServiceProvider.php`

```php
...
protected $policies = [
...
'App\Answer' => 'App\Policies\AnswerPolicy',
...
];
...
```

`5` - Edit `app/Http/Controllers/AnswerController.php`

```php
...
  public function edit(Question $question, Answer $answer)
    {
        $this->authorize('update', $answer);
        return view('answers.edit', compact('question', 'answer'));
    }

     public function update(Request $request, Question $question, Answer $answer)
    {
        $this->authorize('update', $answer);

        $answer->update($request->validate([
            'body' => 'required'
        ]));

        return redirect()
            ->route('questions.show', $question->slug)
            ->with('Your answer updated successfuly');
    }
...
```

<a name="section-2"></a>

## Episode-32 Updating The Answer - Part 2 of 2

`1` - Create new file `edit,blade,php` into `resources/views/answers`

`2` - Edit `resources/views/answers/edit.blade.php`

```php
@ extends('layouts.app')

@ section('content')

<div class="container">

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <h1>Editing answer for question: <strong>{ { $question->title } }</strong></h1>
                    </div>
                    <hr>
                    <form method="post" action="{ { route('questions.answers.update', [$question->id, $answer->id]) } }">
                        @ csrf
                        @ method('PATCH')
                        <div class="form-group">
                            <textarea class="form-control {{ $errors->has('body') ? 'is-invalid' : '' }}" name="body" rows="7">
                            {{ old('body', $answer->body) }}</textarea>
                            @ if($errors->has('body'))
                            <div class="invalid-feedback">
                                <strong>{ { $errors->first('body') } }</strong>
                            </div>
                            @ endif
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-outline-primary">Update</button>
                            <a href="{ { route('questions.show', $question->slug) } }"
                                class="btn btn-outline-default">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@ endsection
```

<a name="section-3"></a>

## Episode-33 Deleting The Answer - Part 1 of 3

`1` - Edit `app/Http/Controllers/AnswerController.php`

```php
...
public function destroy(Question $question, Answer $answer)
    {
        $this->authorize('delete', $answer);

        $answer->delete();

        return back()->with('success', 'Your answer has been removed');
    }
...
```

`2`- Edit `app/Answer.php`

```php
...
    public static function boot()
    {
        ...
        static::deleted(function ($answer) {
            $answer->question->decrement('answers_count');
        });
    }
...
```

<a name="section-4"></a>

## Episode-34 Deleting The Answer - Part 2 of 3

`1` - Edit `app/Answer.php`

```php
...
 static::deleted(function ($answer) {
    $question = $answer->question;

    $question->decrement('answers_count');

    if ($question->best_answer_id === $answer->id) {
        $question->best_answer_id = NULL;
        $question->save();
    }
});
...
public function getStatusAttribute()
{
    return $this->id === $this->question->best_answer_id ? 'vote-accepted' : 'vote-accept';
}
...
```

`2` - Edit `resources/views/answers/_index.blade.php`

```php
...
<a title="Mark this answer as best answer" class="{{ $answer->status }} mt-2">
...
```