<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE STORE_PROCEDURE_GET_APPROVED_ASSET_REQUISITION(
                p_user_id INT
            )
            LANGUAGE plpgsql
            AS $$
            DECLARE
                requisition_record RECORD;
                result JSONB := '[]'::jsonb;
            BEGIN
                DROP TABLE IF EXISTS approved_asset_requisitions_from_store_procedure;
                CREATE TABLE IF NOT EXISTS approved_asset_requisitions_from_store_procedure (
                    requisition_data JSONB NOT NULL
                );
                
                FOR requisition_record IN
                    SELECT 
                        ar.id AS requisition_id,
                        ar.requisition_id AS requisition_code,
                        ar.requisition_by,
                        ar.requisition_date,
                        ar.requisition_status,
                        ar.created_at AS requisition_created_at,
                        ar.updated_at AS requisition_updated_at,
                        (
                            SELECT json_build_object(
                                'name', u.name,
                                'username', u.user_name,
                                'contact_no', u.contact_no,
                                'profie_image', u.profie_image,
                                'email', u.email,
                                'address', u.address,
                                'employee_code', u.employee_code,
                                'user_description', u.user_description
                            )
                            FROM public.users u
                            WHERE u.id = ar.requisition_by
                        ) AS user,
                        json_agg(
                            json_build_object(
                                'id', ari.id,
                                'item_name', ari.item_name,
                                'asset_type', at.name,
                                'files', ari.files,
                                'budget', ari.budget,
                                'period', ari.period,
                                'reason', ari.reason,
                                'priority', ari.priority,
                                'quantity', ari.quantity,
                                'period_to', ari.period_to,
                                'suppliers', ari.suppliers,
                                'created_at', ari.created_at,
                                'updated_at', ari.updated_at,
                                'period_from', ari.period_from,
                                'item_details', ari.item_details,
                                'organization', (SELECT 
                                    data -> 'organizationName' AS organization_name
                                    FROM public.organization
                                    WHERE id = ari.organization),
                                'period_status', ari.period_status,
                                'required_date', ari.required_date,
                                'upgrade_or_new', ari.upgrade_or_new,
                                'business_impact', ari.business_impact,
                                'consumables_kpi', ari.consumables_kpi,
                                'maintenance_kpi', ari.maintenance_kpi,
                                'availabiity_type', ari.availabiity_type,
                                'business_purpose', ari.business_perpose,
                                'expected_conditions', ari.expected_conditions,
                                'service_support_kpi', ari.service_support_kpi,
                                'asset_requisition_id', ari.asset_requisition_id
                            )
                        ) AS items
                    FROM 
                        public.asset_requisitions ar
                    JOIN public.asset_requisitions_items ari ON ar.id = ari.asset_requisition_id
                    JOIN public.assets_types at ON ari.assesttype = at.name
                    JOIN public.procurement_staff ps ON at.id = ps.asset_type_id
                    WHERE 
                        ar.requisition_status = 'APPROVED'
                        AND ar.requisition_by = p_user_id
                    GROUP BY 
                        ar.id, ar.requisition_id, ar.requisition_by, ar.requisition_date, 
                        ar.requisition_status, ar.created_at, ar.updated_at
                LOOP
                    result := result || jsonb_build_object(
                        'id', requisition_record.requisition_id,
                        'user', requisition_record.user,
                        'items', requisition_record.items,
                        'created_at', requisition_record.requisition_created_at,
                        'updated_at', requisition_record.requisition_updated_at,
                        'requisition_by', requisition_record.requisition_by,
                        'requisition_id', requisition_record.requisition_code,
                        'requisition_date', requisition_record.requisition_date,
                        'requisition_status', requisition_record.requisition_status
                    )::jsonb;
                END LOOP;

                RAISE NOTICE 'Requisitions: %', result;
                INSERT INTO approved_asset_requisitions_from_store_procedure (requisition_data)
                VALUES (result);
            END;
            $$;"
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS STORE_PROCEDURE_GET_APPROVED_ASSET_REQUISITION');
    }
};