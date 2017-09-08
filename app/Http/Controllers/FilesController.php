<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{

    public function __construct ()
    {
        $this->middleware('auth' );
    }

    public function download ( Request $request )
    {

        $file = File::find( $request->get( 'id' ) );
        if ( ! $file )
        {
            return redirect()->back()->withErrors( [ 'Файл не найден' ] );
        }

        if ( $file->getToken() != $request->get( 'token' ) )
        {
            return redirect()->back()->withErrors( [ 'Неверный токен' ] );
        }

        try
        {
            $path = storage_path( 'app/' . $file->path );
            return response()->download( $path, $file->name );
        }
        catch ( \Exception $e )
        {
            return redirect()->back()->withErrors( $e->getMessage() );
        }

    }
	
}
