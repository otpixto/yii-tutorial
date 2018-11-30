<?php

namespace App\Traits;

use App\Models\Tag;
use Illuminate\Support\MessageBag;

trait TagsTrait
{

    public function tags ()
    {
        return $this->hasMany( 'App\Models\Tag', 'model_id' )
            ->where( 'model_name', '=', static::class );
    }

    public function addTag ( $text )
    {
        if ( ! isset( $this->id ) )
        {
            return null;
        }
        $tag = Tag::create([
            'model_id'      => $this->id,
            'model_name'    => static::class,
            'text'          => $text,
        ]);
        if ( $tag instanceof MessageBag )
        {
            return $tag;
        }
        $tag->save();
        if ( method_exists( $this, 'addLog' ) )
        {
            $log = $tag->addLog( 'Добавлен тег' );
            if ( $log instanceof MessageBag )
            {
                return $log;
            }
        }
        return $tag;
    }

}
