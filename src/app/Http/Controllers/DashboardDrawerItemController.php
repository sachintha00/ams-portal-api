<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardDrawerItemController extends Controller
{
    public function drawerItems(Request $request){
        $widgetData = [
            'widget_menu_name' => 'Analytics',
            'widget_list' => [
                '<IoAnalytics size={35} />',
                '<TfiBarChartAlt size={35} />',
                '<TfiPieChart size={35} />',
                '<IoSpeedometerOutline size={35} />',
            ],
        ];

        return response()->json($widgetData);
    }
    
    public function dashboardItem(Request $request){
        $widgetData = [
            'x' => 0,
            'y' => 0,
            'w' => 2.5,
            'h' => 6,
            'i' => 1, // You may replace 'index' with a dynamic value if needed
            'style' => '<div class="text-red-500 h-full bg-yellow-400">test</div>',
        ];

        return response()->json($widgetData);
    }
}