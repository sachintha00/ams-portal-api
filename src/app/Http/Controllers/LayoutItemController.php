<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LayoutItem;
use Illuminate\Support\Facades\DB;

class LayoutItemController extends Controller
{
    public function index()
    {
        DB::beginTransaction();

        try {
        
            DB::statement('CALL STORE_PROCEDURE_RETRIEVE_DASHBOARD_LAYOUT_WIDGETS()');
            $dbLayout = DB::table('dashboard_layout_from_store_procedure')->select('*')->get();
            
            DB::commit();
            
            return response()->json(['data' => $dbLayout]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addOrUpdateDashboardLayout(Request $request)
    {
        DB::beginTransaction();

        try {
            $data = $request->all();
        
            DB::statement('CALL STORE_PROCEDURE_INSERT_OR_UPDATE_DASHBOARD_LAYOUT_WIDGET(?, ?, ?, ?, ?, ?, ?, ?)', [
                $data['x'],
                $data['y'],
                $data['w'],
                $data['h'], 
                $data['style'],
                $data['widget_id'],
                $data['widget_type'],
                $data['id'] ?? null
            ]);
            
            DB::commit();
            
            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroy($layout_id)
    {
        DB::beginTransaction();

        try {
            $layourId = $layout_id;
        
            DB::statement('CALL STORE_PROCEDURE_REMOVE_DASHBOARD_LAYOUT_WIDGET(?)', [
                $layourId
            ]);
            
            DB::commit();
            
            return response()->json(['message' => "Successful remove"]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}