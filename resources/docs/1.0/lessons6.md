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
