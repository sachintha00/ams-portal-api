<?php

namespace App\Http\Controllers;

use App\Models\TableDrawerItemLists;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TableDrawerItemListController extends Controller
{
    public function storeTableDrawerItemList(Request $request)
    {

        $item = TableDrawerItemLists::create([
            'image_path' => $request->input('image_path'),
            'category_id' => $request->input('category_id'),
            'design_obj' => $request->input('design_obj'),
        ]);

        return response()->json(['message' => 'Icon inserted successfully', 'item' => $item], 201);
    }

    public function retrieveDrawerItemList(Request $request)
    {
        $items = TableDrawerItemLists::with('category')->get();

        $groupedItems = $items->groupBy('category.category_name')->map(function ($group) {

            $category_name = $group->first()->category->category_name;
            $category_related_all_object = $group->map(function ($item) {
                return [
                    'id' => $item->id,
                    'image_path' => $item->image_path,
                    'design_obj' => $item->design_obj,
                    'design_component' => $item->component,
                    'widget_type' => $item->widget_type,
                ];
            })->all();

            return [
                'category_name' => $category_name,
                'category_related_all_object' => $category_related_all_object,
            ];
        })->values();

        return response()->json(['items' => $groupedItems], 200);
    }

    public function retrieveWidgets(Request $request)
    {
        DB::beginTransaction();

        try {
            DB::statement('CALL STORE_PROCEDURE_RETRIEVE_WIDGETS()');
            
            $widgets = DB::table('temp_widgets_from_store_procedure')->select('data')->get();
            if ($widgets->isEmpty()) {
                return response()->json(['items' => []], 404);
            }

            $widgetData = json_decode($widgets[0]->data, true);

            DB::commit();

            return response()->json(['items' => $widgetData]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}