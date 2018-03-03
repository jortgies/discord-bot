<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Exception;
use Validator;
use Illuminate\Http\Request;
use App\MP3File;
use YoutubeDl\YoutubeDl;


class FileController extends Controller
{
    public function upload(Request $file)
    {
        $validator = Validator::make($file->all(), [
           'file' => 'required|mimes:mpga,mp3'
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
                Storage::disk('local')->put($file->getClientOriginalName(), \File::get($file));
                return view('upload', ['success' => $file->getClientOriginalName()]);
            }
        }
    }

    public function uploadYoutube(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'link' => 'required|url'
        ]);

        if($validator->fails())
            return back()->withErrors($validator)->withInput();

        $dl = new YoutubeDl([
            'extract-audio' => true,
            'audio-format' => 'mp3',
            'audio-quality' => 0, // best
            'output' => '%(title)s.%(ext)s',
        ]);
        $dl->setDownloadPath(Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix());

        $video = $dl->download($request->input('link'));
        return view('upload', ['success' => sprintf('"%s" was successfully downloaded and converted to mp3.', $video->getFulltitle())]);
    }

    public function listFiles()
    {
        $files = Storage::files();
        $file_array = [];
        $storagePath  = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
        foreach($files as $k => $v)
        {
            $length = null;
            if(env('ENABLE_LENGTH_DETECTION', false)) {
                $mp3 = new MP3File($storagePath.DIRECTORY_SEPARATOR.$v);
                $length = MP3File::formatTime($mp3->getDuration());
            }
            $waveform = null;
            if(env('ENABLE_WAVEFORM', false)) {
                dd(env('ENABLE_WAVEFORM'));
                if(!$this->fileHasWaveform($v)) {
                    $waveform = 'uploads/waveform/'.str_slug($v).'_w.png';
                    $this->generateWaveform($v);
                }
            }
            $file_array[] = ['id' => $k, 'name' => $v, 'length' => $length, 'waveform' => $waveform];
        }
        return view('list', ['allFiles' => $file_array]);
    }

    public function renameFile($oldFilename, $newFilename) {
        Storage::disk('local')->move($oldFilename, $newFilename);
        return back()->with(['status' => 'Successfully renamed file.']);
    }

    public function deleteFile($filename) {
        Storage::disk('local')->delete($filename);
        return back()->with(['status' => 'Successfully deleted file.']);
    }

    public function fileHasWaveform($filename)
    {
        if(Storage::disk('local')->exists('waveform'.DIRECTORY_SEPARATOR.str_slug($filename)."_w.png"))
            return true;
        else
            return false;
    }

    function findValues($byte1, $byte2){
        $byte1 = hexdec(bin2hex($byte1));
        $byte2 = hexdec(bin2hex($byte2));
        return ($byte1 + ($byte2*256));
    }

    function html2rgb($input) {
        $input=($input[0]=="#")?substr($input, 1,6):substr($input, 0,6);
        return array(
            hexdec(substr($input, 0, 2)),
            hexdec(substr($input, 2, 2)),
            hexdec(substr($input, 4, 2))
        );
    }

    public function generateWaveform($filename, $stereo = true, $draw_flat = true)
    {
        ini_set("max_execution_time", "500");
        $detail = 5;
        $width = 180;
        $height = 60;
        $foreground = "#FF0000";
        $background = "#FFFFFF";
        $filename_w = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix().'waveform'.DIRECTORY_SEPARATOR.str_slug($filename).'_w.png';
        Storage::makeDirectory('waveform');

        $tmpname = substr(md5(time()), 0, 10);
        $tmpfile = Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix()."temp".DIRECTORY_SEPARATOR.$tmpname;

        Storage::disk('local')->copy($filename, "temp".DIRECTORY_SEPARATOR.$tmpname."_o.mp3");

        // array of wavs that need to be processed
        $wavs_to_process = array();

        if ($stereo) {
            // scale right channel down (a scale of 0 does not work)
            exec("lame {$tmpfile}_o.mp3 --scale-r 0.1 -m m -S -f -b 16 --resample 8 {$tmpfile}.mp3 && lame -S --decode {$tmpfile}.mp3 {$tmpfile}_l.wav");
            // same as above, left channel
            exec("lame {$tmpfile}_o.mp3 --scale-l 0.1 -m m -S -f -b 16 --resample 8 {$tmpfile}.mp3 && lame -S --decode {$tmpfile}.mp3 {$tmpfile}_r.wav");
            $wavs_to_process[] = "{$tmpfile}_l.wav";
            $wavs_to_process[] = "{$tmpfile}_r.wav";

            if(!file_exists($tmpfile."_l.wav"))
                touch($tmpfile."_l.wav");
            if(!file_exists($tmpfile."_r.wav"))
                touch($tmpfile."_r.wav");
        } else {
            exec("lame {$tmpfile}_o.mp3 -m m -S -f -b 16 --resample 8 {$tmpfile}.mp3 && lame -S --decode {$tmpfile}.mp3 {$tmpfile}.wav");
            $wavs_to_process[] = "{$tmpfile}.wav";
        }

        if(Storage::disk('local')->exists('temp'.DIRECTORY_SEPARATOR.$tmpname.'_o.mp3')) Storage::disk('local')->delete('temp'.DIRECTORY_SEPARATOR.$tmpname.'_o.mp3');
        if(Storage::disk('local')->exists('temp'.DIRECTORY_SEPARATOR.$tmpname.'.mp3')) Storage::disk('local')->delete('temp'.DIRECTORY_SEPARATOR.$tmpname.'.mp3');

        $img = false;

        list($r, $g, $b) = $this->html2rgb($foreground);

        for($wav = 1; $wav <= sizeof($wavs_to_process); $wav++) {

            $filename = $wavs_to_process[$wav - 1];

            $handle = fopen($filename, "r");
            // wav file header retrieval
            $heading[] = fread($handle, 4);
            $heading[] = bin2hex(fread($handle, 4));
            $heading[] = fread($handle, 4);
            $heading[] = fread($handle, 4);
            $heading[] = bin2hex(fread($handle, 4));
            $heading[] = bin2hex(fread($handle, 2));
            $heading[] = bin2hex(fread($handle, 2));
            $heading[] = bin2hex(fread($handle, 4));
            $heading[] = bin2hex(fread($handle, 4));
            $heading[] = bin2hex(fread($handle, 2));
            $heading[] = bin2hex(fread($handle, 2));
            $heading[] = fread($handle, 4);
            $heading[] = bin2hex(fread($handle, 4));

            // wav bitrate
            $peek = hexdec(substr($heading[10], 0, 2));
            $byte = $peek / 8;

            // checking whether a mono or stereo wav
            $channel = hexdec(substr($heading[6], 0, 2));

            $ratio = ($channel == 2 ? 40 : 80);

            // start putting together the initial canvas
            // $data_size = (size_of_file - header_bytes_read) / skipped_bytes + 1
            $data_size = floor((filesize($filename) - 44) / ($ratio + $byte) + 1);
            $data_point = 0;

            //invalid file, create empty img
            if($data_size == 0)
            {
                $rimg = imagecreatetruecolor($width, $height);
                imagesavealpha($rimg, true);
                $transparentColor = imagecolorallocatealpha($rimg, 0, 0, 0, 127);
                imagefill($rimg, 0, 0, $transparentColor);
                imagepng($rimg, $filename_w);
                imagedestroy($rimg);
                Storage::disk('local')->deleteDirectory('temp');
                return false;
            }

            // now that we have the data_size for a single channel (they both will be the same)
            // we can initialize our image canvas
            if (!$img) {
                // create original image width based on amount of detail
                // each waveform to be processed with be $height high, but will be condensed
                // and resized later (if specified)
                $img = imagecreatetruecolor($data_size / $detail, $height * sizeof($wavs_to_process));

                // fill background of image
                if ($background == "") {
                    // transparent background specified
                    imagesavealpha($img, true);
                    $transparentColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
                    imagefill($img, 0, 0, $transparentColor);
                } else {
                    list($br, $bg, $bb) = $this->html2rgb($background);
                    imagefilledrectangle($img, 0, 0, (int) ($data_size / $detail), $height * sizeof($wavs_to_process), imagecolorallocate($img, $br, $bg, $bb));
                }
            }

            while(!feof($handle) && $data_point < $data_size){
                if ($data_point++ % $detail == 0) {
                    $bytes = array();

                    // get number of bytes depending on bitrate
                    for ($i = 0; $i < $byte; $i++)
                        $bytes[$i] = fgetc($handle);

                    switch($byte){
                        // get value for 8-bit wav
                        case 1:
                            $data = $this->findValues($bytes[0], $bytes[1]);
                            break;
                        // get value for 16-bit wav
                        case 2:
                            if(ord($bytes[1]) & 128)
                                $temp = 0;
                            else
                                $temp = 128;
                            $temp = chr((ord($bytes[1]) & 127) + $temp);
                            $data = floor($this->findValues($bytes[0], $temp) / 256);
                            break;
                    }

                    // skip bytes for memory optimization
                    fseek($handle, $ratio, SEEK_CUR);

                    // draw this data point
                    // relative value based on height of image being generated
                    // data values can range between 0 and 255
                    $v = (int) ($data / 255 * $height);

                    // don't print flat values on the canvas if not necessary
                    if (!($v / $height == 0.5 && !$draw_flat))
                        // draw the line on the image using the $v value and centering it vertically on the canvas
                        imageline(
                            $img,
                            // x1
                            (int) ($data_point / $detail),
                            // y1: height of the image minus $v as a percentage of the height for the wave amplitude
                            $height * $wav - $v,
                            // x2
                            (int) ($data_point / $detail),
                            // y2: same as y1, but from the bottom of the image
                            $height * $wav - ($height - $v),
                            imagecolorallocate($img, $r, $g, $b)
                        );

                } else {
                    // skip this one due to lack of detail
                    fseek($handle, $ratio + $byte, SEEK_CUR);
                }
            }

            // close and cleanup
            fclose($handle);
        }

        // want it resized?
        if ($width) {
            // resample the image to the proportions defined in the form
            $rimg = imagecreatetruecolor($width, $height);
            // save alpha from original image
            imagesavealpha($rimg, true);
            imagealphablending($rimg, false);
            // copy to resized
            imagecopyresampled($rimg, $img, 0, 0, 0, 0, $width, $height, imagesx($img), imagesy($img));
            imagepng($rimg, $filename_w);
            imagedestroy($rimg);
        } else {
            imagepng($img, $filename_w);
        }
        imagedestroy($img);

        Storage::disk('local')->deleteDirectory('temp');
        return true;
    }
}