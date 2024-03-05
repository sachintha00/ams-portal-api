<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrawerDataSeeder extends Seeder
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
                'design_obj' => json_encode(['x_value' => 0, 'y_value' => 0, 'width' => 3, 'height' => 6.9,  'style' => "<div class='max-w-sm w-full bg-white rounded-lg p-4 md:p-6'><div class='flex justify-between border-gray-200 border-b  pb-3'><dl><dt class='text-base font-normal text-gray-500'>Profit<\/dt><dd class='leading-none text-3xl font-bold text-gray-900 '>$5,405<\/dd><\/dl><div><span class='bg-green-100 text-green-800 text-xs font-medium inline-flex items-center px-2.5 py-1 rounded-md'><svg class='w-2.5 h-2.5 me-1.5' aria-hidden='true' xmlns='http:\/\/www.w3.org\/2000\/svg' fill='none' viewBox='0 0 10 14'><path stroke='currentColor' strokeLinecap='round' strokeLinejoin='round' strokeWidth='2' d='M5 13V1m0 0L1 5m4-4 4 4'\/><\/svg>Profit rate 23.5%<\/span><\/div><\/div><div class='grid grid-cols-2 py-3'><dl><dt class='text-base font-normal text-gray-500 pb-1'>Income<\/dt><dd class='leading-none text-xl font-bold text-green-500 '>$23,635<\/dd><\/dl><dl><dt class='text-base font-normal text-gray-500 '>Expense<\/dt><dd class='leading-none text-xl font-bold text-red-600 '>-18,230<\/dd><\/dl><\/div><div id='bar-chart' \/><div class='grid grid-cols-1 items-center border-gray-200 border-t  justify-between'><div class='flex justify-between items-center pt-5'><button id='dropdownDefaultButton' data-dropdown-toggle='lastDaysdropdown' data-dropdown-placement='bottom' class='text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 text-center inline-flex items-center ' type='button'>Last 6 months<svg class='w-2.5 m-2.5 ms-1.5' aria-hidden='true' xmlns='http:\/\/www.w3.org\/2000\/svg' fill='none' viewBox='0 0 10 6'><path stroke='currentColor' strokeLinecap='round' strokeLinejoin='round' strokeWidth='2' d='m1 1 4 4 4-4'\/><\/svg><\/button><div id='lastDaysdropdown' class='z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 '><ul class='py-2 text-sm text-gray-700 ' aria-labelledby='dropdownDefaultButton'><li><a href='#' class='block px-4 py-2 hover:bg-gray-100 '>Yesterday<\/a><\/li><li><a href='#' class='block px-4 py-2 hover:bg-gray-100 '>Today<\/a><\/li><li><a href='#' class='block px-4 py-2 hover:bg-gray-100 '>Last 7 days<\/a><\/li><li><a href='#' class='block px-4 py-2 hover:bg-gray-100 '>Last 30 days<\/a><\/li><li><a href='#' class='block px-4 py-2 hover:bg-gray-100 '>Last 90 days<\/a><\/li><li><a href='#' class='block px-4 py-2 hover:bg-gray-100 '>Last 6 months<\/a><\/li><li><a href='#' class='block px-4 py-2 hover:bg-gray-100 '>Last year<\/a><\/li><\/ul><\/div><a href='#' class='uppercase text-sm font-semibold inline-flex items-center rounded-lg text-blue-600 hover:text-blue-700  hover:bg-gray-100  px-3 py-2'>Revenue Report<svg class='w-2.5 h-2.5 ms-1.5 rtl:rotate-180' aria-hidden='true' xmlns='http:\/\/www.w3.org\/2000\/svg' fill='none' viewBox='0 0 6 10'><path stroke='currentColor' strokeLinecap='round' strokeLinejoin='round' strokeWidth='2' d='m1 9 4-4-4-4'\/><\/svg><\/a><\/div><\/div><\/div>"]),
            ],
            [
                'image_path' => "/assets/icons/widget_drawer/stats.png",
                'category_id' => $categoryIds[1],
                'design_obj' => json_encode(['x_value' => 0, 'y_value' => 0, 'width' => 3, 'height' => 6.9,  'style' => ""]),
            ],
        ];

        DB::table('table_drawer_item_lists')->insert($itemLists);
    }
}
