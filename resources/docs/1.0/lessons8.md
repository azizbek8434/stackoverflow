# Episodes from 55 to 64

- [65-Creating Favorite Component - Part 2 of 3 (Event Handler)](#section-1)
- [66-Creating Favorite Component - Part 3 of 3 (Authenticating the button)](#section-2)
- [67-Creating Accept Answer Component - Part 1 of 2 (from button into Vue component)](#section-3)
- [68-Creating Accept Answer Component - Part 2 of 2 (event handler)](#section-4)
- [69-Rewriting The Authorization Logic - Part 1 of 2 (Core authorization)](#section-5)
- [70-Rewriting The Authorization Logic - Part 2 of 2 (Refactoring)](#section-6)
- [71-Creating Vote Component - Part 1 of 3 (From blade to Vue Component)](#section-7)
- [72-Creating Vote Component - Part 2 of 3 (Event Handling)](#section-8)
- [73-Creating Vote Component - Part 3 of 3 (Fixing issues)](#section-9)
- [74-Creating Vue Answers Component](#section-10)

<a name="section-1"></a>

## Episode-65 Creating Favorite Component - Part 2 of 3 (Event Handler)

`1` - Edit `resources/js/components/Favorite.vue`

```html
<template>
  <a title="Click to mark favorite question (Click agan to undo)"
    :class="classes"
    @click.prevent="toggle"
  >
...
</template>
```

js part

```js
...
export default {
  props: ["question"],
  data() {
    return {
      isFavorited: this.question.is_favorited,
      count: this.question.favorites_count,
      signedIn: true,
      id: this.question.id
    };
  },
  computed: {
    classes() {
      return [
        "favorite",
        "mt-2",
        !this.signedIn ? "off" : this.isFavorited ? "favorited" : ""
      ];
    },
    endpoint() {
      return `/questions/${this.id}/favorites`;
    }
  },
  methods: {
    toggle() {
      if (!this.signedIn) {
        this.$toast.warning(
          "Please login to favorite this question",
          "Warning",
          {
            timeout: 3000,
            position: "bottomLeft"
          }
        );
        return;
      }
      this.isFavorited ? this.destroy() : this.create();
    },
    destroy() {
      axios.delete(this.endpoint).then(res => {
        this.count--;
        this.isFavorited = false;
        this.$toast.success("Removed from favorites", "Success", {
          timeout: 3000
        });
      });
    },
    create() {
      axios.post(this.endpoint).then(res => {
        this.count++;
        this.isFavorited = true;
        this.$toast.success("Added to favorites", "Success", {
          timeout: 3000
        });
      });
    }
  }
};
...
```

`2` - Edit `app/Http/Controllers/FavoritesController.php`

```php
...
public function store(Question $question)
{
    $question->favorites()->attach(auth()->id());

    if (request()->expectsJson()) {
        return response()->json(null, 204);
    }
    return back();
}

public function destroy(Question $question)
{
    $question->favorites()->detach(auth()->id());
    if (request()->expectsJson()) {
        return response()->json(null, 204);
    }
    return back();
}
...
```

<a name="section-2"></a>

## Episode-66 Creating Favorite Component - Part 3 of 3 (Authenticating the button)

`1` - `resources/views/layouts/app.blade.php`

```php
...
<body>
...
<script>
  window.Auth = { !! json_encode([
    'signedIn' => Auth::check(),
    'user' => Auth::user()
]) !! }
</script>
  <script src="{ { asset('js/app.js') } }"></script>
</body>
...
```

`2` - Edit `resources/js/components/Favorite.vue`

```js
...
data() {
  return {
    isFavorited: this.question.is_favorited,
    count: this.question.favorites_count,
    id: this.question.id
  };
},
computed: {
  ...,
  signedIn() {
    return window.Auth.signedIn;
  }
}
...
```

<a name="section-3"></a>

## Episode-67 Creating Accept Answer Component - Part 1 of 2 (from button into Vue component)

`1` - Create new `Accept.vue` file into `resources/js/components`

`2` - Edit `resources/js/components/Accept.vue`

```html
<template>
  <div>
    <a title="Mark this answer as best answer"
      :class="classes"
      v-if="canAccept">
      <i class="fas fa-check fa-2x"></i>
    </a>
    <a title="The question owner accepted this answer as best answer"
      :class="classes"
      v-if="accepted">
      <i class="fas fa-check fa-2x"></i>
    </a>
  </div>
</template>
<script>
export default {
  props: ["answer"],
  data() {
    return {
      isBest: this.answer.is_best
    };
  },
  computed: {
    canAccept() {
      return true;
    },
    accepted() {
      return !this.canAccept && this.isBest;
    },
    classes() {
      return ["mt-2", this.isBest ? "vote-accepted" : "vote-accept"];
    }
  }
};
</script>
```

`3` - Edit `resources/js/app.js`

```js
...
Vue.component('accept-component', require('./components/Accept.vue').default);
...
```

`4` - `resources/views/shared/_vote.blade.php`

```php
...
@ include('shared._accept',[
  'model' => $model
])
...
```

change to

```php
...
<accept-component :answer="{ { $model } }"></accept-component>
...
```

`5` - Edit `app/Answer.php`

```php
...
protected $appends = ['created_date', 'body_html', 'is_best'];
...
```

<a name="section-4"></a>

## Episode-68 Creating Accept Answer Component - Part 2 of 2 (event handler)

`1` - Edit `resources/js/components/Accept.vue`

- add to button `create` method

```html
...
<a title="Mark this answer as best answer"
  :class="classes"
  v-if="canAccept"
  @click.prevent="create"
>
...
```

js part

```js
...,
data() {
  return {
    isBest: this.answer.is_best,
    id: this.answer.id
  };
},
...,
methods: {
    create() {
      axios.post(`/answers/${this.id}/accept`).then(res => {
        this.$toast.success(res.data.message, "Success", {
          timeout: 3000,
          position: "bottomLeft"
        });
        this.isBest = true;
      });
    }
  }
...
```

`2` - Edit `app/Http/Controllers/AcceptAnswerController.phpa`

```php
...
public function __invoke(Answer $answer)
{
  ...
    if (request()->expectsJson()) {
        return response()->json([
            'message' => 'You have accepted this answer as best answer'
        ]);
    }
  ...
}
...
```


<a name="section-5"></a>

## Episode-69 Rewriting The Authorization Logic - Part 1 of 2 (Core authorization)

`1` - Create new file `policies.js` into `resources/js`

```js
export default {
    modify(user, model) {
        return user.id === model.user_id;
    },
    accept(user, answer) {
        return user.id === answer.question.user_id;
    }
}
```

`2` - Edit `resources/js/app.js`

```js
...
import policies from './policies'

Vue.prototype.authorize = function (policy, model) {
    if (!window.Auth.signedIn) return false;
    if (typeof policy === 'string' && typeof model === 'object') {
        const user = window.Auth.user;
        return policies[policy](user, model);
    }
}
...
```

`3` - Edit `resources/js/components/Accept.vue`

```js
...
computed: {
  canAccept() {
    return this.authorize("accept", this.answer);
  },
  ...
}
...
```

