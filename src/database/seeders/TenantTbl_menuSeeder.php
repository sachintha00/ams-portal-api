<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TenantTbl_menuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $UserManagementID = DB::table('permissions')->where('name', 'User Management')->value('id');
        $RoleID = DB::table('permissions')->where('name', 'Role')->value('id');
        $UsersID = DB::table('permissions')->where('name', 'Users')->value('id');
        $ConfigID = DB::table('permissions')->where('name', 'Config')->value('id');
        $OrganizationID = DB::table('permissions')->where('name', 'Organization')->value('id');
        $WorkflowID = DB::table('permissions')->where('name', 'Workflow')->value('id');
        $ProcurementManagementID = DB::table('permissions')->where('name', 'Procurement Management')->value('id');
        $AssetRequisitionsID = DB::table('permissions')->where('name', 'Asset Requisitions')->value('id');
        $ProcurementInitiateID = DB::table('permissions')->where('name', 'Procurement Initiate')->value('id');
        $ProcurementStaffID = DB::table('permissions')->where('name', 'Procurement Staff')->value('id');
        $SupplierID = DB::table('permissions')->where('name', 'Supplier')->value('id');
        $SupplierQuotationID = DB::table('permissions')->where('name', 'Supplier Quotation')->value('id');
        $AssetsManagementID = DB::table('permissions')->where('name', 'Assets Management')->value('id');
        $currentTime = Carbon::now();

        DB::table('tbl_menu')->insert([
            [
                'permission_id' => $UserManagementID,
                'parent_id' => null,
                'menuname' => 'User Management',
                'description' => 'test',
                'menulink' => '#',
                'icon' => 'MdManageAccounts',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $RoleID,
                'parent_id' => 1,
                'menuname' => 'Role',
                'description' => 'test',
                'menulink' => '/dashboard/Roles',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $UsersID,
                'parent_id' => 1,
                'menuname' => 'Users',
                'description' => 'test',
                'menulink' => '/dashboard/users',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $ConfigID,
                'parent_id' => null,
                'menuname' => 'Config',
                'description' => 'test',
                'menulink' => '#',
                'icon' => 'GrDocumentConfig',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $OrganizationID,
                'parent_id' => 4,
                'menuname' => 'Organization',
                'description' => 'test',
                'menulink' => '/dashboard/organization',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $WorkflowID,
                'parent_id' => 4,
                'menuname' => 'Workflow',
                'description' => 'test',
                'menulink' => '/dashboard/workflow',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $ProcurementManagementID,
                'parent_id' => null,
                'menuname' => 'Procurement Management',
                'description' => 'test',
                'menulink' => '#',
                'icon' => 'VscServerProcess',
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $AssetRequisitionsID,
                'parent_id' => 7,
                'menuname' => 'Asset Requisitions',
                'description' => 'test',
                'menulink' => '/dashboard/asset_requisitions',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $ProcurementInitiateID,
                'parent_id' => 7,
                'menuname' => 'Procurement Initiate',
                'description' => 'test',
                'menulink' => '/dashboard/procurement_initiate',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $ProcurementStaffID,
                'parent_id' => 7,
                'menuname' => 'Procurement Staff',
                'description' => 'test',
                'menulink' => '/dashboard/staff',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $SupplierID,
                'parent_id' => 7,
                'menuname' => 'Supplier',
                'description' => 'test',
                'menulink' => '/dashboard/supplier',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $SupplierQuotationID,
                'parent_id' => 7,
                'menuname' => 'Supplier Quotation',
                'description' => 'test',
                'menulink' => '/dashboard/supplier_quotation',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
            [
                'permission_id' => $AssetsManagementID,
                'parent_id' => 7,
                'menuname' => 'Assets Management',
                'description' => 'test',
                'menulink' => '/dashboard/assets_management',
                'icon' => null,
                'created_at' => $currentTime,
                'updated_at' => $currentTime
            ],
        ]);

        // $tbl_menu_list = [
        //     [
        //         'permission_id' => $UserManagementID,
        //         'parent_id' => null,
        //         'menuname' => 'User Management',
        //         'description' => 'test',
        //         'menulink' => '#',
        //         'icon' => 'MdManageAccounts',
        //     ],
        //     [
        //         'permission_id' => $RoleID,
        //         'parent_id' => 1,
        //         'menuname' => 'Role',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/Roles',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $UsersID,
        //         'parent_id' => 1,
        //         'menuname' => 'Users',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/users',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $ConfigID,
        //         'parent_id' => null,
        //         'menuname' => 'Config',
        //         'description' => 'test',
        //         'menulink' => '#',
        //         'icon' => 'GrDocumentConfig',
        //     ],
        //     [
        //         'permission_id' => $OrganizationID,
        //         'parent_id' => 4,
        //         'menuname' => 'Organization',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/organization',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $WorkflowID,
        //         'parent_id' => 4,
        //         'menuname' => 'Workflow',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/workflow',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $ProcurementManagementID,
        //         'parent_id' => null,
        //         'menuname' => 'Procurement Management',
        //         'description' => 'test',
        //         'menulink' => '#',
        //         'icon' => 'VscServerProcess',
        //     ],
        //     [
        //         'permission_id' => $AssetRequisitionsID,
        //         'parent_id' => 7,
        //         'menuname' => 'Asset Requisitions',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/asset_requisitions',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $ProcurementInitiateID,
        //         'parent_id' => 7,
        //         'menuname' => 'Procurement Initiate',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/procurement_initiate',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $ProcurementStaffID,
        //         'parent_id' => 7,
        //         'menuname' => 'Procurement Staff',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/staff',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $SupplierID,
        //         'parent_id' => 7,
        //         'menuname' => 'Supplier',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/supplier',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $SupplierQuotationID,
        //         'parent_id' => 7,
        //         'menuname' => 'Supplier Quotation',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/supplier_quotation',
        //         'icon' => null,
        //     ],
        //     [
        //         'permission_id' => $AssetsManagementID,
        //         'parent_id' => 7,
        //         'menuname' => 'Assets Management',
        //         'description' => 'test',
        //         'menulink' => '/dashboard/supplier_quotation',
        //         'icon' => null,
        //     ],
        // ];

        // // Seed multiple permission
        // foreach ($tbl_menu_list as $TBL_menu_list) {
        //     tbl_menu::create($TBL_menu_list);
        // }
    } 
}