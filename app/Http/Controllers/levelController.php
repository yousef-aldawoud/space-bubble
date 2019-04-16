<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Level;
use App\Area;
use App\Table;
use DB;
class levelController extends Controller
{
    
    public function create()
    {
        return view('level.create');
    }
    public function showLevels(){
        return view('levels.index');
    }

    public function showAreas($id){
        $level_id=$id;
        
        $areas=Level::find($level_id)->areas()->get();
        return $areas;
    }
    
    

    public function store(Request $request)
    {
        $level=new Level;

        $level->building_id= $request->building_id;
        $level->name=$request->name;

        $level->save();

        return ["response"=>"<p class='success--text'>Building was created</p>","status"=>1];
    }
    public function changeMap()
    {   
        $checkMapID = true;
        $mapID=$this->ask("What is the maps id ?");
        $this->registerComponent($mapID);
        
        if($checkMapID){
            $compenet = fopen(base_path("resources/js/components/")."map-".$mapID.".vue", "w"); 
            fwrite($compenet, 
"<template>\n<area-map v>\n    <div slot=\"map\">\n\n         <!--put your svg code here-->\n\n    </div>\n</area-map></template>");
            fclose($compenet);
        }
    }
    public function registerComponent($mapID){
        $appJsBath=base_path("resources/js/app.js");
        $txt=file_get_contents($appJsBath);
        $spliter="//--------------------";
        $arr =explode($spliter,$txt);        
    
        $appJsWrite = fopen($appJsBath,"w");
        $newAppJsFileContent = $arr[0]."\nVue.component('map-".$mapID."', require('./components/map-".$mapID.".vue').default);\n".$spliter.$arr[1];
        fwrite($appJsWrite,$newAppJsFileContent);
        fclose($appJsWrite);
    }
    public function changeMap(Request $request){
    
    }
    public function delete(Request $request){
        $id=$request->id;
        
        $areas=Level::find($id)->areas();
        
        foreach ($areas->get() as $area){
            $area_id=$area->id;
            
            
            $tables=Area::find($area_id)->tables();
            $tables->delete();
        }
        $areas->delete();
        DB::table('levels')->where('id', '=', $id)->delete();
        
    }
    public function update(Request $request){
        DB::table('levels')
            ->where('id', $request->id)
            ->update(['name' =>$request->name]);

    }
    
}
