<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Larviu\Models\StorageFile;

/**
 * Class LaravueController
 *
 * @package App\Http\Controllers
 */
class LarviuController extends Controller
{
    /**
     * Entry point for Laravue Dashboard
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('larviu');
    }
    /**
     * Get file URL
     *
     * @param $disk
     * @param $path
     *
     * @return array
     */
    public function url(Request $request)
    {
    	$path = explode('/', $request->path);
    	$filename = last($path);
    	$file_record = StorageFile::where('name',$filename)->whereNull('type')->first();
        $extension = substr(strstr($filename, '.'), 1);

    	if (!$file_record) {
        	$file_record = StorageFile::create([
                'name' => $filename,
                'url' => 'api/getFile?disk='.env('FILESYSTEM_DRIVER', 'local').'&path='.urlencode($request->path),
                'extension' => $extension,
            ]);
    	}
        return [
            'result' => [
                'status'  => 'success',
                'message' => null,
                'data'	  => $file_record
            ],
        ];
    }
}
