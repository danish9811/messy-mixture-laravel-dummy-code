<?php

namespace App\Http\Controllers;

use App\Models\{Post, Tag, User};
use App\Traits\{Paths, Utils};

class PractController extends Controller {
    use Utils, Paths;

    public function __construct() {

    }

    public function index() {
        $user = User::find(4);
        $user->roles()->sync([2, 3]);
        dd($user->roles);
    }


    // https://youtu.be/JQ01o10Mva4
    public function qiroLabManyToMany(): string { // endpoint : qiro-many-to-many

        $post = Post::with('tags')->first();

        // attach post with multiple tags
        $post->tags()->detach();

        // $post->tags()->attach([1,1]);
        // $post->tags()->attach(1);

        // the inverse relation has not created yet
        // $tag = Tag::with('posts')->first();
        // $tag = Tag::where('id', 1)->get();
        // $post->tags()->attach($tag);
        $tag = Tag::first();
        dd($post);

        return __METHOD__;
        // return view('qirolab-many-to-many');
    }

    public function qiroOneToOne() {
        return __METHOD__;
    }

    public function qiroOneToMany() {
        return __METHOD__;
    }

    public function checkPath() {
        dd(is_file($this->countriesJson));
    }
}
