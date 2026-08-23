<?php

namespace Database\Seeders;

use App\Models\Planter;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanterSeeder extends Seeder
{
    public const PLANTER_COUNT = 120;

    public const PLANTER_PASSWORD = 'Planter@12345';

    public const PENDING_ONLINE = 30;

    public const PENDING_OFFLINE = 15;

    public const APPROVED_WITH_PASSWORD = 55;

    public const APPROVED_NO_PASSWORD = 10;

    public const REJECTED = 10;

    public function run(): void
    {
        $adminId = User::query()->where('email', UserSeeder::ADMIN_EMAIL)->value('id');
        $districts = Planter::districts();
        $businessTypes = array_keys(Planter::businessTypes());
        $year = (string) now()->year;
        $districtSequences = [];
        $auditStages = [
            Planter::AUDIT_NOT_STARTED,
            Planter::AUDIT_OPEN,
            Planter::AUDIT_IN_PROGRESS,
            Planter::AUDIT_IN_REVIEW,
            Planter::AUDIT_RESULT,
        ];
        $auditOutcomes = [
            Planter::AUDIT_OUTCOME_CERTIFIED,
            Planter::AUDIT_OUTCOME_CONDITIONAL,
            Planter::AUDIT_OUTCOME_NOT_CERTIFIED,
        ];
        $names = $this->namePool();

        for ($i = 1; $i <= self::PLANTER_COUNT; $i++) {
            $number = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $district = $districts[($i - 1) % count($districts)];
            $districtCode = Planter::districtCode($district);
            $name = $names[($i - 1) % count($names)].' '.$this->surnameFor($i);
            $emailSlug = strtolower(str_replace([' ', '.'], ['.', ''], $names[($i - 1) % count($names)])).'.'.$number;

            $districtSequences[$districtCode] = ($districtSequences[$districtCode] ?? 0) + 1;
            $sequence = str_pad((string) $districtSequences[$districtCode], 4, '0', STR_PAD_LEFT);
            $scsnrId = 'SCSNR/Ad/'.$districtCode.'/'.$year.'/'.$sequence;

            $data = array_merge($this->applicationDefaults($i, $name, $emailSlug, $district, $businessTypes), [
                'identification_number' => $scsnrId,
                'temporary_id' => $scsnrId,
                'status' => Planter::STATUS_PENDING,
                'registration_type' => Planter::TYPE_ONLINE,
                'password' => null,
                'audit_status' => Planter::AUDIT_NOT_STARTED,
                'audit_result_outcome' => null,
                'audit_result_notes' => null,
                'audit_status_updated_at' => null,
                'rejection_reason' => null,
                'approved_by' => null,
                'approved_at' => null,
                'last_login_at' => null,
                'created_at' => now()->subDays(self::PLANTER_COUNT - $i),
            ]);

            if ($i <= self::PENDING_ONLINE) {
                $data['registration_type'] = Planter::TYPE_ONLINE;
            } elseif ($i <= self::PENDING_ONLINE + self::PENDING_OFFLINE) {
                $data['registration_type'] = Planter::TYPE_OFFLINE;
            } elseif ($i <= self::PENDING_ONLINE + self::PENDING_OFFLINE + self::APPROVED_WITH_PASSWORD) {
                $auditStage = $auditStages[($i - 1) % count($auditStages)];

                $data['status'] = Planter::STATUS_APPROVED;
                $data['password'] = self::PLANTER_PASSWORD;
                $data['approved_by'] = $adminId;
                $data['approved_at'] = now()->subDays(max(1, self::PLANTER_COUNT - $i));
                $data['audit_status'] = $auditStage;
                $data['audit_status_updated_at'] = now()->subDays(max(1, (int) floor((self::PLANTER_COUNT - $i) / 2)));

                if ($auditStage === Planter::AUDIT_RESULT) {
                    $outcome = $auditOutcomes[($i - 1) % count($auditOutcomes)];
                    $data['audit_result_outcome'] = $outcome;
                    $data['audit_result_notes'] = match ($outcome) {
                        Planter::AUDIT_OUTCOME_CERTIFIED => 'Certified under SCSNR audit programme.',
                        Planter::AUDIT_OUTCOME_CONDITIONAL => 'Conditional certification pending corrective actions.',
                        Planter::AUDIT_OUTCOME_NOT_CERTIFIED => 'Not certified — audit requirements not fully met.',
                    };
                }

                if ($i % 7 === 0) {
                    $data['last_login_at'] = now()->subHours($i % 48);
                }
            } elseif ($i <= self::PENDING_ONLINE + self::PENDING_OFFLINE + self::APPROVED_WITH_PASSWORD + self::APPROVED_NO_PASSWORD) {
                $data['status'] = Planter::STATUS_APPROVED;
                $data['approved_by'] = $adminId;
                $data['approved_at'] = now()->subDays(2);
                $data['audit_status'] = Planter::AUDIT_OPEN;
                $data['audit_status_updated_at'] = now()->subDay();
            } else {
                $data['status'] = Planter::STATUS_REJECTED;
                $data['rejection_reason'] = 'Application rejected — incomplete or invalid supporting documents.';
                $data['approved_by'] = $adminId;
                $data['approved_at'] = now()->subDays(3);
            }

            Planter::query()->create($data);
        }
    }

    /**
     * @param  list<string>  $businessTypes
     * @return array<string, mixed>
     */
    private function applicationDefaults(int $index, string $name, string $emailSlug, string $district, array $businessTypes): array
    {
        $towns = ['Town Centre', 'Estate Junction', 'Village Road', 'Plantation Lane', 'Rubber Colony'];
        $town = $towns[$index % count($towns)];
        $phoneSuffix = str_pad((string) (3000000 + $index), 7, '0', STR_PAD_LEFT);
        $businessType = $businessTypes[$index % count($businessTypes)];
        $alreadyCertified = $index % 5 === 0;
        $processesOnFarm = $index % 2 === 0;

        return [
            'name' => $name,
            'nic' => $this->nicFor($index),
            'email' => $emailSlug.'@sample.lk',
            'phone' => '077'.$phoneSuffix,
            'whatsapp' => '077'.$phoneSuffix,
            'fax' => $index % 4 === 0 ? '011'.substr($phoneSuffix, 0, 7) : null,
            'district' => $district,
            'rdd_division' => $district.' RDD Division',
            'farm_name' => $name.' Rubber Estate',
            'address' => $town.', '.$district,
            'business_type' => $businessType,
            'already_certified' => $alreadyCertified,
            'certification_standard' => $alreadyCertified ? 'SCSNR Natural Rubber' : null,
            'prior_certificate_document' => null,
            'certification_rejected_or_suspended' => $index % 11 === 0,
            'certification_issue_reason' => $index % 11 === 0 ? 'Prior certification suspended pending review.' : null,
            'crops_products' => [
                ['name' => 'Natural rubber (latex)', 'area' => (string) (2 + ($index % 6)), 'quantity' => (string) (750 + ($index * 40))],
                ...($index % 3 === 0 ? [['name' => 'Rubber timber', 'area' => '1', 'quantity' => (string) (100 + $index)]] : []),
            ],
            'aware_of_certification' => true,
            'has_certification_leaflet' => $index % 2 === 1,
            'processes_rubber_on_farm' => $processesOnFarm,
            'has_process_plan' => $processesOnFarm
                ? ($index % 3 === 0 ? Planter::PROCESS_PLAN_YES : Planter::PROCESS_PLAN_NO)
                : Planter::PROCESS_PLAN_NA,
            'group_name' => $businessType !== Planter::BUSINESS_INDIVIDUAL ? $name.' Group' : null,
            'group_address' => $businessType !== Planter::BUSINESS_INDIVIDUAL ? $town.', '.$district : null,
            'application_document' => null,
        ];
    }

    private function nicFor(int $number): string
    {
        if ($number % 3 === 0) {
            return str_pad((string) (900000000 + $number), 9, '0', STR_PAD_LEFT).'V';
        }

        return '19'.str_pad((string) (8000000000 + $number), 10, '0', STR_PAD_LEFT);
    }

    private function surnameFor(int $number): string
    {
        $surnames = [
            'Perera', 'Fernando', 'Silva', 'Jayawardena', 'Bandara', 'Kumara',
            'Wickramasinghe', 'Ratnayake', 'Dissanayake', 'Mendis', 'Peiris', 'Alwis',
        ];

        return $surnames[($number - 1) % count($surnames)];
    }

    /**
     * @return list<string>
     */
    private function namePool(): array
    {
        return [
            'Sunil', 'Nimal', 'Kamal', 'Ruwan', 'Chaminda', 'Lasitha', 'Priyantha', 'Anura', 'Saman',
            'Malith', 'Dilshan', 'Tharaka', 'Nuwan', 'Sanjeewa', 'Indika', 'Roshan', 'Ajith', 'Upul',
            'Kumari', 'Sanduni', 'Nadeesha', 'Tharindu', 'Ravi', 'Sachini', 'Dinesh', 'Mahesh', 'Lahiru',
            'Pasindu', 'Isuru', 'Kavindu', 'Sajith', 'Damith', 'Nuwantha', 'Chathura', 'Pradeep', 'Asanka',
        ];
    }
}
