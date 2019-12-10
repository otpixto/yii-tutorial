<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Support\MessageBag;

trait CommentsTrait
{

    public function comments ()
    {
        return $this->hasMany( 'App\Models\Comment', 'model_id' )
            ->where( 'model_name', '=', static::class );
    }

    public function addComment ( $text, $color = null )
    {
        if ( ! isset( $this->id ) )
        {
            return null;
        }
        $comment = Comment::create([
            'model_id'      => $this->id,
            'model_name'    => static::class,
            'text'          => $text,
        ]);
        if ( $comment instanceof MessageBag )
        {
            return $comment;
        }
        $comment->color = $color;
        $comment->save();
        if ( method_exists( $this, 'addLog' ) )
        {
            $log = $comment->addLog( 'Добавлен комментарий' );
            if ( $log instanceof MessageBag )
            {
                return $log;
            }
        }
        return $comment;
    }

}
