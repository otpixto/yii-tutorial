<?php

namespace App\Interfaces;

interface Authorizable
{
    public function can ( $permission ) : bool;
    public function canOne ( ... $permissions ) : bool;
    public function cant ( $permission ) : bool;
    public function cannot ( $permission ) : bool;
}
