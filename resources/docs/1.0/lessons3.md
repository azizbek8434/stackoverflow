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
<div class="status {{ $question->status }}">
    <strong>{{ $question->answers_count }}</strong> {{ str_plural('answer', $question->answers_count) }}
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