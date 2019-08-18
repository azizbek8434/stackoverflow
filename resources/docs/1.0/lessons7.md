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

`3` - Edit `resources/js/app.js`

```js
require('./bootstrap');

require('./fontawesome')

window.Vue = require('vue');

Vue.component('user-info', require('./components/UserInfo.vue').default);

const app = new Vue({
    el: '#app',
});
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

<a name="section-3"></a>

## Episode-57 Creating Vue Answer Component - Part 1 of 5 (Using Vue.js Inline Template)

`1` - Create new file `Answer.vue` into `resources/js/components`

`2` - Edit `resources/js/components/Answer.vue`

```html
<script>
export default {
  props: ["answer"]
};
</script>
```

`3` - Edit `resources/js/app.js`

```js
...
Vue.component('answer-component', require('./components/Answer.vue').default);
...
```

`4` - Edit `resources/views/answers/_answer.blade.php`

```php
<answer-component  :answer="{ { $answer } }" inline-template>
  ...
</answer-component>
```

<a name="section-4"></a>

## Episode-58 Creating Vue Answer Component - Part 2 of 5 (Ajaxifying The Edit button)

`1` - Edit `resources/views/answers/_answer.blade.php`

```php
...
<form v-if="editing"> 
  Edit answer form
  <button @ click="editing = false">Update</button>
</form>
<div v-else>
  ...
@ can('update', $answer)
  <a @ click.prevent="editing = true" class="btn btn-outline-info btn-sm">Edit</a>
@ endcan
  ...
</div>
...
```

`2` - Edit `resources/js/components/Answer.vue`

```html
...
<script>
export default {
  props: ["answer"],
  data() {
    return {
      editing: false
    };
  }
};
</script>
...
```

<a name="section-5"></a>

## Episode-59 Creating Vue Answer Component - Part 3 & 4 of 5 (Build Answer Inline Form) & (Undoing changes)

`1` - Edit `resources/views/answers/_answer.blade.php`

```php
...
<form v-if="editing" @ submit.prevent="update">
  <div class="form-group">
      <textarea  class="form-control" v-model="body" rows="10"></textarea>
  </div>
  <div class="form-group">
      <button type="submit" class="btn btn-outline-primary">Update</button>
      <button type="button" class="btn btn-outline-default" @ click="cancel">Cancel</button>
  </div>
</form>
<div v-else>
  <div v-html="bodyHtml"></div>
  ...
      @ can('update', $answer)
      <a @ click.prevent="edit"
          class="btn btn-outline-info btn-sm">Edit</a>
      @ endcan
      ...
</div>
...
```

<a name="section-6"></a>

`2` - Edit `resources/js/components/Answer.vue`

```js
...
export default {
  props: ["answer"],
  data() {
    return {
      editing: false,
      id: this.answer.id,
      body: this.answer.body,
      bodyHtml: this.answer.body_html,
      questionId: this.answer.question_id,
      beforeEditCache: null
    };
  },
  methods: {
    edit() {
      this.beforeEditCache = this.body;
      this.editing = true;
    },
    cancel() {
      this.body = this.beforeEditCache;
      this.editing = false;
    },
    update() {
      axios
        .patch(`/questions/${this.questionId}/answers/${this.id}`, {
          body: this.body
        })
        .then(response => {
          this.bodyHtml = response.data.body_html;
          this.editing = false;
        })
        .catch(errors => {
          console.log(errors.body[0]);
        });
    }
  }
};
...
```

`3` - Edit `app/Answer.php`

- add to appands array `body_html` accessor

```php
...
protected $appends = ['created_date', 'body_html'];
...
```

`4` - Edit `app/Http/Controllers/AnswerController.php`

```php
...
public function update(Request $request, Question $question, Answer $answer)
{
    ...

    if ($request->expectsJson()) {
        return response()->json([
            'message' => 'Your answer has been updated',
            'body_html' => $answer->body_html
        ], 200);
    }
    ...
}
...
```

`5` - Edit `resources/views/answers/_index.blade.php`

- add `v-cloak` while vue instance is loading holds hidden

```php
...
<div class="row mt-4" v-cloak>
...
```

`6` - Edit `resources/sass/app.scss`

```css
...
[v-cloak] {
    display: none
}
...
```
