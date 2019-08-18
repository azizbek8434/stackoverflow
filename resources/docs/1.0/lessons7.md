# Episodes from 55 to 64

- [55-Creating our first Vue.js Component - Part 1 of 2 (Building The Component)](#section-1)
- [56-Creating our first Vue.js Component - Part 2 of 2 (Using The Component)](#section-2)
- [57-Creating Vue Answer Component - Part 1 of 5 (Using Vue.js Inline Template)](#section-3)
- [58-Creating Vue Answer Component - Part 2 of 5 (Ajaxifying The Edit button)](#section-4)
- [59-Creating Vue Answer Component - Part 3 of 5 (Build Answer Inline Form)](#section-5)
- [60-Creating Vue Answer Component - Part 4 of 5 (Undoing changes)](#section-6)
- [61-Creating Vue Answer Component - Part 5 of 5 (Validation)](#section-7)
- [62-Ajaxifying The Delete Button](#section-8)
- [63-Beautifying The Flash & Confirm messages](#section-9)
- [64-Creating Favorite Component - Part 1 of 3 (From button to Vue.js Component))](#section-10)

<a name="section-1"></a>

## Episode-55 Creating our first Vue.js Component - Part 1 of 2 (Building The Component)

`1` - Create new file `UserInfo.vue`  into `resources/js/components`

`2` - Edit `resources/js/components/UserInfo.vue`

```html
<template>
  <div>
    <span class="text-muted">{ { postDate } }</span>
    <div class="media mt-2">
      <a :href="user.url " class="pr-2">
        <img :src="user.avatar" alt="avatar" />
      </a>
      <div class="media-body mt-1">
        <a :href="user.url">{ { user.name } }</a>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ["model", "label"],
  computed: {
    postDate() {
      return this.label + " " + this.model.created_date;
    }
  },
  data() {
    return {
      user: this.model.user
    };
  }
};
</script>
```

<a name="section-2"></a>

## Episode-56 Creating our first Vue.js Component - Part 2 of 2 (Using The Component)

`1` - Edir `resources/views/questions/show.blade.php`

```php
...
@ include('shared._author',[
    'model' => $question,
    'label' => 'Asked'
])
...
```

change to

```php
...
<user-info :model="{ { $question } }" :label="Asked"></user-info>
...
```

`2` - Edit `app/Providers/RouteServiceProvider.php`

- added eager loading `Question` to `user`

```php
...
public function boot()
    {
        Route::bind('slug', function ($slug) {
            return Question::with('user', 'answers.user')->where('slug', $slug)->first() ?? abort(404);
        });
...
```

`3` - Edit `app/User.php`

```php
...
protected $appends = ['url', 'avatar'];
...
```

`4` - Edit `app/Question.php`

```php
...
protected $appends = ['created_date'];
...
```

`5` - Edit `resources/views/answers/_answer.blade.php`

```php
...
@ include('shared._author', [
    'model' => $answer,
    'label' => 'Answered'
])
...
```

change to

```php
...
<user-info :model="{ { $answer } }" label="Answered"></user-info>
...
```

`6` Edit `app/Answer.php`

```php
...
protected $appends = ['created_date'];
...
```
