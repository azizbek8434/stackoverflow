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

<a name="section-2"></a>

## Episode-37 Accepting the answer as best answer - Part 2 of 2

`1` - Edit `app/Policies/AnswerPolicy.php`

```php
...
public function accept(User $user, Answer $answer)
    {
        return $user->id === $answer->question->user_id;
    }
...
```

`2` - Edit `app/Http/Controllers/AcceptAnswerController.php`

```php
...
 public function __invoke(Answer $answer)
    {
        $this->authorize('accept', $answer);
       ...
    }
...
```

`3` - Edit `resources/views/answers/_index.blade.php`

```php
...
@ can('accept', $answer)
    // Mark answer button  
@ else
    @ if($answer->is_best)
            <a title="The question owner accepted this answer as best answer" class="{ { $answer->status } } mt-2">
                <i class="fas fa-check fa-2x"></i>
            </a>
    @ endif
@ endcan
...
```

`4` - Edit `app/Answer.php`

```php
...
public function getStatusAttribute()
{
    return $this->isBest() ? 'vote-accepted' : 'vote-accept';
}

public function getIsBestAttribute()
{
    return $this->isBest();
}

protected function isBest()
{
    return $this->id === $this->question->best_answer_id;
}
...
```

<a name="section-3"></a>

## Episode-38 Favoriting The Question - Part 1 of 4

`1` -  Create new migration file `create_favorites_table`

```command
php artisan make:migration create_favorites_table
```

`2` - Edit `app/User.php`

- Using `belongsToMany` eloquent relation

```php
...
public function favorites()
{
    $this->belongsToMany(Question::class, 'favorites')->withTimestamps();
    // $this->belongsToMany(Question::class, 'favorites', 'author_id', 'question_id'); // when column names are optionally named
}
...
```

`3` - Edit `app/Question.php`

```php
...
public function favorites()
{
    return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    // $this->belongsToMany(User::class, 'favorites', 'some_id', 'user_id'); // when column names are optionally named
}
...
```

<a name="section-4"></a>

## Episode-39 Favoriting The Question - Part 2 of 4

`1` - Edit `app/Question.php`

```php
...
public function getIsFavoritedAttribute()
{
    return $this->isFavorited();
}

public function getFavoritesCountAttribute()
{
    return $this->favorites->count();
}

protected function isFavorited()
{
    return $this->favorites()->where('user_id', auth()->id())->count() > 0;
}
...
```

<a name="section-5"></a>

## Episode-40 Favoriting The Question - Part 3 of 4

`1` - Create new seeder file `UsersQuestionsAnswersTableSeeder`

```command
php artisan make:seeder UsersQuestionsAnswersTableSeeder
```

`2` - Edit `database/seeds/UsersQuestionsAnswersTableSeeder.php`

```php
<?php

use App\User;
use App\Answer;
use App\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersQuestionsAnswersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('answers')->delete();
        DB::table('questions')->delete();
        DB::table('users')->delete();
        factory(User::class, 3)->create()->each(function ($user) {
            $user->questions()
                ->saveMany(
                    factory(Question::class, rand(3, 5))->make()
                )
                ->each(function ($question) {
                    $question->answers()
                        ->saveMany(
                            factory(Answer::class, rand(1, 5))->make()
                        );
                });
        });
    }
}
```

`3` - Create new seeder file `FavoritesTableSeeder`

```command
php artisan make:seeder FavoritesTableSeeder
```

`4` - Edit `database/seeds/FavoritesTableSeeder.php`

```php
<?php

use App\User;
use App\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FavoritesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('favorites')->delete();
        $users = User::pluck('id')->all();
        $numberOfUsers = count($users);
        foreach (Question::all() as $question) {
            for ($i = 0; $i < rand(1, $numberOfUsers); $i++) {
                $user = $users[$i];
                $question->favorites()->attach($user);
            }
        }
    }
}
```

`5` - Edit `database/seeds/DatabaseSeeder.php`

```php
...
public function run()
{
    $this->call([
        UsersQuestionsAnswersTableSeeder::class,
        FavoritesTableSeeder::class
    ]);
}
...
```

`6` - Run artisan command for seeding single seeder file

```command
php artisan db:seed --class=FavoritesTabelSeeder
```

`7` - Edit `resources/views/questions/show.blade.php`

- displaying favorites count

```php
...
<span class="favorites-count">{ { $question->favorites_count } }</span>
...
```

<a name="section-6"></a>

## Episode-41 Favoriting The Question - Part 4 of 4

`1` - Edit `routes/web.php`

```php
...
Route::post('/questions/{question}/favrites', 'FavoritesController@store')->name('questions.favorite');
Route::delete('/questions/{question}/favrites', 'FavoritesController@destroy')->name('questions.unfavorite');
...
```

`2` - Create new controller FavoritesController

```command
php artisan make:controller FavoritesController
```

`3` - Edit `app/Http/Controllers/FavoritesController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Question;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Question $question)
    {
        $question->favorites()->attach(auth()->id());

        return back();
    }

    public function destroy(Question $question)
    {
        $question->favorites()->detach(auth()->id());
        return back();
    }
}
```

`4` - Edit `resources/views/questions/show.blade.php`

```php
...
<a title="Click to mark favorite question (Click agan to undo)" class="favorite mt-2
{ { Auth::guest() ? 'off' : ($question->is_favorited ? 'favorited' : '') } }"
onclick="event.preventDefault(); document.getElementById('favorite-question-{ { $question->id } }').submit();">
    <i class="fas fa-star fa-2x"></i>
    <span class="favorites-count">{ { $question->favorites_count } }</span>
</a>
<form id="favorite-question-{ { $question->id } }" method="POST"
action="/questions/{ { $question->id } }/favorites" style="display:none;">
@ csrf
@ if($question->is_favorited)
    @ method('DELETE');
@ endif
...
```