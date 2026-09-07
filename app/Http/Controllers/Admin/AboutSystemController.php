<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Planter;
use App\Models\RdoDivision;
use App\Services\PlanterIdentityService;
use Illuminate\View\View;

class AboutSystemController extends Controller
{
    public function __invoke(): View
    {
        $exampleDistrict = District::query()->orderBy('name')->first();
        $exampleDivision = $exampleDistrict
            ? RdoDivision::query()->where('district_id', $exampleDistrict->id)->orderBy('name')->first()
            : null;

        $districtCode = $exampleDistrict?->code ?: 'KEGA';
        $divisionCode = $exampleDivision?->code ?: 'AMIT';
        $exampleNumber = PlanterIdentityService::ID_PREFIX.'/'.$districtCode.'/'.$divisionCode.'/00001';

        return view('about-system', [
            'exampleDistrict' => $exampleDistrict,
            'exampleDivision' => $exampleDivision,
            'districtCode' => $districtCode,
            'divisionCode' => $divisionCode,
            'exampleNumber' => $exampleNumber,
            'sequencePad' => PlanterIdentityService::SEQUENCE_PAD,
            'districtCount' => District::query()->count(),
            'divisionCount' => RdoDivision::query()->count(),
            'planterCount' => Planter::query()->count(),
        ]);
    }
}
