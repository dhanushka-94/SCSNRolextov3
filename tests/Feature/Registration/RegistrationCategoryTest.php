<?php

namespace Tests\Feature\Registration;

use App\Models\District;
use App\Models\Planter;
use App\Models\RdoDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationCategoryTest extends TestCase
{
    use RefreshDatabase;

    private District $district;

    private RdoDivision $division;

    protected function setUp(): void
    {
        parent::setUp();

        // The reference districts and RDO divisions are created by migrations, so use an existing active pair.
        $this->district = District::query()
            ->where('status', District::STATUS_ACTIVE)
            ->where('name', 'Kegalle')
            ->firstOrFail();

        $this->division = RdoDivision::query()
            ->where('district_id', $this->district->id)
            ->where('status', RdoDivision::STATUS_ACTIVE)
            ->firstOrFail();
    }

    public function test_registration_opens_the_three_category_chooser_in_three_languages(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Sole Proprietor Registration')
            ->assertSee('Group Registration')
            ->assertSee('Company Registration')
            ->assertSee('තනි හිමිකරු ලියාපදිංචිය')
            ->assertSee('කණ්ඩායම් ලියාපදිංචිය')
            ->assertSee('සමාගම් ලියාපදිංචිය')
            ->assertSee('தனி உரிமையாளர் பதிவு')
            ->assertSee('குழு பதிவு')
            ->assertSee('நிறுவனப் பதிவு')
            ->assertSee('/register/apply/solo', false)
            ->assertSee('/register/apply/group', false)
            ->assertSee('/register/apply/company', false);
    }

    public function test_each_category_opens_its_own_form(): void
    {
        foreach (['solo', 'group', 'company'] as $category) {
            $this->get('/register/apply/'.$category)
                ->assertOk()
                ->assertSee('name="registration_category" value="'.$category.'"', false);
        }
    }

    public function test_an_unknown_category_is_not_found(): void
    {
        $this->get('/register/apply/other')->assertNotFound();
        $this->get('/register/apply/%20')->assertNotFound();
    }

    public function test_solo_form_hides_the_group_section_and_others_show_it(): void
    {
        $this->get('/register/apply/solo')
            ->assertOk()
            ->assertDontSee('name="group_name"', false);

        $this->get('/register/apply/group')
            ->assertOk()
            ->assertSee('name="group_name"', false)
            ->assertSee('Group or society name');

        $this->get('/register/apply/company')
            ->assertOk()
            ->assertSee('Company name');
    }

    public function test_company_registration_is_stored_as_company_even_if_the_form_sends_another_type(): void
    {
        $this->submitOnline(['registration_category' => 'company', 'business_type' => 'individual', 'group_name' => 'Hevea Estates Ltd'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('planter.register.submitted'));

        $this->assertDatabaseHas('planters', [
            'registration_category' => 'company',
            'business_type' => Planter::BUSINESS_COMPANY,
            'group_name' => 'Hevea Estates Ltd',
        ]);
    }

    public function test_group_registration_maps_to_society(): void
    {
        $this->submitOnline(['registration_category' => 'group', 'group_name' => 'Kegalle Planters Society'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('planters', [
            'registration_category' => 'group',
            'business_type' => Planter::BUSINESS_SOCIETY,
            'group_name' => 'Kegalle Planters Society',
        ]);
    }

    public function test_solo_registration_maps_to_individual_and_drops_group_details(): void
    {
        $this->submitOnline([
            'registration_category' => 'solo',
            'business_type' => 'company',
            'group_name' => 'Should not be kept',
            'group_address' => 'Should not be kept either',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('planters', [
            'registration_category' => 'solo',
            'business_type' => Planter::BUSINESS_INDIVIDUAL,
            'group_name' => null,
            'group_address' => null,
        ]);
    }

    public function test_group_and_company_need_a_name(): void
    {
        $this->submitOnline(['registration_category' => 'group', 'group_name' => ''])
            ->assertSessionHasErrors('group_name');

        $this->submitOnline(['registration_category' => 'company', 'group_name' => ''])
            ->assertSessionHasErrors('group_name');

        $this->assertDatabaseCount('planters', 0);
    }

    public function test_a_category_is_required_for_online_and_paper_registration(): void
    {
        $this->submitOnline(['registration_category' => null])
            ->assertSessionHasErrors('registration_category');

        $this->withSession(['planter_registration_started_at' => now()->subMinutes(1)->timestamp])
            ->post('/register/offline', [
                'name' => 'Paper Applicant',
                'nic' => '901234567V',
                'phone' => '0771234567',
                'district_id' => $this->district->id,
                'rdo_division_id' => $this->division->id,
            ])
            ->assertSessionHasErrors('registration_category');
    }

    public function test_paper_registration_stores_the_category(): void
    {
        $this->withSession(['planter_registration_started_at' => now()->subMinutes(1)->timestamp])
            ->post('/register/offline', [
                'registration_category' => 'company',
                'name' => 'Paper Applicant',
                'nic' => '901234567V',
                'phone' => '0771234567',
                'district_id' => $this->district->id,
                'rdo_division_id' => $this->division->id,
                'application_document' => \Illuminate\Http\UploadedFile::fake()->create('form.pdf', 50, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('planters', ['nic' => '901234567V', 'registration_category' => 'company']);
    }

    public function test_mobile_api_needs_the_category_and_sets_the_business_type(): void
    {
        $this->postJson('/api/v1/registrations', $this->payload(['registration_category' => null]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('registration_category');

        $this->postJson('/api/v1/registrations', $this->payload([
            'registration_category' => 'group',
            'business_type' => 'individual',
            'group_name' => 'Api Planters Society',
        ]))
            ->assertCreated();

        $this->assertDatabaseHas('planters', [
            'registration_category' => 'group',
            'business_type' => Planter::BUSINESS_SOCIETY,
        ]);
    }

    public function test_reference_options_list_the_categories_for_the_app(): void
    {
        $this->getJson('/api/v1/reference/options')
            ->assertOk()
            ->assertJsonCount(3, 'registration_categories')
            ->assertJsonPath('registration_categories.0.value', 'solo')
            ->assertJsonPath('registration_categories.0.business_type', 'individual')
            ->assertJsonPath('registration_categories.1.business_type', 'society')
            ->assertJsonPath('registration_categories.2.business_type', 'company');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function submitOnline(array $overrides = [])
    {
        return $this->withSession(['planter_registration_started_at' => now()->subMinutes(1)->timestamp])
            ->post('/register', $this->payload($overrides));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'registration_category' => 'solo',
            'name' => 'Test Applicant '.fake()->unique()->numerify('###'),
            'nic' => fake()->unique()->numerify('9#########V'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0771234567',
            'district_id' => $this->district->id,
            'rdo_division_id' => $this->division->id,
            'farm_name' => 'Test Farm',
            'address' => 'Test road, Kegalle',
            'latitude' => 7.2513,
            'longitude' => 80.3464,
            'business_type' => 'individual',
            'already_certified' => false,
            'certification_rejected_or_suspended' => false,
            'crops_products' => ['Rubber sheets'],
            'aware_of_certification' => true,
            'has_certification_leaflet' => true,
            'processes_rubber_on_farm' => false,
        ], $overrides);
    }
}
