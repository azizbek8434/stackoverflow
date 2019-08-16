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
        dd($question->id);
        $question->favorites()->attach(auth()->id());

        return back();
    }

    public function destroy(Question $question)
    {
        dd($question->id);
        $question->favorites()->detach(auth()->id());
        return back();
    }
}
