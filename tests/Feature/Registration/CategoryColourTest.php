<?php

namespace Tests\Feature\Registration;

use App\Models\Planter;
use App\Models\User;
use App\Services\RegistrationFormPdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Tests\TestCase;

class CategoryColourTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_category_has_its_own_colour(): void
    {
        $styles = Planter::categoryStyles();

        $this->assertCount(3, $styles);
        $this->assertCount(3, array_unique(array_column($styles, 'badge')));
        $this->assertCount(3, array_unique(array_column($styles, 'bar')));

        $planter = new Planter(['registration_category' => Planter::CATEGORY_COMPANY]);
        $this->assertSame($styles[Planter::CATEGORY_COMPANY], $planter->categoryStyle());
        $this->assertSame('Not set', (new Planter)->categoryStyle()['label']);
    }

    public function test_lobby_filters_by_category_and_shows_counts(): void
    {
        $admin = User::factory()->admin()->create();
        $this->planter('Solo Sam', Planter::CATEGORY_SOLO);
        $this->planter('Group Gina', Planter::CATEGORY_GROUP);
        $this->planter('Company Cora', Planter::CATEGORY_COMPANY);

        $all = $this->actingAs($admin, 'web')->get(route('admin.planters.registration-lobby'))->assertOk()->getContent();
        foreach (['Solo Sam', 'Group Gina', 'Company Cora'] as $name) {
            $this->assertTrue(str_contains($all, $name), "$name missing from the full lobby.");
        }

        $group = $this->actingAs($admin, 'web')
            ->get(route('admin.planters.registration-lobby', ['category' => 'group']))
            ->assertOk()
            ->getContent();

        $this->assertTrue(str_contains($group, 'Group Gina'));
        $this->assertFalse(str_contains($group, 'Solo Sam'));
        $this->assertFalse(str_contains($group, 'Company Cora'));
        $this->assertTrue(str_contains($group, 'border-l-amber-500'), 'Group rows should carry the amber accent.');

        // Unknown values are ignored instead of hiding everything.
        $junk = $this->actingAs($admin, 'web')
            ->get(route('admin.planters.registration-lobby', ['category' => 'nonsense']))
            ->assertOk()
            ->getContent();
        $this->assertTrue(str_contains($junk, 'Solo Sam'));
    }

    public function test_registry_filters_by_category(): void
    {
        $admin = User::factory()->admin()->create();
        $this->planter('Solo Sam', Planter::CATEGORY_SOLO, Planter::STATUS_APPROVED);
        $this->planter('Company Cora', Planter::CATEGORY_COMPANY, Planter::STATUS_APPROVED);

        $html = $this->actingAs($admin, 'web')
            ->get(route('admin.planters.index', ['category' => 'company']))
            ->assertOk()
            ->getContent();

        $this->assertTrue(str_contains($html, 'Company Cora'));
        $this->assertFalse(str_contains($html, 'Solo Sam'));
        $this->assertTrue(str_contains($html, 'border-l-violet-500'));
    }

    public function test_create_form_offers_the_three_types(): void
    {
        $admin = User::factory()->admin()->create();

        $html = $this->actingAs($admin, 'web')->get(route('admin.planters.create'))->assertOk()->getContent();

        foreach (['border-l-sky-500', 'border-l-amber-500', 'border-l-violet-500'] as $bar) {
            $this->assertTrue(str_contains($html, $bar), "$bar missing from the create form.");
        }
    }

    public function test_staff_must_pick_a_type_when_creating(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')
            ->post(route('admin.planters.store'), ['name' => 'No Type'])
            ->assertSessionHasErrors('registration_category');
    }

    public function test_older_records_without_a_type_can_still_be_edited(): void
    {
        $admin = User::factory()->admin()->create();
        $planter = $this->planter('Existing', null);

        $this->actingAs($admin, 'web')
            ->get(route('admin.planters.edit', $planter))
            ->assertOk();
    }

    public function test_paper_form_downloads_as_a_pdf_with_tamil_and_all_three_types(): void
    {
        $response = $this->get(route('planter.register.form'))->assertOk();

        $this->assertStringContainsString('pdf', (string) $response->headers->get('Content-Type'));
        $this->assertTrue(str_starts_with($this->downloadedBytes($response), '%PDF'));

        // The fallback (DomPDF) page lists the three registration types in all three languages.
        $html = view('planter.pdf.registration-form', RegistrationFormPdf::viewData())->render();
        foreach (Planter::registrationCategories() as $category) {
            foreach (['si', 'en', 'ta'] as $language) {
                $this->assertTrue(str_contains($html, $category[$language]), "Missing {$language} name for {$category['en']}.");
            }
        }
        $this->assertTrue(str_contains($html, 'NotoTamil'));
    }

    private function downloadedBytes($response): string
    {
        return $response->baseResponse instanceof BinaryFileResponse
            ? (string) file_get_contents($response->baseResponse->getFile()->getPathname())
            : $response->getContent();
    }

    private function planter(string $name, ?string $category, string $status = Planter::STATUS_PENDING): Planter
    {
        return Planter::query()->create([
            'temporary_id' => Planter::generateTemporaryId(),
            'identification_number' => $status === Planter::STATUS_APPROVED ? 'RUB/SUS/KEGA/TEST/'.fake()->unique()->numerify('#####') : null,
            'name' => $name,
            'nic' => fake()->unique()->numerify('9#########V'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '0771234567',
            'district' => 'Kegalle',
            'address' => 'Test road',
            'password' => 'Planter-Pass-2026',
            'status' => $status,
            'registration_type' => Planter::TYPE_ONLINE,
            'registration_category' => $category,
            'audit_status' => Planter::AUDIT_NOT_STARTED,
        ]);
    }
}
