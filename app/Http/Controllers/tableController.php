<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Table;
use App\Area;
class tableController extends Controller
{
    public static  $TOKEN_LENGTH = 25;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request){
        $id=$request->id;
        DB::table('tables')->where('id', '=', $id)->delete();
        return ['status'=>true,'response' => "Table deleted!","id"=>$request->id];
    }
    
    public function create()
    {
        return view('table.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $table=new Table;
        $state = true;
        $table->area_id= $request->area_id;
        while($state){
            $token=str_random(self::$TOKEN_LENGTH);
            if(Table::where("token",'=',$token)->count()>0){
                $token=str_random(self::$TOKEN_LENGTH);
                continue;
            }else{
                $table->token=$token;
                $state=false;
                break;
            }
            
        }
        
        $table->taken=0;

        $table->save();
        $ID=DB::table('tables')->orderBy('id','desc')->first()->id;
        return ['status'=>true,'response' => "Table created!","data"=>$request->all(),'id'=>$ID];
    }

    public function storeMany(Request $request){
        for ($i=0;$i<$request->quantity;$i++){
            $this->store($request);
        }
    }

    public function makeTablesTaken($id,$number){
        $tables=Area::find($id)->tables()->get();
        $reach = 0;
        foreach ($tables as $table) {
            if($reach==$number){
                $table->taken = 0;

            }else{
                $reach++;
                $table->taken = 1;
            }
            $table->save();
            
        }
        
    }

    public function moveTo(Request $request){
        
        if(Area::where('id', '=', $request->area_id)->count() == 0){
            return 'Error area does not exist';
        }else{
            $area=Area::find($request->area_id)->first();
            DB::table('tables')
            ->where('id', $request->table_id)
            ->update(['area_id' =>$request->area_id]);
            return 'update was successful';
        }
    }
   
   
    public function statusUpdate(Request $request){
        
        DB::table('tables')
            ->where('token', $request->token)
            ->update(['taken' => $request->status]);
     
    }


}
