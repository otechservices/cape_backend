<?php

namespace App\Utilities;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Image,Str;

class FileStorage
{
    static public function setFile($disk, $file, $directory="",$name=""){

        $directory_name=$directory;

        $directory_exist=Storage::disk($disk)->exists($directory_name);
   
            if(!$directory_exist) Storage::disk($disk)->makeDirectory($directory_name);

            $pre_filename= $name=="" || $name==null?$file->getFilename():Str::slug($name);

            $filename=$pre_filename.'.'.$file->getClientOriginalExtension();

        if (!Storage::disk($disk)->exists($directory_name!="" || $directory_name!=null?$directory_name."/".$filename:$filename)) {
      
               Storage::disk($disk)->put($directory_name!="" || $directory_name!=null?$directory_name."/".$filename:$filename,  File::get($file));
        }

        return $filename;
    }

    static public function set64File($disk, $file, $directory="",$name=""){

        $directory_name=$directory;

        $directory_exist=Storage::disk($disk)->exists($directory_name);
   
            if(!$directory_exist) Storage::disk($disk)->makeDirectory($directory_name);
            $image =$file; 
            $extension = explode('/', mime_content_type($image))[1];
            $image=explode(',',$image);
            $image = str_replace(' ', '+', $image[1]);
            $filename = $name.'.'.$extension;
            //  $image = str_replace('data:image/png;base64,', '', $image);
            //  info($image);
            // $image = str_replace(' ', '+', $image);
            // $filename = $name.'.pdf';
            // $pre_filename= $name=="" || $name==null?$file->getFilename():Str::slug($name);

            // $filename=$pre_filename.'.'.$file->getClientOriginalExtension();

        if (!Storage::disk($disk)->exists($directory_name!="" || $directory_name!=null?$directory_name."/".$filename:$filename)) {
            \File::put(Storage::disk($disk)->path($directory_name!="" || $directory_name!=null?$directory_name."/".$filename:$filename), base64_decode($image));
        }

        return $filename;
    }

    static public function deleteFile($disk,$file,$directory=""){

      $file_exist=Storage::disk($disk)->exists($directory!=""|| $directory!=null?$directory.'/'.$file:$file);

      if($file_exist){
        Storage::disk($disk)->delete($directory!=""|| $directory!=null?$directory.'/'.$file:$file);
        } 
    }

    static public function moveFile($disk,$directory,$old,$new){


        $directory_exist=Storage::disk($disk)->exists($directory);
   
        if(!$directory_exist) Storage::disk($disk)->makeDirectory($directory);

        if(file_exists(Storage::disk($disk)->path($old)))  
        rename(Storage::disk($disk)->path($old), Storage::disk($disk)->path($new));

        return true;
    }

}
