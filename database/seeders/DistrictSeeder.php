<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\RdoDivision;
use App\Support\LocationCode;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    public function run(): void
    {
        $regions = $this->regions();
        $takenDistrictCodes = [];

        foreach ($regions as $districtName => $divisions) {
            $districtCode = LocationCode::fromName($districtName, $takenDistrictCodes);
            $takenDistrictCodes[] = $districtCode;

            $district = District::query()->updateOrCreate(
                ['name' => $districtName],
                [
                    'code' => $districtCode,
                    'status' => District::STATUS_ACTIVE,
                ],
            );

            $takenDivisionCodes = RdoDivision::query()->pluck('code')->all();
            $uniqueDivisions = [];

            foreach ($divisions as $divisionName) {
                $divisionName = trim($divisionName);

                if ($divisionName === '' || strcasecmp($divisionName, 'Plantation companies') === 0) {
                    continue;
                }

                $key = mb_strtolower($divisionName);

                if (isset($uniqueDivisions[$key])) {
                    continue;
                }

                $uniqueDivisions[$key] = $divisionName;
            }

            foreach ($uniqueDivisions as $divisionName) {
                $divisionCode = LocationCode::fromName($divisionName, $takenDivisionCodes);
                $takenDivisionCodes[] = $divisionCode;

                RdoDivision::query()->updateOrCreate(
                    [
                        'district_id' => $district->id,
                        'name' => $divisionName,
                    ],
                    [
                        'code' => $divisionCode,
                        'status' => RdoDivision::STATUS_ACTIVE,
                    ],
                );
            }
        }
    }

    /**
     * @return array<string, list<string>>
     */
    private function regions(): array
    {
        return [
            'Kegalle' => [
                'Amithirigala',
                'Warakapola',
                'Mawanella',
                'Kegalle',
                'Mirigama',
                'Dedigama',
                'Weliweriya',
                'Galagedara',
                'Algama',
                'Imbulana',
                'Dehiowita',
                'Rambukkana',
                'Aranayaka',
                'Arandara',
                'Weragoda',
                'Welikadamulla',
                'Alpitiya',
                'Alawwa',
                'Thulhiriya',
                'Tholangamuwa',
                'Galigamuwa',
                'Papiliyawala',
                'Dompe',
                'Matale',
                'Redeegama',
                'Rideegama',
                'Deraniyagala',
                'Vavuniya',
                'Attanagalla',
                'Hemmathagama',
                'Hettimulla',
                'Ganethenna',
                'Ruwanwella',
                'Kotiyakumbura',
                'Hatharaliyadda',
                'Panawala',
                'Algoda',
                'Kithulgala',
                'Yatiyanthota',
                'Ussapitiya',
                'Magammana',
            ],
            'Kalutara' => [
                'Mahaoya',
                'Baduraliya',
                'Hedigalla',
                'Morapitiya',
                'Walallawita',
                'Meegahathenna',
                'Agalawatta',
                'Mahagama',
                'Yatagampitiya',
                'Bulathsinhala',
                'Govinna',
                'Agaloya',
                'Madurawala',
                'Mathugama',
                'Welipenna',
                'Dodangoda',
                'Nebada',
                'Beruwala',
                'Ingiriya',
                'Poruwadanda',
                'Bandaragama',
                'Galpatha',
                'Horana',
                'Meewanapalana',
                'Millewa',
                'Milleniya',
            ],
            'Galle' => [
                'Baddegama',
                'Elpitiya',
                'Pitigala',
                'Thawalama',
                'Katuwana',
                'Sooriyawewa',
                'Walasmulla',
                'Akuressa',
                'Kamburupitiya',
                'Malimbada',
                'Mulatiyana',
                'Pitabeddara',
                'Mathara',
                'Hambanthota',
                'Galle',
            ],
            'Ratnapura' => [
                'Godakawela',
                'Embilipitiya',
                'Opanayaka',
                'Kalawana',
                'Nivithigala',
                'Pelmadulla',
                'Rathnapura',
                'Eheliyagoda',
                'Karadana',
                'Getahetta',
                'Erapola',
                'Ellawala',
                'Kiriella',
                'Kuruwita',
                'Parakaduwa',
                'Dumbara',
                'Ayagama',
                'Elapatha',
                'Niriella',
                'Kosgama',
                'Puwakpitiya',
                'Hanwella',
                'Bope',
                'Padukka',
                'Karandana',
            ],
            'Monaragala' => [
                'Monaragala 11',
                'Buttala',
                'Madulla',
                'Siyambalanduwa',
                'Medagama 1',
                'Medagama 11',
                'Badalkumbura 1',
                'Badalkumbura 11',
                'Badalkumbura 111',
                'Badalkumbura IV',
                'Badalkumbura V',
                'Wellawaya',
                'Badulla/Passara',
                'Lunugala',
                'Reedimaliyadda/Meegahakiwla/Kandaketiya/Mahiyangana',
                'Haldummulla',
                'Padiyatalawa/Dehiattakandiya',
                'Uhana/Ampara/Damana/Lahugala',
                'Padiyathalawa',
                'Bibila I',
                'Buththala',
                'Rideemaliyadda',
                'Padiyathalawa Nursery',
                'Kumbukkana Nursery',
            ],
        ];
    }
}
