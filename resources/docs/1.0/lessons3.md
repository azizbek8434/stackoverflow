# Episodes from 21 to 30

- [21-Designing Answer Schema](#section-1)
- [22-Generating Fake Answers - Part 1 of 2](#section-2)
- [23-Generating Fake Answers - Part 2 of 2](#section-3)
- [24-Displaying answers for question](#section-4)
- [25-Adding Vote Controls on Question and Answer - Part 1 of 3](#section-5)
- [26-Adding Vote Controls on Question and Answer - Part 2 of 3](#section-6)
- [27-Adding Vote Controls on Question and Answer - Part 3 of 3](#section-7)
- [28-Saving The Answer - Part 1 of 3](#section-8)
- [29-Saving The Answer - Part 2 of 3](#section-9)
- [30-Saving The Answer - Part 3 of 3](#section-10)

<a name="section-1"></a>

## Episode-21 Designing Answer Schema

`1` - Create new model `Answer` with migration file

```command
php artisan make:model Answer -m
```

`2` - Edit `database/migrations/2019_08_14_104146_create_answers_table.php`

```php
...
class CreateAnswersTable extends Migration
{
    public function up()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('user_id');
            $table->text('body');
            $table->integer('votes_count')->default(0);
            $table->timestamps();
        });
    }
}
...
```

`3` - Edit `app/Question.php` && `app/User.php`

```php
...
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
...
```

`4` - Edit `app/Answer.php`

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getBodyHtmlAttribute()
    {
        return \Parsedown::instance()->text($this->body);
    }
}
```

`5` - Edit `database/factories/QuestionFactory.php`

- renamed `answers` to `answers_count`

```php
$factory->define(Question::class, function (Faker $faker) {
    return [
        ...
        'answers_count' => rand(0, 10),
        ...
    ];
});
```

`6` - Edit `resources/views/questions/index.blade.php`

- renamed `answers` to `answers_count`

```php
...
<div class="status { { $question->status } }">
    <strong>{ { $question->answers_count } }</strong> { { str_plural('answer', $question->answers_count) } }
</div>
...
```

`7` - Edit `app/Policies/QuestionPolicy.php`

- renamed `answers` to `answers_count`

```php
...
public function delete(User $user, Question $question)
    {
        return $user->id === $question->user_id && $question->answers_count < 1;
    }
...
```

`8` - Edit `app/Question.php`

- renamed `answers` to `answers_count`

```php
...
public function getStatusAttribute()
    {
        if ($this->answers_count > 0) {
        ...
        }
    }
...
```

`9` - Create new migration file `rename_ansers_in_questions_table`

```command
php artisan make:migration rename_answers_in_questions_table --table=questions
```

`10` - Edit `database/migrations/2019_08_14_104146_create_answers_table.php`

```php
...
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('answers', 'answers_count');
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->renameColumn('answers_count', 'answers');
        });
    }
...
```

`11` - installing `doctrine/dbal` in order to rename db column

```command
composer require doctrine/dbal
```

<a name="section-2"></a>

## Episode-22 Generating Fake Answers - Part 1 of 2

`1` - Create new factory file `AnswerFactory`

```command
php artisan make:factory AnswerFactory
```

`2` - Edit `database/factories/AnswerFactory.php`

```php
<?php
use App\User;
use App\Answer;
use Faker\Generator as Faker;

$factory->define(Answer::class, function (Faker $faker) {
    return [
        'body' => $faker->paragraph(rand(3, 7), true),
        'user_id' => User::pluck('id')->random(),
        'votes_count' => rand(0, 5)
    ];
});
```

`3` - Edit `database/seeds/DatabaseSeeder.php`

```php
<?php

use App\Answer;
...
class DatabaseSeeder extends Seeder
{
    public function run()
    {
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

<a name="section-3"></a>

## Episode-23 Generating Fake Answers - Part 2 of 2

`1` - Edit `app/Answer.php`

```php
...
    public static function boot()
    {
        parent::boot();

        static::created(function ($answer) {
            $answer->question->increment('answers_count');
        });
    }
...
```

`2` - Edit `database/factories/QuestionFactory.php`

- commented `answers_count` column

```php
...
$factory->define(Question::class, function (Faker $faker) {
    return [
        ..
        // 'answers_count' => rand(0, 10),
        ...
    ];
});
```

<a name="section-4"></a>

## Episode-24 Displaying answers for question

`1` - Edit `resources/views/questions/show.blade.php`

```php
...
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
            ...
                  <div class="card-body">
                    { !! $question->body_html !! }
                    <div class="float-right">
                        <span class="text-muted">Answered: { {  $question->created_date } }</span>
                        <div class="media mt-2">
                            <a href="{ { $question->user->url } }" class="pr-2">
                                <img src="{ { $question->user->avatar } }" alt="avatar">
                            </a>
                            <div class="media-body mt-1">
                                <a href="{ { $question->user->url } }">{ { $question->user->name } }</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <h2>{ { $question->answers_count ." ". str_plural("Answer", $question->answers_count) } }</h2>
                    </div>
                    <hr>
                    @ foreach ($question->answers as $answer)
                    <div class="media">
                        <div class="media-body">
                            { !! $answer->body_html !! }
                            <div class="float-right">
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
                    <hr>
                    @ endforeach
                </div>
            </div>
        </div>
    </div>
</div>
```

`2` - Edit `app/User.php`

```php
...
public function getAvatarAttribute()
    {
        $email = $this->email;
        $size = 32;
        return "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?s=" . $size;
    }
...
```

`3` - Edit `app/Answer.php`

```php
...
 public function getCreatedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }
...
```

`4` - Edit `app/Providers/RouteServiceProvider.php`

- Eager loading `answers.user` in order to fix n+1 queries loading

```php
...
    public function boot()
    {
        Route::bind('slug', function ($slug) {
            return Question::with('answers.user')->where('slug', $slug)->first() ?? abort(404);
        });

        parent::boot();
    }
...
```

<a name="section-5"></a>

## Episode-25 Adding Vote Controls on Question and Answer - Part 1 of 3

`1` - Edit `resources/views/questions/show.blade.php`

```php
...
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <div class="d-flex align-items-center">
                            <h1>{ { $question->title } }</h1>
                            <div class="ml-auto">
                                <a href="{ { route('questions.index') } }" class="btn btn-outline-secondary">
                                    Back to all questions
                                </a>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="media">
                        <div class="d-flex flex-column vote-controls">
                            <a title="This question is useful" class="vote-up">
                            <i class="fas fa-caret-up fa-3x"></i>
                            </a>
                            <span class="vote-count">1234</span>
                            <a title="This question is not useful" class="vote-down off">
                                <i class="fas fa-caret-down fa-3x"></i>
                            </a>
                            <a title="Click to mark favorite question (Click agan to undo)" class="favorite mt-2 favorited">
                                <i class="fas fa-star fa-2x"></i>
                                <span class="favorites-count">123</span>
                            </a>
                        </div>
                        <div class="media-body">
                            { !! $question->body_html !! }
                            <div class="float-right">
                                <span class="text-muted">Answered: { {  $question->created_date } }</span>
                                <div class="media mt-2">
                                    <a href="{ { $question->user->url } }" class="pr-2">
                                        <img src="{ { $question->user->avatar } }" alt="avatar">
                                    </a>
                                    <div class="media-body mt-1">
                                        <a href="{ { $question->user->url } }">{ { $question->user->name } }</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="card-title">
                        <h2>{ { $question->answers_count ." ". str_plural("Answer", $question->answers_count) } }</h2>
                    </div>
                    <hr>
                    @ foreach ($question->answers as $answer)
                    <div class="media">
                        <div class="d-flex flex-column vote-controls">
                            <a title="This answer is useful" class="vote-up">
                            <i class="fas fa-caret-up fa-3x"></i>
                            </a>
                            <span class="vote-count">1234</span>
                            <a title="This answer is not useful" class="vote-down off">
                                <i class="fas fa-caret-down fa-3x"></i>
                            </a>
                            <a title="Mark this answer as best answer" class="vote-accept mt-2">
                                <i class="fas fa-check fa-2x"></i>
                            </a>
                        </div>
                    ..... // some codes
                    </div>
                    <hr>
                    @ endforeach
                </div>
            </div>
        </div>
    </div>
</div>
```

<a name="section-6"></a>

## Episode-26 Adding Vote Controls on Question and Answer - Part 2 of 3

`1` - Installing fontawesome using `npm`

```command
npm i @fortawesome/fontawesome @fortawesome/fontawesome-free-solid -D
```

`2` - Create new file `fontawesome.js` into `resources/js`

`3` - Edit `resources/js/fontawesome.js`

```js
import fontawesome from '@fortawesome/fontawesome';
import faCaretUp from '@fortawesome/fontawesome-free-solid/faCaretUp';
import faCaretDown from '@fortawesome/fontawesome-free-solid/faCaretDown';
import faStar from '@fortawesome/fontawesome-free-solid/faStar';
import faCheck from '@fortawesome/fontawesome-free-solid/faCheck';

fontawesome.library.add([faCaretUp, faCaretDown, faStar, faCheck]);

```

`4` - Edit `resources/js/app.js`

```js
...
require('./fontawesome')
...
```

<a name="section-7"></a>

## Episode-27 Adding Vote Controls on Question and Answer - Part 3 of 3

`1` - Edit `resources/sass/app.scss`

```css
...
.vote-controls {
    min-width: 60px;
    margin-right: 30px;
    text-align: center;
    color: $gray-700;

    span,
    a {
        display: block;
    }

    span {
        &.votes-count {
            font-size: 25px;
        }

        &.favorites-count {
            font-size: 12px;
        }
    }

    a {
        cursor: pointer;
        color: $gray-600;

        &.off,
        &.off:hover {
            color: $gray-500;
        }

        &.favorite {

            &.favorited,
            &.favorited:hover {
                color: $orange;
            }
        }

        &.vote-accept {
            color: $gray-300;
        }

        &.vote-accepted {
            color: $green;
        }
    }
}
...
```

`2` - run npm command

```command
npm run watch
```

<a name="section-8"></a>

## Episode-28 Saving The Answer - Part 1 of 3

`1` - Edit `resources/views/questions/show.blade.php`

```php
...
<div class="container">
    <div class="row justify-content-center">
    ...
    </div>
    @ include('answers._index',[
        'answers' => $question->answers,
        'answersCount' => $question->answers_count
    ])
</div>
...
```

`2` - Create new folder `answers` into `resources/views`

`3` - Create new file `_index.blade.php` into `resources/views/answers`

`4` - Edit `resources/views/answers/_index.blade.php`

```php
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h2>{ { $answersCount ." ". str_plural("Answer", $answersCount) } }</h2>
                </div>
                <hr>
                @ foreach ($answers as $answer)
                <div class="media">
                    <div class="d-flex flex-column vote-controls">
                        <a title="This answer is useful" class="vote-up">
                            <i class="fas fa-caret-up fa-3x"></i>
                        </a>
                        <span class="vote-count">1234</span>
                        <a title="This answer is not useful" class="vote-down off">
                            <i class="fas fa-caret-down fa-3x"></i>
                        </a>
                        <a title="Mark this answer as best answer" class="vote-accept mt-2">
                            <i class="fas fa-check fa-2x"></i>
                        </a>
                    </div>
                    <div class="media-body">
                        { !! $answer->body_html !! }
                        <div class="float-right">
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
                <hr>
                @ endforeach
            </div>
        </div>
    </div>
</div>
```

<a name="section-9"></a>

## Episode-29 Saving The Answer - Part 2 of 3

`1` - Create new file `_create.blade.php` into `resources/views/answers`

`2` - Edit `resources/views/answers/_create.blade.php`

```php
<div class="row mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="card-title">
                    <h2>Your Answer</h2>
                </div>
                <hr>
                <form method="post" action="">
                    @ csrf
                    <div class="form-group">
                        <textarea class="form-control" name="body" rows="7"></textarea>
                    </div>
                    <div class="form-group">
                    <button type="submit" class="btn btn-outline-primary btn-lg">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
```

`3` - Edit `resources/views/questions/show.blade.php`

```php
<div class="container">
...
@ include('answers._create')
</div>
...
```

`4` - Edit `routes/web.php`

```php
...
Route::resource('questions.answers', 'AnswerController')->except(['index', 'create', 'show']);
...
```

`5` - Create new controller `AnswerController`

```command
php artisan make:controller AnswerController -r -m Answer
```

`6` - Edit `app/Http/Controllers/AnswerController.php`

```php
use App\Question;
...
  public function store(Question $question, Request $request)
    {
        $question->answers()->create($request->validate([
            'body' => 'required'
        ]) + [
            'user_id' => \Auth::id()
        ]);
        return back()->with('success', 'Your answer has been submitted successfuly');
    }
...
```

<a name="section-10"></a>

## Episode-30 Saving The Answer - Part 3 of 3

`1` - Edit `resources/views/answers/_index.blade.php`

```php
...
<div class="card">
    @ include('layouts._message') //include flash message file
...
</div>
...
```

`2` - Edit `resources/views/answers/_create.blade.php`

```php
...
<form method="post" action="{ { route('questions.answers.store', $question->id) } }">
@ csrf
<div class="form-group">
    <textarea class="form-control { { $errors->has('body') ? 'is-invalid' : '' } }" name="body" rows="7"></textarea>
    @ if($errors->has('body'))
        <div class="invalid-feedback">
            <strong>{ { $errors->first('body') } }</strong>
        </div>
    @ endif
</div>
...
</form>
...
```

`3` - Edit `app/Answer.php`

```php
...
protected $fillable = ['body', 'user_id'];
...
```