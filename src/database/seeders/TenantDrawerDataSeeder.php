<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantDrawerDataSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['category_name' => 'Analytics'],
            ['category_name' => 'Billings'],
        ];

        $categoryIds = [];
        foreach ($categories as $category) {
            $categoryId = DB::table('table_drawer_categories')->insertGetId($category);
            $categoryIds[] = $categoryId;
        }


        $itemLists = [
            [
                'image_path' => "/assets/icons/widget_drawer/stats.png",
                'category_id' => $categoryIds[0],
                'design_obj' => json_encode(['x_value' => 0, 'y_value' => 0, 'width' => 3, 'height' => 12]),
                'content' => '<div class="w-auto h-[-webkit-fill-available] bg-white border border-gray-200 rounded-lg shadow dark:bg-[#1e1e1e] dark:border-gray-700 p-4">
                          <div class="flex items-center justify-between mb-4">
                            <h5 class="text-xl font-bold leading-none text-gray-900 dark:text-white">To Do List</h5>
                          </div>
                          <div class="flow-root">
                            <div id="user-list">
                              <!-- User List Placeholder -->
                            </div>
                          </div>
                        </div>',
            ],
            [
                'image_path' => "/assets/icons/widget_drawer/stats.png",
                'category_id' => $categoryIds[1],
                'design_obj' => json_encode(['x_value' => 0, 'y_value' => 0, 'width' => 3, 'height' => 6.9]),
                'content' => '',
            ],
        ];

        DB::table('table_drawer_item_lists')->insert($itemLists);
    }
}