<?php

namespace Database\Seeders;

use App\Models\AuditChecklistItem;
use Illuminate\Database\Seeder;

class AuditChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [AuditChecklistItem::MODULE_PSM, 'PSM-01', 'Approved crop protection products only', 'Confirm only approved products are used on the plantation.', 10],
            [AuditChecklistItem::MODULE_PSM, 'PSM-02', 'Safe storage of agrochemicals', 'Chemicals stored securely, labelled, and separated from food/water.', 20],
            [AuditChecklistItem::MODULE_PSM, 'PSM-03', 'Product handling hygiene', 'Harvest and processing hygiene practices are documented and followed.', 30],
            [AuditChecklistItem::MODULE_EMM, 'EMM-01', 'Soil and water protection', 'Erosion control and watercourse buffer practices are in place.', 10],
            [AuditChecklistItem::MODULE_EMM, 'EMM-02', 'Waste management', 'Farm waste is segregated and disposed without polluting soil or water.', 20],
            [AuditChecklistItem::MODULE_EMM, 'EMM-03', 'Biodiversity awareness', 'Natural vegetation and wildlife habitats around the farm are respected.', 30],
            [AuditChecklistItem::MODULE_WHSWM, 'WHSWM-01', 'Worker PPE availability', 'Workers have access to appropriate protective equipment.', 10],
            [AuditChecklistItem::MODULE_WHSWM, 'WHSWM-02', 'Safe working conditions', 'No unsafe tools, open pits, or unguarded machinery hazards observed.', 20],
            [AuditChecklistItem::MODULE_WHSWM, 'WHSWM-03', 'Fair labour practices', 'No child labour; workers understand wages and working hours.', 30],
            [AuditChecklistItem::MODULE_PMM, 'PMM-01', 'Traceability records', 'Farm keeps harvest/sales records suitable for QR traceability.', 10],
            [AuditChecklistItem::MODULE_PMM, 'PMM-02', 'Product identity integrity', 'Natural rubber lots are identifiable and not mixed with unknown sources.', 20],
            [AuditChecklistItem::MODULE_PMM, 'PMM-03', 'Market claims accuracy', 'Any sustainability claims match actual farm practices.', 30],
            [AuditChecklistItem::MODULE_GRM, 'GRM-01', 'Legal land / farm occupancy', 'Applicant can demonstrate legitimate farm occupancy or ownership.', 10],
            [AuditChecklistItem::MODULE_GRM, 'GRM-02', 'Map pin matches farm location', 'Pinned coordinates align with the farm visited / described.', 20],
            [AuditChecklistItem::MODULE_GRM, 'GRM-03', 'Application data accuracy', 'NIC, contacts, district/RDO, and farm details match field evidence.', 30],
        ];

        foreach ($items as [$module, $code, $label, $description, $sortOrder]) {
            AuditChecklistItem::query()->updateOrCreate(
                ['module' => $module, 'code' => $code],
                [
                    'label' => $label,
                    'description' => $description,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }
}
