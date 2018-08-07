<?php

namespace App\Classes;

class Segments
{

    protected $cache_tags = 'segments';
    protected $cache_life = 120;

    public function clearCache ()
    {
        \Cache::tags( $this->cache_tags )->flush();
    }

}