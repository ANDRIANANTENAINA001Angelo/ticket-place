<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;


class ApiResponse{

    public static function success(Model|Collection|array $data, string $message="Success", int $status=200){
        
        $result["message"]=$message;
        if(is_countable($data)){
            $result["count"]=count($data);
            $result["data"]=$data;
        }
        else{
            $result["count"]=1;
            $result["data"]=[];
            array_push($result["data"],$data);
        }

        return response()->json($result,$status);
    }

    public static function error(string $message="Error", int $status, string $error=""){
        if($error!=""){
            $data["error"]=$error;
        }
        $data["message"]=$message;

        return response()->json($data,$status);
    }
}



?>