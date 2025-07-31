<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\Configuration;

class ConfigurationsController extends Controller
{

    public function index(){
        $configurations = DB::table('configurations')->whereNull('deleted_at')->orderBy('created_at', 'desc')->first();

        if ($configurations == null) {
            return view('backoffice.configurations.new');
        }
        else {
            $logo = DB::table('images')->where('id', $configurations->image_id)->whereNull('deleted_at')->first();
            $bg = DB::table('images')->where('id',  $configurations->image_background_id)->whereNull('deleted_at')->first();

            return view('backoffice.configurations.index', compact('configurations', 'logo','bg'));
        }
    }

    public function store(Request $request){

        $configuration = DB::table('configurations')->select('id')->whereNull('deleted_at')->orderBy('created_at', 'desc')->first();


        if($configuration == null){
            $configuration_id =  DB::table('configurations')->insertGetId(['created_at' => now()]);
        }
        else{
            $configuration_id = $configuration->id;
        }

        DB::table('configurations')->where('id', $configuration_id)->update([
            'text_about_pt'  => $request->input('text_about_pt'),
            'text_about_en'  => $request->input('text_about_en'),
            'text_termos_pt' => $request->input('text_termos_pt'),
            'text_termos_en' => $request->input('text_termos_en'),
            'text_rgpd_pt'   => $request->input('text_rgpd_pt'),
            'text_rgpd_en'   => $request->input('text_rgpd_en')
        ]);


        if ($request->hasFile('imagens')){
            foreach ($request->file('imagens') as $img) {
                $this->saveImage($img, $configuration_id, 'logo', 'logo');
            }

            $image = DB::table('images')->select('id')->whereNull('deleted_at')->where('type', 'logo')->orderBy('created_at', 'desc')->first();

            DB::table('configurations')->where('id', $configuration_id)->update(['image_id' => $image->id]);

            DB::table('images')->where('id', $image->id)->update(['main' => '1']);
        }

        if ($request->hasFile('imagens-mobile')){
            foreach ($request->file('imagens-mobile') as $img) {
                $this->saveImage($img,  $configuration_id, 'bg', 'bg');
            }

            $bg = DB::table('images')->select('id')->whereNull('deleted_at')->where('type', 'bg')->orderBy('created_at', 'desc')->first();

            DB::table('configurations')->where('id',  $configuration_id)->update(['image_background_id' => $bg->id]);

            DB::table('images')->where('id',  $bg->id)->update(['main' => '1']);
        }

        flash(__('Settings successfully saved!'))->success();

        return redirect()->route('backoffice.configurations.index');
    }

    protected function saveImage($img, $id,$type, $slug){
        $image = new Image();
        $image->storeImage($img, $id, $type, $slug);
    }
}
