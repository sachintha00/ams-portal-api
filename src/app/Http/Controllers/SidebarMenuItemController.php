<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

class SidebarMenuItemController extends Controller
{    public function getSidebarMenus(){
        DB::beginTransaction();

        try {
        
            DB::statement('CALL STORE_PROCEDURE_RETRIEVE_SIDEBAR_MENUS()');
            $menus = DB::table('temp_sidebar_menus_from_store_procedure')->select('*')->get();
            
            DB::commit();
            
            return response()->json(['data' => $menus]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}