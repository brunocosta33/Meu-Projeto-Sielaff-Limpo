<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Image;

class ImagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function imageDelete($id)
    {

        DB::table('images')->where('id',  $id)->update(['deleted_at' => now(),'main'=>'0']);

        $type = DB::table('images')->select('type')->where('id',  $id)->first();
        $images = DB::table('images')->where('id',  $id)->first();
        $imagemain = DB::table('images')->where('related_id',  $images->related_id)->where('main','=','1')->first();

         if (empty($imagemain)) {
            $image = DB::table('images')->whereNull('deleted_at')->where('type',  $type->type)->where('related_id', $images->related_id)->first();
            if(!empty($image)){
            DB::table($type->type)->where('id',  $images->related_id)->update(['image_id' => $image->id]);
            DB::table('images')->where('id',  $image->id)->update(['main' => '1']);
            return $image->id;
           }
        } else {
            return 0;
        }

    }

    protected function imageStar($id, $item)
    {
        $type = DB::table('images')->select('type')->where('id',  $id)->first();
        
        DB::table($type->type)->where('id',  $item)->update(['image_id' => $id]);
        DB::table('images')->where('related_id',  $item)->where('type',  $type->type)->update(['main' => '0']);
        DB::table('images')->where('id',  $id)->update(['main' => '1']);
    }   
    
}
