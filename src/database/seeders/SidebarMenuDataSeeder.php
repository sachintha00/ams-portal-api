<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SidebarMenuDataSeeder extends Seeder
{
    public function run(): void
    {
        $jsonData = '[
                {
                    "menu": "Dashboard",
                    "icon": "/assets/icons/dashboard_sidebar/dashboard.png",
                    "link": "/dashboard"
                },
                {
                    "menu": "Apps",
                    "icon": "/assets/icons/dashboard_sidebar/predictive-chart.png",
                    "link": "/dashboard",
                    "subMenu": [
                        {
                            "subMenuName": "Test 1",
                            "submenuItems": {
                                "nestedSubMenu": [
                                    { "link": "/dashboard", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" }
                                ]
                            }
                        },
                        {
                            "subMenuName": "Test 2",
                            "submenuItems": {
                                "nestedSubMenu": [
                                    { "link": "/dashboard", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" }
                                ]
                            }
                        },
                        {
                            "subMenuName": "Test 3",
                            "submenuItems": {
                                "nestedSubMenu": [
                                    { "link": "/dashboard", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" }
                                ]
                            }
                        }
                    ]
                },
                
                {
                    "menu": "Pages",
                    "icon": "/assets/icons/dashboard_sidebar/group.png",
                    "subMenu": [
                        {
                            "subMenuName": "Test 1",
                            "submenuItems": {
                                "nestedSubMenu": [
                                    { "link": "/dashboard", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" }
                                ]
                            }
                        },
                        {
                            "subMenuName": "Test 2",
                            "submenuItems": {
                                "nestedSubMenu": [
                                    { "link": "/dashboard", "name": "Nested Test 1" },
                                    { "link": "/test", "name": "Nested Test 1" }
                                ]
                            }
                        }
                    ]
                }
            ]';

        $data = json_decode($jsonData, true);

        DB::table('sidebar_menu_items')->insert([
            'menu_structure' => json_encode($data)
        ]);
    }
}