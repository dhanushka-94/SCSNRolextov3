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
        $year = (string) now()->year;
        $districtSequences = [];
        $auditStages = [
            Planter::AUDIT_NOT_STARTED,
            Planter::AUDIT_OPEN,
            Planter::AUDIT_IN_PROGRESS,
            Planter::AUDIT_IN_REVIEW,
            Planter::AUDIT_RESULT,
        ];

        for ($i = 1; $i <= self::PLANTER_COUNT; $i++) {
            $number = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $district = $districts[($i - 1) % count($districts)];
            $districtCode = Planter::districtCode($district);

            if (! isset($districtSequences[$districtCode])) {
                $districtSequences[$districtCode] = 0;
            }

            $districtSequences[$districtCode]++;
            $sequence = str_pad((string) $districtSequences[$districtCode], 4, '0', STR_PAD_LEFT);

            $data = [
                'temporary_id' => 'TMP-'.$year.'-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'name' => 'Sample Planter '.$number,
                'nic' => $this->nicFor($i),
                'email' => 'planter'.$number.'@sample.lk',
                'phone' => '077'.str_pad((string) (1000000 + $i), 7, '0', STR_PAD_LEFT),
                'district' => $district,
                'address' => 'Rubber estate lane '.$i.', '.$district,
                'registration_type' => Planter::TYPE_ONLINE,
                'application_document' => null,
                'password' => null,
                'status' => Planter::STATUS_PENDING,
                'audit_status' => Planter::AUDIT_NOT_STARTED,
                'audit_result_outcome' => null,
                'audit_result_notes' => null,
                'audit_status_updated_at' => null,
                'rejection_reason' => null,
                'identification_number' => null,
                'approved_by' => null,
                'approved_at' => null,
                'last_login_at' => null,
                'created_at' => now()->subDays(self::PLANTER_COUNT - $i),
            ];

            if ($i <= self::PENDING_ONLINE) {
                $data['registration_type'] = Planter::TYPE_ONLINE;
            } elseif ($i <= self::PENDING_ONLINE + self::PENDING_OFFLINE) {
                $data['registration_type'] = Planter::TYPE_OFFLINE;
            } elseif ($i <= self::PENDING_ONLINE + self::PENDING_OFFLINE + self::APPROVED_WITH_PASSWORD) {
                $auditStage = $auditStages[($i - 1) % count($auditStages)];

                $data['status'] = Planter::STATUS_APPROVED;
                $data['identification_number'] = 'SCSNR/Ad/'.$districtCode.'/'.$year.'/'.$sequence;
                $data['password'] = self::PLANTER_PASSWORD;
                $data['approved_by'] = $adminId;
                $data['approved_at'] = now()->subDays(max(1, self::PLANTER_COUNT - $i));
                $data['audit_status'] = $auditStage;
                $data['audit_status_updated_at'] = now()->subDays(max(1, (int) floor((self::PLANTER_COUNT - $i) / 2)));

                if ($auditStage === Planter::AUDIT_RESULT) {
                    $data['audit_result_outcome'] = Planter::AUDIT_OUTCOME_CERTIFIED;
                    $data['audit_result_notes'] = 'Sample certification outcome for demo planter '.$number.'.';
                }

                if ($i % 7 === 0) {
                    $data['last_login_at'] = now()->subHours($i % 48);
                }
            } elseif ($i <= self::PENDING_ONLINE + self::PENDING_OFFLINE + self::APPROVED_WITH_PASSWORD + self::APPROVED_NO_PASSWORD) {
                $data['status'] = Planter::STATUS_APPROVED;
                $data['identification_number'] = 'SCSNR/Ad/'.$districtCode.'/'.$year.'/'.$sequence;
                $data['approved_by'] = $adminId;
                $data['approved_at'] = now()->subDays(2);
                $data['audit_status'] = Planter::AUDIT_OPEN;
                $data['audit_status_updated_at'] = now()->subDay();
            } else {
                $data['status'] = Planter::STATUS_REJECTED;
                $data['rejection_reason'] = 'Sample rejection - documentation incomplete for demo planter '.$number.'.';
                $data['approved_by'] = $adminId;
                $data['approved_at'] = now()->subDays(3);
            }

            Planter::query()->create($data);
        }
    }

    private function nicFor(int $number): string
    {
        if ($number % 3 === 0) {
            return str_pad((string) (900000000 + $number), 9, '0', STR_PAD_LEFT).'V';
        }

        return '19'.str_pad((string) (8000000000 + $number), 10, '0', STR_PAD_LEFT);
    }
}
