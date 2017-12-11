<?php

namespace App\Http\Controllers;

use App\Classes\Title;
use App\Models\News;

class NewsController extends Controller
{

    public function __construct ()
    {
        Title::add( 'Новости ЕДС' );
    }

    public function index ()
    {
        $news = News
            ::mine()
            ->orderBy( 'datetime', 'desc' )
            ->paginate( 30 );
        return view( 'news.index' )
            ->with( 'news', $news );
    }

    public function rss ()
    {

        $news = News
            ::mine()
            ->orderBy( 'datetime', 'desc' )
            ->take( 30 )
            ->get();

        $xml = '<?xml version="1.0"?>
            <rss version="2.0">
                <channel>
                   <title>' . \Config::get( 'app.name' ) . '</title>
                   <link>' . \Session::get( 'settings' )->news_domain . '</link>
                   <description>Новости ЕДС ЖКХ</description>';

        foreach ( $news as $r )
        {
            $body_text = strip_tags( $r->body );
            preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $r->body, $media );
            $data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0] );
            foreach ( $data as $src )
            {
                $body_text .= PHP_EOL . '<![CDATA[<img src="http://' . \Session::get( 'settings' )->news_domain . $src . '" />]]>';
            }
            $xml .= '
                <item>
                    <title>' . $r->title . '</title>
                    <link>' . route( 'news.show', $r->id ) . '</link>
                    <description>' . $body_text . '</description>
                    <author>' . $r->author->getName() . '</author>
                    <pubDate>' . $r->getDateTime( 'r' ) . '</pubDate>
                </item>';
        }

        $xml .= '
           </channel>
        </rss>';

        $xml = str_replace( [ "\r\n", "\r", "\n", "\t", '  ', '    ', '    ' ], '', $xml );

        return response( $xml )
            ->header( 'Content-Type', 'Content-Type: application/rss+xml' );

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show ( $id )
    {

        /*if ( \Auth::user()->can( 'works.edit' ) )
        {
            return redirect()->route( 'works.edit', $id );
        }*/

        $news = News::find( $id );

        if ( ! $news )
        {
            return redirect()->route( 'error.404' )->withErrors(['Новость не найдена']);
        }

        Title::add( $news->title );

        return view( 'news.show' )
            ->with( 'news', $news );

    }

}