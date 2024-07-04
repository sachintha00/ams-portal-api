<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
            // SQL statement to create the stored procedure
            $procedure = <<<SQL
                CREATE OR REPLACE PROCEDURE create_full_asset_requisition(
                    IN _requisition_id VARCHAR(255),
                    IN _user_id BIGINT,
                    IN _requisition_date DATE,
                    IN _requisition_status VARCHAR(255),
                    IN _current_time TIMESTAMP WITH TIME ZONE,
                    IN _items JSON 
                )
                LANGUAGE plpgsql
                AS \$\$
                DECLARE
                    asset_requisition_id INTEGER;
                    item JSON;
                BEGIN
                    -- Insert the asset requisition
                    INSERT INTO asset_requisitions (requisition_id, requisition_by, requisition_date, requisition_status, created_at, updated_at)
                    VALUES (_requisition_id, _user_id, _requisition_date, _requisition_status, _current_time, _current_time)
                    RETURNING id INTO asset_requisition_id;
                
                    -- Loop through the items JSON array and insert each item
                    FOR item IN SELECT * FROM json_array_elements(_items)
                    LOOP
                        INSERT INTO asset_requisitions_items (
                            asset_requisition_id, 
                            item_name,
                            assesttype, 
                            quantity, 
                            budget, 
                            business_perpose,
                            upgrade_or_new,
                            period_status,
                            period_from,
                            period_to,
                            period,
                            availabiity_type,
                            priority,
                            required_date,
                            organization,
                            reason,
                            business_impact,
                            suppliers,
                            files,
                            item_details,
                            expected_conditions,
                            maintenance_kpi,
                            service_support_kpi,
                            consumables_kpi,
                            created_at,
                            updated_at
                        )
                        VALUES (
                            asset_requisition_id, 
                            item->>'itemName', 
                            item->>'assesttype', 
                            (item->>'quantity')::INTEGER, 
                            (item->>'budget')::NUMERIC, 
                            item->>'businessperpose',
                            item->>'upgradeOrNew', 
                            item->>'periodStatus', 
                            (item->>'periodFrom')::DATE, 
                            (item->>'periodTo')::DATE, 
                            item->>'period',
                            item->>'availabiityType',
                            item->>'priority', 
                            (item->>'requiredDate')::DATE, 
                            (item->>'organization')::BIGINT,
                            item->>'reason', 
                            item->>'businessImpact', 
                            (item->>'suppliers')::JSON,
                            (item->>'files')::JSON,
                            (item->>'itemDetails')::JSON,
                            (item->>'expected_conditions')::JSON, 
                            (item->>'maintenanceKpi')::JSON, 
                            (item->>'serviceSupportKpi')::JSON, 
                            (item->>'consumablesKPI')::JSON,
                            _current_time,
                            _current_time
                        );
                    END LOOP;
                END;
                \$\$;
                SQL;
                
            // Execute the SQL statement
            DB::unprepared($procedure);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS create_full_asset_requisition;');
    }
};
