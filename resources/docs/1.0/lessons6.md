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
