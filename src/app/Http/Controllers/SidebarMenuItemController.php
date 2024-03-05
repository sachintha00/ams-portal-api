<?php

namespace App\Http\Controllers;

use App\Models\SidebarMenuItem;
use Illuminate\Http\Request;

class SidebarMenuItemController extends Controller
{
    public function storeSidebarMenuItems(Request $request)
    {
        SidebarMenuItem::create(['menu_structure' => json_encode($request->menu_structure)]);
    }
    public function getSidebarMenuItems()
    {
        $sidebarMenuItems = SidebarMenuItem::all();
        
        $formattedMenuItems = [];
        
        foreach ($sidebarMenuItems as $menuItem) {
            $formattedMenuItems[] = json_decode($menuItem->menu_structure, true);
        }

        return response()->json($formattedMenuItems);
    }
}