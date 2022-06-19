<?php

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\File;
use Spatie\YamlFrontMatter\YamlFrontMatter;

class Post
{

    public $title;
    public $excerpt;
    public $date;
    public $body;
    public $slug;

    public function __construct($title, $excerpt, $date, $body, $slug)
    {
        $this->title = $title;
        $this->excerpt = $excerpt;
        $this->date = $date;
        $this->body = $body;
        $this->slug = $slug;
    }

    public static function all()
    {
        return cache()->remember('posts.all', 60, function () {
            $files = File::files(resource_path("posts"));

            $document = collect($files)->map(function ($file) {
                return YamlFrontMatter::parseFile($file);
            });

            $posts = collect($document)
                ->map(function ($document) {

                    return new Post(
                        $document->title,
                        $document->excerpt,
                        $document->date,
                        $document->body(),
                        $document->slug
                    );
                });


            return $posts->sortByDesc('date');
        });
    }

    public static function find($slug)
    {
        $post = static::all()->firstWhere('slug', $slug);
        
        
        return $post;
    }



    public static function findOrFail($slug)
    {
        $post = static::find($slug);

        if (! $post){
            throw new ModelNotFoundException();
        }

        
        
        return $post;
    }
}
