# Episodes from 11 to 20

- [11-Buiding Question Form - Part 1 of 2](#section-1)
- [12-Buiding Question Form - Part 2 of 2](#section-2)
- [13-Validating and Saving the Question - Part 1 of 2](#section-3)
- [14-Validating and Saving the Question - Part 2 of 2](#section-4)
- [15-Updating The Question - Part 1 of 2](#section-5)
- [16-Updating The Question - Part 2 of 2](#section-6)
- [17-Deleting The Question](#section-7)
- [18-Showing The Question detail](#section-8)
- [19-Authorizing The Question - Using Gates](#section-9)
- [20-Authorizing The Question - Using Policies](#section-10)

<a name="section-1"></a>

## Episode-11 Buiding Question Form - Part 1 of 2

`1` - Edit ``



<a name="section-6"></a>

## Episode-16 Updating The Question - Part 2 of 2

`1` - Create new file `edit.blade.php` into `resources/views/questions`

`2` - Edit `resources/views/questions/edit.blade.php`

```php
@ extends('layouts.app')

@ section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h2>Edit Question</h2>
                        <div class="ml-auto">
                            <a href="{ { route('questions.index') } }" class="btn btn-outline-secondary">
                                Back to all questions
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="post" action="{ { route('questions.update', $question->id) } }">
                        { { method_field('PUT') } }
                        @ include('questions._form', [
                        'buttonText' => 'Update Questions'
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@ endsection
```

`3` - Edit `resources/views/questions/_form.blade.php`

- added second parametr to old value

```php
...
<div class="form-group">
    ...
    <input type="text" name="title" value="{ { old('title', $question->title) } }" id="question-title"
        class="form-control { { $errors->has('title') ? 'is-invalid' : '' } }">
    ...
</div>
<div class="form-group">
    ...
    <textarea id="question-body" class="form-control { { $errors->has('body') ? 'is-invalid' : '' } }" name="body"
        rows="10">{ { old('body', $question->body) } }</textarea>
    ...
</div>
...
```

`4` - Edit `app/Http/Controllers/QuestionController.php`

```php
...
    public function update(AskQuestionRequest $request, Question $question)
    {
        $question->update($request->only('title', 'body'));
        return redirect('/questions')->with('success', 'Your question has been updated.');
    }
...
```
