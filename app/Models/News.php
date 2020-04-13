<?php

namespace App\Models;

class News extends BaseModel
{

    protected $table = 'news';
    public static $_table = 'news';

    public static $name = 'Новости';

    public static $date_format = 'd.m.Y H:i';

    public static $types = [
        1 => 'Новость',
        2 => 'Обращение',
        3 => 'Работа на сетях'
    ];

    public static $rules = [
        'type_id'           => 'required|integer',
        'title'             => 'required|string|max:255',
        'body'              => 'required|string',
    ];

    protected $fillable = [
        'type_id',
        'title',
        'body',
    ];

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function scopeMine ( $query )
    {
        $query
            ->mineProvider();
    }

    public function getType ()
    {
        return self::$types[ $this->type_id ] ?? null;
    }

    public function getClass ( $side = 'left' )
    {
        switch ( $this->type_id )
        {

            case 1:
                return 'mt-content-container bg-blue-chambray bg-font-blue-chambray border-blue-chambray border-' . $side . '-before-blue-chambray';
            break;

            case 2:
                return 'mt-content-container bg-blue bg-font-blue border-blue border-' . $side . '-before-blue';
            break;

            case 3:
                return 'mt-content-container bg-red bg-font-red border-' . $side . '-before-red border-red';
            break;

            default:
                return '';
            break;

        }
    }

    public function getIconNew ()
    {
        switch ( $this->type_id )
        {

            case 1:
                return '<div class="timeline-icon bg-blue-chambray bg-font-blue-chambray border-grey-steel"><i class="icon-bubbles"></i></div>';
            break;

            case 2:
                return '<div class="timeline-icon bg-blue bg-font-blue border-grey-steel"><i class="icon-call-in"></i></div>';
            break;

            case 3:
                return '<div class="timeline-icon bg-red bg-font-red border-grey-steel"><i class="icon-globe"></i></div>';
            break;

            default:
                return '';
            break;

        }
    }

    public function getIcon ()
    {
        switch ( $this->type_id )
        {

            case 1:
                return '<div class="mt-timeline-icon bg-blue-chambray bg-font-blue-chambray border-grey-steel"><i class="icon-bubbles"></i></div>';
            break;

            case 2:
                return '<div class="mt-timeline-icon bg-blue bg-font-blue border-grey-steel"><i class="icon-call-in"></i></div>';
            break;

            case 3:
                return '<div class="mt-timeline-icon bg-red bg-font-red border-grey-steel"><i class="icon-globe"></i></div>';
            break;

            default:
                return '';
            break;

        }
    }

    public function getDateTime ( $date_format = null )
    {
        if ( is_null( $date_format ) )
        {
            $date_format = self::$date_format;
        }
        if ( $this->datetime )
        {
            return date( $date_format, strtotime( $this->datetime ) );
        }
        else
        {
            return $this->created_at->format( $date_format );
        }
    }

}
