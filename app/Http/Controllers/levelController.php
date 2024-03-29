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
        if(!Level::find($id)){
            return [];
        }
        $ar=[];
        $areas=Level::find($level_id)->areas()->get();
        for ($i=0;$i<count($areas);$i++){
            $areas[$i]['tables']= areaController::totalTables($areas[$i]['id']);
            $areas[$i]['takenTables']= areaController::totalTakenTables($areas[$i]['id']);
        }
        return $areas;
    }
    
    
    public function getMap($id){
        if(Level::find($id)===null){
            return view("404-map");
        }
        $level = Level::find($id);
        $building=$level->building()->get()[0];
        $nav=[["text"=>'Buildings',"href"=>"/","disabled"=>false]];
        array_push($nav,['text'=>$building->name,"href"=>"/","disabled"=>true]);
        array_push($nav,['text'=>$building->name,"href"=>"/map/".$id,"disabled"=>false]);
        return view('map')->with(["id"=>$id,"nav"=>$nav,"level_name"=>$level->name]);
    }
    public function store(Request $request)
    {
        $level=new Level;
        $level->building_id= $request->building_id;
        $level->name=$request->name;
        $level->save();
        $this->registerComponent($level->id);
        return ["data"=>$request->all(),"response"=>"<p class='success--text'>Building was created</p>","status"=>1];
    }


    public function changeMap(Request $request,$id)
    {   
        if($level = Level::find($id)){
            $level->name = $request->name;
            $level->save();
        }else{
            return ["response"=>'level not found'];
        }
        $checkMapID = $request->file('map')!==null;
        $mapID=$id;
        
        if($checkMapID){
            $compenet = fopen(base_path("resources/js/components/maps/")."map-".$mapID.".vue", "w"); 
            fwrite($compenet, 
"<template>\n<area-map :id=\"id\" :name=\"name\" v>\n    <div slot=\"map\">\n\n         ".$this->saveMapFile($request)." \n\n   </div>\n</area-map></template><script>
export default {
   props:{
       name:{type:String},
      id:{type:String},
      nav:{type:Array}
   }
}
</script>");
            fclose($compenet);
        }
        return ["response"=>"<p class='success--text'>Building was created</p>","status"=>1];
    }

    public function deleteVueComponent($mapID){
        $appJsBath=base_path("resources/js/app.js");
        $txt=file_get_contents($appJsBath);
        $appJsWrite = fopen($appJsBath,"w");
        $phrase = "Vue.component('map-".$mapID."', require('./components/maps/map-".$mapID.".vue').default);";
        $txt=str_replace($phrase,"",$txt);
        fwrite($appJsWrite,$txt);
        fclose($appJsWrite);
        if(file_exists(base_path("resources/js/components/maps/")."map-".$mapID.".vue")){

            unlink(base_path("resources/js/components/maps/")."map-".$mapID.".vue");
        }
    }

    public function writeVueFile($mapID,$content){
        $compenet = fopen(base_path("resources/js/components/maps/")."map-".$mapID.".vue", "w"); 
            fwrite($compenet, 
"<template>\n<area-map v>\n    <div slot=\"map\">\n\n         ".$content." \n\n   </div>\n</area-map></template>");
            fclose($compenet);
    
            
    }

    public function saveMapFile(Request $request){
        $file = $request->file('map'); // get the file user sent via POST
        $svgFile = str_replace('<?xml version="1.0" encoding="UTF-8" standalone="no"?>','',file_get_contents($file));

        $heightPattren = "/\sheight=\"[0-9a-z]*\"/";
        $widthPattren = "/\swidth=\"[0-9a-z]*\"/";
        $svgFile = preg_replace($heightPattren, "", $svgFile);
        $svgFile = preg_replace($widthPattren, "", $svgFile);
        return $svgFile;
    }

    public function registerComponent($mapID){
        $appJsBath=base_path("resources/js/app.js");
        $txt=file_get_contents($appJsBath);
        $spliter="//--------------------";
        $arr =explode($spliter,$txt);
        $appJsWrite = fopen($appJsBath,"w");
        $this->writeVueFile($mapID,"<p>This map is not available</p>");
        $newAppJsFileContent = $arr[0]."\nVue.component('map-".$mapID."', require('./components/maps/map-".$mapID.".vue').default);\n".$spliter.$arr[1];
        fwrite($appJsWrite,$newAppJsFileContent);
        fclose($appJsWrite);
    }
    public function delete($id,Request $request){
    
        
        $areas=Level::find($id)->areas();
        
        foreach ($areas->get() as $area){
            $area_id=$area->id;
            
            
            $tables=Area::find($area_id)->tables();
            $tables->delete();
        }
        $areas->delete();
        $this->deleteVueComponent($id);

        DB::table('levels')->where('id', '=', $id)->delete();

        return ["response"=>"Area deleted!", 'request'=>$request->all(),'status'=>1];
        
    }
    public function update(Request $request){
        DB::table('levels')
            ->where('id', $request->id)
            ->update(['name' =>$request->name]);

    }
    
}
