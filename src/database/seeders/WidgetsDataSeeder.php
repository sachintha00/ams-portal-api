<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WidgetsDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Analytics'],
            ['category_name' => 'Billings'],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryId = DB::table('widgets_categories')->insertGetId($category);
            $categoryIds[] = $categoryId;
        }


        $itemLists = [
            [
                'image_path' => "/assets/icons/widget_drawer/analytics-graph-chart-svgrepo-com.svg",
                'category_id' => $categoryIds[0],
                'design_obj' => json_encode(['x_value' => 0, 'y_value' => 0, 'width' => 3, 'height' => 6.9]),
                'design_component' => "<div>test</div>",
                'widget_type' => "LINE_CHART",
            ],
            [
                'image_path' => "/assets/icons/widget_drawer/analytics-svgrepo-com(1).svg",
                'category_id' => $categoryIds[1],
                'design_obj' => json_encode(['x_value' => 0, 'y_value' => 0, 'width' => 3, 'height' => 6.9]),
                'design_component' => "<div>Hello</div>",
                'widget_type' => "BAR_CHART",
            ],
        ];

        DB::table('widgets')->insert($itemLists);
    }
}