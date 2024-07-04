<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SidebarMenusDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('sidebar_menus')->insert([
            ['label' => 'Home', 'key' => 'home', 'icon' => 'HomeOutlined', 'href' => '/dashboard', 'level' => 1],
            ['label' => 'Activity', 'key' => 'activity', 'icon' => 'AppstoreOutlined', 'href' => '/dashboard/activity', 'level' => 1],
            ['label' => 'Progress', 'key' => 'progress', 'icon' => 'AreaChartOutlined', 'href' => '/progress', 'level' => 1],
            ['label' => 'Payment', 'key' => 'payment', 'icon' => 'PayCircleOutlined', 'href' => '/payment', 'level' => 1],
            ['label' => 'Setting', 'key' => 'setting', 'icon' => 'SettingOutlined', 'href' => '/setting', 'level' => 1],
            ['label' => 'Task', 'key' => 'task', 'icon' => 'BarsOutlined', 'href' => null, 'level' => 1],
        ]);

        $taskId = DB::table('sidebar_menus')->where('key', 'task')->first()->id;

        DB::table('sidebar_menus')->insert([
            ['label' => 'Task 1', 'key' => 'task-1', 'icon' => null, 'href' => '/dashboard/workflow', 'parent_id' => $taskId, 'level' => 2],
            ['label' => 'Task 2', 'key' => 'task-2', 'icon' => null, 'href' => '/task-2', 'parent_id' => $taskId, 'level' => 2],
            ['label' => 'Task 3', 'key' => 'task-3', 'icon' => null, 'href' => '/task-3', 'parent_id' => $taskId, 'level' => 2],
            ['label' => 'Task 4', 'key' => 'subtask1', 'icon' => null, 'href' => null, 'parent_id' => $taskId, 'level' => 2],
            ['label' => 'Task 5', 'key' => 'task-5', 'icon' => null, 'href' => '/task-5', 'parent_id' => $taskId, 'level' => 2],
            ['label' => 'Task 6', 'key' => 'task-6', 'icon' => null, 'href' => '/task-6', 'parent_id' => $taskId, 'level' => 2],
            ['label' => 'Task 7', 'key' => 'subtask2', 'icon' => null, 'href' => null, 'parent_id' => $taskId, 'level' => 2],
        ]);

        $task4Id = DB::table('sidebar_menus')->where('key', 'subtask1')->first()->id;
        $task7Id = DB::table('sidebar_menus')->where('key', 'subtask2')->first()->id;

        DB::table('sidebar_menus')->insert([
            ['label' => 'Sub Task 1', 'key' => 'sub-task1-1', 'icon' => null, 'href' => '/sub-task1-1', 'parent_id' => $task4Id, 'level' => 3],
            ['label' => 'Sub Task 2', 'key' => 'sub-task1-2', 'icon' => null, 'href' => '/sub-task1-2', 'parent_id' => $task4Id, 'level' => 3],
            ['label' => 'Sub Task 3', 'key' => 'sub-task1-3', 'icon' => null, 'href' => '/sub-task1-3', 'parent_id' => $task4Id, 'level' => 3],
            ['label' => 'Sub Task 4', 'key' => 'sub-task1-4', 'icon' => null, 'href' => '/sub-task1-4', 'parent_id' => $task4Id, 'level' => 3],
        ]);

        DB::table('sidebar_menus')->insert([
            ['label' => 'Sub Task 1', 'key' => 'sub-task2-1', 'icon' => null, 'href' => '/sub-task2-1', 'parent_id' => $task7Id, 'level' => 3],
            ['label' => 'Sub Task 2', 'key' => 'sub-task2-2', 'icon' => null, 'href' => '/sub-task2-2', 'parent_id' => $task7Id, 'level' => 3],
            ['label' => 'Sub Task 3', 'key' => 'sub-task2-3', 'icon' => null, 'href' => '/sub-task2-3', 'parent_id' => $task7Id, 'level' => 3],
            ['label' => 'Sub Task 4', 'key' => 'sub-task2-4', 'icon' => null, 'href' => '/sub-task2-4', 'parent_id' => $task7Id, 'level' => 3],
        ]);
    }
}