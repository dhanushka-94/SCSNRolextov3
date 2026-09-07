<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Planter;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlanterSeeder extends Seeder
{
    public const PLANTER_COUNT = 200;

    public const PLANTER_PASSWORD = 'Planter@12345';

    public const PENDING_ONLINE = 45;

    public const PENDING_OFFLINE = 25;

    public const APPROVED_WITH_PASSWORD = 90;

    public const APPROVED_NO_PASSWORD = 20;

    public const REJECTED = 20;

    public function run(): void
    {
        $adminId = User::query()->where('email', UserSeeder::ADMIN_EMAIL)->value('id');
        $districtModels = District::query()
            ->with(['rdoDivisions' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        if ($districtModels->isEmpty()) {
            $this->command?->warn('No districts found. Run DistrictSeeder before PlanterSeeder.');

            return;
        }

        $locationPairs = [];

        foreach ($districtModels as $districtModel) {
            foreach ($districtModel->rdoDivisions as $division) {
                $locationPairs[] = [$districtModel, $division];
            }
        }

        if ($locationPairs === []) {
            $this->command?->warn('No RDO divisions found. Run DistrictSeeder before PlanterSeeder.');

            return;
        }

        $businessTypes = array_keys(Planter::businessTypes());
        $divisionSequences = [];
        $names = $this->namePool();

        for ($i = 1; $i <= self::PLANTER_COUNT; $i++) {
            $number = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            [$districtModel, $division] = $locationPairs[($i - 1) % count($locationPairs)];
            $districtCode = $districtModel->code;
            $divisionCode = $division->code;
            $name = $names[($i - 1) % count($names)].' '.$this->surnameFor($i);
            $emailSlug = strtolower(str_replace([' ', '.'], ['.', ''], $names[($i - 1) % count($names)])).'.'.$number;
            $year = (string) now()->year;
            $temporaryId = 'TMP-'.$year.'-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT);
            $pendingCutoff = self::PENDING_ONLINE + self::PENDING_OFFLINE;
            $isPending = $i <= $pendingCutoff;

            $registrationId = null;

            if (! $isPending) {
                $sequenceKey = $districtCode.'/'.$divisionCode;
                $divisionSequences[$sequenceKey] = ($divisionSequences[$sequenceKey] ?? 0) + 1;
                $sequence = str_pad((string) $divisionSequences[$sequenceKey], 5, '0', STR_PAD_LEFT);
                $registrationId = 'RUB/SUS/'.$districtCode.'/'.$divisionCode.'/'.$sequence;
            }

            $data = array_merge($this->applicationDefaults($i, $name, $emailSlug, $districtModel, $division, $businessTypes), [
                'identification_number' => $registrationId,
                'temporary_id' => $temporaryId,
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
            } elseif ($i <= $pendingCutoff) {
                $data['registration_type'] = Planter::TYPE_OFFLINE;
            } elseif ($i <= $pendingCutoff + self::APPROVED_WITH_PASSWORD) {
                $data['status'] = Planter::STATUS_APPROVED;
                $data['password'] = self::PLANTER_PASSWORD;
                $data['approved_by'] = $adminId;
                $data['approved_at'] = now()->subDays(max(1, self::PLANTER_COUNT - $i));
                $data['audit_status'] = Planter::AUDIT_NOT_STARTED;

                if ($i % 7 === 0) {
                    $data['last_login_at'] = now()->subHours($i % 48);
                }
            } elseif ($i <= $pendingCutoff + self::APPROVED_WITH_PASSWORD + self::APPROVED_NO_PASSWORD) {
                $data['status'] = Planter::STATUS_APPROVED;
                $data['approved_by'] = $adminId;
                $data['approved_at'] = now()->subDays(2);
                $data['audit_status'] = Planter::AUDIT_NOT_STARTED;
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
    private function applicationDefaults(
        int $index,
        string $name,
        string $emailSlug,
        District $districtModel,
        ?\App\Models\RdoDivision $division,
        array $businessTypes,
    ): array {
        $district = $districtModel->name;
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
            'district_id' => $districtModel->id,
            'rdd_division' => $division?->name,
            'rdo_division_id' => $division?->id,
            'farm_name' => $name.' Rubber Estate',
            'address' => $town.', '.$district,
            ...$this->coordinatesFor($district, $index),
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

    /**
     * @return array{latitude: float, longitude: float}
     */
    private function coordinatesFor(string $district, int $index): array
    {
        $centres = [
            'Kegalle' => [7.2513, 80.3464],
            'Kalutara' => [6.5854, 79.9607],
            'Galle' => [6.0535, 80.2210],
            'Ratnapura' => [6.6828, 80.4012],
            'Monaragala' => [6.8726, 81.3509],
        ];

        [$baseLat, $baseLng] = $centres[$district] ?? [7.8731, 80.7718];
        $jitter = (($index % 17) - 8) * 0.012;

        return [
            'latitude' => round($baseLat + $jitter, 7),
            'longitude' => round($baseLng + (($index % 13) - 6) * 0.012, 7),
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
