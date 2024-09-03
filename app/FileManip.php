<?php
namespace App;

// use Dotenv\Util\Str;
use Illuminate\Support\Str;

class FileManip{


    public static function PathToUrl(string $path):string{
        return env("APP_URL")."/storage/".$path;
    }

    // public static function UrlToPath(string $path){
    //     $len = Str::len(env("APP_URL")."/storage/");
    //     return Str::substr($path,$len,Str::len($path));
    // }
    
    public static function UrlToPath(string $url): string
    {
        $storageUrl = env("APP_URL") . "/storage/";
        return Str::after($url, $storageUrl);
    }

}


?>