<?php

namespace App;

use App\Traits\VotableTrait;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use VotableTrait;

    protected $appends = ['created_date', 'is_favorited', 'favorites_count'];

    protected $fillable = [
        'title',
        'body',
    ];

    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = str_slug($value);
    }

    public function getUrlAttribute()
    {
        return route("questions.show", $this->slug);
    }

    public function getCreatedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    public function getStatusAttribute()
    {
        if ($this->answers_count > 0) {
            if ($this->best_answered_id) {
                return 'answered-accepted';
            }
            return "answered";
        }
        return "unanswered";
    }

    public function getBodyHtmlAttribute()
    {
        return clean($this->bodyHtml());
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

    public function acceptBestAnswer($answer)
    {
        $this->best_answer_id = $answer->id;
        $this->save();
    }

    public function answers()
    {
        return $this->hasMany(Answer::class)->orderBy('votes_count', 'DESC');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

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
}
