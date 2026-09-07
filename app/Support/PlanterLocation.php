<?php

namespace App\Support;

use App\Models\District;
use App\Models\RdoDivision;
use Illuminate\Validation\Rule;

class PlanterLocation
{
    /**
     * @return list<mixed>
     */
    public static function districtIdRules(bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'integer',
            Rule::exists('districts', 'id')->where(fn ($query) => $query->where('status', District::STATUS_ACTIVE)),
        ];
    }

    /**
     * @return list<mixed>
     */
    public static function rdoDivisionIdRules(bool $required = true, string $districtIdField = 'district_id'): array
    {
        return [
            $required ? 'required' : 'nullable',
            'integer',
            Rule::exists('rdo_divisions', 'id')->where(fn ($query) => $query->where('status', RdoDivision::STATUS_ACTIVE)),
            function (string $attribute, mixed $value, \Closure $fail) use ($districtIdField): void {
                if (blank($value)) {
                    return;
                }

                $districtId = request()->input($districtIdField);
                $matches = RdoDivision::query()
                    ->whereKey($value)
                    ->where('district_id', $districtId)
                    ->where('status', RdoDivision::STATUS_ACTIVE)
                    ->exists();

                if (! $matches) {
                    $fail('The selected Rubber Development Officer division is invalid for the chosen district.');
                }
            },
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function hydrateNames(array $data): array
    {
        if (! empty($data['district_id'])) {
            $district = District::query()->find($data['district_id']);
            $data['district'] = $district?->name;
        }

        if (! empty($data['rdo_division_id'])) {
            $division = RdoDivision::query()->find($data['rdo_division_id']);
            $data['rdd_division'] = $division?->name;
        } elseif (array_key_exists('rdo_division_id', $data) && blank($data['rdo_division_id'])) {
            $data['rdd_division'] = null;
        }

        return $data;
    }

    /**
     * @return array<int|string, list<array{id:int, name:string, code:string}>>
     */
    public static function activeDivisionMap(): array
    {
        return RdoDivision::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'district_id', 'name', 'code'])
            ->groupBy(fn (RdoDivision $row) => (string) $row->district_id)
            ->map(fn ($rows) => $rows->map(fn (RdoDivision $row) => [
                'id' => $row->id,
                'name' => $row->name,
                'code' => $row->code,
            ])->values()->all())
            ->all();
    }
}
