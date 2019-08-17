# Episodes from 48 to 54

- [48-Refactoring The Models](#section-1)
- [49-Refactoring The Views - Part 1 of 2](#section-2)
- [50-Refactoring The Views - Part 2 of 2](#section-3)
- [51-Preventing The Application from XSS Attack - Part 1 of 2](#section-4)
- [52-Preventing The Application from XSS Attack - Part 2 of 2](#section-5)
- [53-Miscellaneous](#section-6)
- [54-Tidying up our views](#section-7)

<a name="section-1"></a>

## Episode-48 Refactoring The Models

- Reflactoring Models using `DRY` (Don't repeat yourself) principle

`1` - Edit `app/User.php`

```php
...
public function voteQuestion(Question $question, $vote)
{
    $voteQuestions = $this->voteQuestions();
    $this->_vote($voteQuestions, $question, $vote);
}

public function voteAnswer(Answer $answer, $vote)
{
    $voteAnswers = $this->voteAnswers();
    $this->_vote($voteAnswers, $answer, $vote);
}
 private function _vote($relationship, $model, $vote)
{
    if ($relationship->where('votable_id', $model->id)->exists()) {
        $relationship->updateExistingPivot($model, ['vote' => $vote]);
    } else {
        $relationship->attach($model, ['vote' => $vote]);
    }
    $model->load('votes');

    $upVotes = (int) $model->upVotes()->sum('vote');

    $downVotes = (int) $model->downVotes()->sum('vote');

    $model->votes_count = $upVotes + $downVotes;

    $model->save();
}
...
```

`2` - Create new Traits folder `app`

`3` - Create new trait file `VotableTrait` into `app/Traits`

`4` - Edit `app/Traits/VotableTrait.php`

```php
<?php

namespace App\Traits;

use App\User;

trait VotableTrait
{
    public function votes()
    {
        return $this->morphToMany(User::class, 'votable')->withTimestamps();
    }

    public function upVotes()
    {
        return $this->votes()->wherePivot('vote', 1);
    }

    public function downVotes()
    {
        return $this->votes()->wherePivot('vote', -1);
    }
}
```

`5` - Edit `app/Answer.php`

```php
use App\Traits\VotableTrait;
...
class Answer extends Model
{
use VotableTrait;
...
}
```

`6` - Edit `app/Question.php`

```php
use App\Traits\VotableTrait;
...
class Question extends Model
{
use VotableTrait;
...
}
```

<a name="section-2"></a>

## Episode-49 Refactoring The Views - Part 1 of 2

`1` - Edit `resources/views/answers/_index.blade.php`

```php
...
<div class="col-4 justify-content-center align-self-end">
    @ include('shared._author', [
        'model' => $answer,
        'label' => 'Answered'
    ])
</div>
...
```

`2` - Create new folder `shared` into `resources/views`

`3` - Create new file `_author.blade.php` into `resources/views/shared`

`4` - Edit `resources/views/shared/_author.blade.php`

```php
<span class="text-muted">{ { $label . ": " . $model->created_date } }</span>
<div class="media mt-2">
    <a href="{ { $model->user->url } }" class="pr-2">
        <img src="{ { $model->user->avatar } }" alt="avatar">
    </a>
    <div class="media-body mt-1">
        <a href="{ { $model->user->url } }">{ { $model->user->name } }</a>
    </div>
</div>
```

`5` - Edit `resources/views/questions/show.blade.php`

```php
...
<div class="media-body">
    { !! $question->body_html !! }
    <div class="row">
        <div class="col-4"></div>
        <div class="col-4"></div>
        <div class="col-4">
            @ include('shared._author',[
                'model' => $question,
                'label' => 'Asked'
            ])
        </div>
    </div>
</div>
...
```

<a name="section-3"></a>

## Episode-50 Refactoring The Views - Part 2 of 2

`1` - Edit `resources/views/questions/show.blade.php`

```php
...
 <div class="media">
    @ include('shared._vote',[
        'model' => $question
    ])
    ...
</div>
...
```

`2` - Edit `resources/views/answers/_index.blade.php`

```php
...
 <div class="media">
    @ include('shared._vote',[
        'model' => $answer
    ])
    ...
</div>
...
```

`3` - Create new file `_vote.blade.php` into `resources/views/shared`

`4` - Create new file `_favorite.blade.php` into `resources/views/shared`

`5` - Create new file `_accept.blade.php` into `resources/views/shared`

`6` - Edit `resources/views/shared/_vote.blade.php`

```php
@ if($model instanceof App\Question)
    @ php
        $name = 'question';
        $firstUriSegment = 'questions';
    @ endphp
@ elseif($model instanceof App\Answer)
    @ php
        $name = 'answer';
        $firstUriSegment = 'answers';
    @ endphp
@ endif
@ php
    $formId = $name .'-'.$model->id;
    $formAction = "/{$firstUriSegment}/{$model->id}/vote"
@ endphp
<div class="d-flex flex-column vote-controls">
    <a title="This question is useful" class="vote-up { { Auth::guest() ? 'off' : '' } }"
        onclick="event.preventDefault(); document.getElementById('vote-up-{ { $formId } }').submit();">
        <i class="fas fa-caret-up fa-3x"></i>
    </a>
    <form id="vote-up-{ { $formId } }" method="POST" action="{ { $formAction } }" style="display:none;">
        @ csrf
        <input type="hidden" name="vote" value="1">
    </form>
    <span class="vote-count">{ { $model->votes_count } }</span>
    <a title="This question is not useful" class="vote-down { { Auth::guest() ? 'off' : '' } }"
        onclick="event.preventDefault(); document.getElementById('vote-down-{ { $formId } }').submit();">
        <i class="fas fa-caret-down fa-3x"></i>
    </a>
    <form id="vote-down-{ { $formId } }" method="POST" action="{ { $formAction } }" style="display:none;">
        @ csrf
        <input type="hidden" name="vote" value="-1">
    </form>
    @ if($model instanceof App\Question)
        @ include('shared._favorite',[
    'model' => $model
    ])
    @ elseif($model instanceof App\Answer)
        @ include('shared._accept',[
    'model' => $model
    ])
    @ endif
</div>
```

`7` - Edit `resources/views/shared/_accept.blade.php`

```php
@ can('accept', $model)
<a title="Mark this answer as best answer" class="{ { $model->status } } mt-2"
    onclick="event.preventDefault(); document.getElementById('accept-answer-{ { $model->id } }').submit();">
    <i class="fas fa-check fa-2x"></i>
</a>
<form id="accept-answer-{ { $model->id } }" method="POST"
    action="{ { route('answers.accept', $model->id) } }" style="display:none;">
    @ csrf
</form>
@ else
    @ if($model->is_best)
        <a title="The question owner accepted this answer as best answer" class="{ { $model->status } } mt-2">
            <i class="fas fa-check fa-2x"></i>
        </a>
    @ endif
@ endcan
```

`8` - Edit `resources/views/shared/_favorite.blade.php`

```php
<a title="Click to mark favorite question (Click agan to undo)" class="favorite mt-2 
{ { Auth::guest() ? 'off' : ($model->is_favorited ? 'favorited' : '') } }" onclick="event.preventDefault(); document.getElementById('favorite-question-{ { $model->id } }').submit();">
    <i class="fas fa-star fa-2x"></i>
    <span class="favorites-count">{ { $model->favorites_count } }</span>
</a>
<form id="favorite-question-{ { $model->id } }" method="POST"
    action="/questions/{ { $model->id } }/favorites" style="display:none;">
    @ csrf
    @ if($model->is_favorited)
        @ method('DELETE');
    @ endif
</form>
```

<a name="section-4"></a>

## Episode-51 Preventing The Application from XSS Attack - Part 1 of 2

`1` -  Edit `resources/views/questions/index.blade.php`

```php
...
 <div class="excerpt">
    { { $question->excerpt } }
</div>
...
```

`2` - Edit `resources/views/questions/show.blade.php`

```php
...
//  { !! $question->body_html !! } changed this line to
    { { $question->excerpt } }
```

`3` - Edit `app/Question.php`

```php
...
public function getBodyHtmlAttribute()
{
    return $this->bodyHtml();
}

public function getExcerptAttribute()
{
    return $this->excerpt(250);
}

public function excerpt($length)
{
    return str_limit(strip_tags($this->bodyHtml()), $length);
}

public function bodyHtml()
{
    return \Parsedown::instance()->text($this->body);
}
...
```

<a name="section-5"></a>

## Episode-52 Preventing The Application from XSS Attack - Part 2 of 2

`1` - Installing `mewebstudio/Purifier`

```command
composer require mews/purifier
```

`2` - Configuration purifier, publish config command

```command
php artisan vendor:publish --provider="Mews\Purifier\PurifierServiceProvider"
```

`3` - Edit `app/Question.php`

- using purifier package's helper `clean`

```php
...
public function getBodyHtmlAttribute()
{
    return clean($this->bodyHtml());
}
...
```

second way using `clean` helper is create new `setBodyAttribute`

```php
// public function setBodyAttibute($value)
// {
//     $this->attributes['body'] = clean($value);
// }
```

`4` - Edit `app/Answer.php`

```php
...
public function getBodyHtmlAttribute()
{
    return clean(\Parsedown::instance()->text($this->body));
}
...
```

<a name="section-6"></a>

## Episode-53 Miscellaneous

`1` - Edit `routes/web.php`

```php
...
Route::get('/', 'QuestionController@index');
...
```

`2` - Edit `app/Providers/RouteServiceProvider.php`

```php
...
public function boot()
{
    Route::bind('slug', function ($slug) {
        return Question::with(['answers.user', 'answers' => function ($query) {
            $query->orderBy('votes_count', 'DESC');
        }])->where('slug', $slug)->first() ?? abort(404);
    });
    ...
}
...
```

second way ordering answers by votes_count => Edit `app/Question.php`

```php
...
public function answers()
{
    return $this->hasMany(Answer::class)->orderBy('votes_count', 'DESC');
}
...
```

`3` - Edit `resources/views/answers/_index.blade.php`

```php
@ if($answersCount > 0 )
    ...
@ endif
```

`4` - Edit `resources/views/questions/index.blade.php`

```php
@ forelse($questions as $question)
...
@ empty
<div class="alert alert-warning">
    <strong>Sorry</strong> There are no questions available.
</div>
@ endforelse
...
```
