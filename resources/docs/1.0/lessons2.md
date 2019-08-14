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

<a name="section-7"></a>

## Episode-17 Deleting The Question

`1` - Edit `resources/views/questions/index.blade.php`

```php
...
<div class="ml-auto">
    ... // edit button
    <form class="form-delete" method="post" action="{ { route('questions.destroy', $question->id) } }">
        @ method('DELETE')
        @ csrf
        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
</div>
...
```

`2` - Edit `app/Http/Controllers/QuestionController.php`

```php
...
    public function destroy(Question $question)
    {
        $question->delete();

        return redirect('/questions')->with('success', 'Your question has been deleted');
    }
...
```

`3` - Edit `resources/sass/app.scss`

```css
...
form.form-delete {
    display: inline;
}
```

`4` - Run npm command

```command
npm run watch
```

<a name="section-8"></a>

## Episode-18 Showing The Question detail

`1` - Edit `routes/web.php`

```php
...
Route::resource('questions', 'QuestionController')->except('show');
Route::get('/questions/{slug}', 'QuestionController@show')->name('questions.show');
...
```

`2` - Edit  `app/Providers/RouteServiceProvider.php`

```php
use App\Question;
...
    public function boot()
    {
        Route::bind('slug', function ($slug) {
            return Question::where('slug', $slug)->first() ?? abort(404);
        });
        ...
    }
...
```

`3` - Edit `app/Question.php`

- change getUrlAttibute param to slug

```php
...
public function getUrlAttribute()
    {
        return route("questions.show", $this->slug);
    }
...
```

`4` - Edit `app/Http/Controllers/QuestionController.php`

```php
...
public function show(Question $question)
    {
        $question->increment('views');

        return view('questions.show', compact('question'));
    }
...
```

`5` - Create new file `show.blade.php` into `resources/views/questions`

`6` - Edit  `resources/views/questions/show.blade.php`

```php
@ extends('layouts.app')

@ section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h1>{ { $question->title } }</h1>
                        <div class="ml-auto">
                            <a href="{ { route('questions.index') } }" class="btn btn-outline-secondary">
                                Back to all questions
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    { !! $question->body_html !! }
                </div>
            </div>
        </div>
    </div>
</div>
@ endsection
```

`7` - Edit `app/Question.php`

- create a new accessor `getBodyHtmlAttribute`

```php
...
public function getBodyHtmlAttribute()
    {
        return \Parsedown::instance()->text($this->body);
    }
...
```

<a name="section-9"></a>

## Episode-19 Authorizing The Question - Using Gates

`1` - Edit `app/Providers/AuthServiceProvider.php`

```php
use Illuminate\Support\Facades\Gate;
...
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('update-question', function ($user, $question) {
            return $user->id === $question->user_id;
        });

        Gate::define('delete-question', function ($user, $question) {
            return $user->id === $question->user_id;
        });
    }
...
```

`2` - Edit `app/Http/Controllers/QuestionController.php`

- using gates in the controller

```php
...
public function edit(Question $question)
{
    if (\Gate::denies('update-question', $question)) {
        abort('403', 'Access denied');
    }
    return view('questions.edit', compact('question'));
}

...
public function destroy(Question $question)
{
    if (\Gate::denies('delete-question', $question)) {
            abort('403', 'Access denied');
    }
    ...
}
```

`3` - Edit `resources/views/questions/index.blade.php`

- using gates in the view

```php
...
@ auth
@ if(Auth::user()->can('update-question', $question))
    <a href="{ { route('questions.edit', $question->id) } }"
        class="btn btn-outline-info btn-sm">Edit</a>
@ endif
@ if(Auth::user()->can('delete-question', $question))
    <form class="form-delete" method="post" action="{ { route('questions.destroy', $question->id) } }">
        @ method('DELETE')
        @ csrf
        <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
    </form>
@ endif
@ endauth
...
```
