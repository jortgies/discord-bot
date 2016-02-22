<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Validator;
use Illuminate\Http\Request;


class FileController extends Controller
{
    public function upload(Request $file)
    {
        $validator = Validator::make($file->all(), [
           'file' => 'required|mimes:mpga'
        ]);
        if($validator->fails()) {
            return redirect('upload')->withErrors($validator);
        }
        else
        {
            $file = Input::file('file');
            $exists = Storage::disk('local')->exists($file->getClientOriginalName());
            if($exists) {
                return redirect('upload')->withErrors('Es existiert bereits eine Datei mit diesem Namen.');
            }
            else {
		$file->move('uploads', $file->getClientOriginalName());
                return view('upload', ['success' => $file->getClientOriginalName()]);
            }
        }
    }

    public function listFiles()
    {

        $files = Storage::files();
        return view('list', ['allFiles' => $files]);
    }
}
