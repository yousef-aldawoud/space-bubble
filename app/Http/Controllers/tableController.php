<?php

namespace App\Http\Controllers;
use DB;
use Illuminate\Http\Request;
use App\Table;
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

        $table->area_id= $request->area_id;
        while(true){
            $token=str_random(self::$TOKEN_LENGTH);
            if(Table::where("token",'=',$token)){
                $token=str_random(self::$TOKEN_LENGTH);
            }else{
                $table->token=$token;
                break;
            }
        }
        
        $table->taken=0;

        $table->save();
    }

    
   
    public function statusUpdate(Request $request){
        
        DB::table('tables')
            ->where('token', $request->token)
            ->update(['taken' => $request->status]);

        
                
    }

    function refresh_area($id){
        echo'hello';
    }

}
