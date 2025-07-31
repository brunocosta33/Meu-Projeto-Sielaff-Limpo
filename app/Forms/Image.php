<?php

namespace App\Forms;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Image extends Model
{
    //
    public function storeImage( $img, $id, $type, $slug){

        $fileInfo = pathinfo($img->getClientOriginalName());
        $year =  now()->year;
        $month =  now()->month;
        $day =  now()->day;

        $uri = '/'. $type. '/'. $year . '/' . $month. '/' . $day;
        $this->path =  base_path()  . '/public/app_images' . $uri ;// public_path('images/../../../../app_images/cromotema') . $uri;
        $this->url = '/app_images' . $uri. '/';
        $date = date('dmYhis', time());
        $this->file = $id . '-'. $slug. '-'. $date . '.' . $fileInfo['extension'];

        $img->move($this->path, $this->file);

        DB::table('images')->insertGetId([
            'path' => $this->path,
            'url' => $this->url,
            'file' => $this->file,
            'related_id' => $id,
            'type' => $type,
            'created_at' => now(),
        ]);
        return $id;
    }

    //

    public function deleteImage($id){
        $result = null;
        return $result;
    }

    public function deleteOldImage(){
        $result = null;
        if($this->path != null && $this->filename != null){
            $file = $this->path . "/". $this->filename;
            if(file_exists($file)){
                try {
                    unlink($file);
                    $result = true;
                } catch (\Exception $e) {
                    $result = false;
                }
            }
        }
        return $result;
    }

    public function storeLogo(Company $company, $img){

        $fileInfo = pathinfo($img->getClientOriginalName());
        $companyname = str_replace(' ','',$company->display_name);

        $uri = '/logos' . '/'. $companyname;
        $this->path =  base_path() . '/public/app_images' . $uri ;// public_path('images/../../../../app_images/cromotema') . $uri;
        $this->url = '/app_images' . $uri. '/';
        $date = date('dmYhis', time());
        $this->file = $company->id . '-'. $date . '.' . $fileInfo['extension'];
        $this->type = 'logos';
        $img->move($this->path, $this->file);
    }


    public function products()
    {
        return $this->hasOne(Product::class);
    }
}
