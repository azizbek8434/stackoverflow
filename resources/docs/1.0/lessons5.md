# Episodes from 36 to 45

- [36-Accepting the answer as best answer - Part 1 of 2](#section-1)
- [37-Accepting the answer as best answer - Part 2 of 2](#section-2)
- [38-Favoriting The Question - Part 1 of 4](#section-3)
- [39-Favoriting The Question - Part 2 of 4](#section-4)
- [40-Favoriting The Question - Part 3 of 4](#section-5)
- [41-Favoriting The Question - Part 4 of 4](#section-6)
- [42-Voting The Question & Answer - Part 1 of 6](#section-7)
- [43-Voting The Question & Answer - Part 2 of 6](#section-8)
- [44-Voting The Question & Answer - Part 3 of 6](#section-9)
- [45-Voting The Question & Answer - Part 4 of 6](#section-10)

<a name="section-1"></a>

## Episode-36 Accepting the answer as best answer - Part 1 of 2

`1` - Edit `resources/views/answers/_index.blade.php`

- Mark button `onclick` method

```php
...
<a title="Mark this answer as best answer"
    class="{ { $answer->status } } mt-2"
    onclick="event.preventDefault(); document.getElementById('accept-answer-{ { $answer->id } }').submit();">
    <i class="fas fa-check fa-2x"></i>
</a>
<form id="accept-answer-{ { $answer->id } }" method="POST" action="{ { route('answers.accept', $answer->id) } }" style="display:none;">
    @ csrf
</form>
...
```

`2` - Create new controller `AcceptAnswerController` this will be single action controller

```command
php artisan make:controller AcceptAnswerController --invokable
```

`3` - Edit `routes/web.php`

```php
...
Route::post('/answers/{answer}/accept', 'AcceptAnswerController')->name('answers.accept');
...
```

`4` - Edit `app/Http/Controllers/AcceptAnswerController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Answer;
use Illuminate\Http\Request;

class AcceptAnswerController extends Controller
{
    public function __invoke(Answer $answer)
    {
        $answer->question->acceptBestAnswer($answer);
        return back();
    }
}
```

`5` - Edit `app/Question.php`

```php
...
    public function acceptBestAnswer($answer)
    {
        $this->best_answer_id = $answer->id;
        $this->save();
    }
...
```

