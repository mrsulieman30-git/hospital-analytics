<?php

namespace Tests\Feature;

use App\Models\HospitalSetting;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SopPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        // Seed SOPs
        Storage::fake('public');
    }

    public function test_superadmin_can_access_sop_portal_and_view_procedures(): void
    {
        $superadmin = User::where('email', 'superadmin@kaafi.org')->first();

        // Create a test SOP
        $sop = Sop::create([
            'title' => 'Emergency Triage Protocol for Acute Resuscitation',
            'sop_code' => 'SOP-EMR-001',
            'department' => 'emergency',
            'category' => 'Triage & Critical Care',
            'version' => '1.0',
            'file_path' => 'sops/test.pdf',
            'file_name' => 'test.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'is_downloadable' => true,
            'extracted_html' => '<p>Emergency triage steps</p>',
            'created_by' => $superadmin->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($superadmin)->get(route('sops.index'));
        $response->assertStatus(200);
        $response->assertSee('Standard Operating Procedures (SOPs)');
        $response->assertSee('Emergency Triage Protocol for Acute Resuscitation');

        // Can view SOP show page
        $showResponse = $this->actingAs($superadmin)->get(route('sops.show', $sop));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('Emergency triage steps', false);
    }

    public function test_superadmin_can_toggle_portal_on_and_off(): void
    {
        $superadmin = User::where('email', 'superadmin@kaafi.org')->first();

        // Default state should be active
        $response = $this->actingAs($superadmin)->patch(route('superadmin.sops.toggle'));
        $response->assertRedirect(route('sops.index'));

        $setting = HospitalSetting::current();
        $this->assertFalse($setting->extra_metadata['sop_portal_enabled'] ?? true);

        // Toggle back ON
        $response2 = $this->actingAs($superadmin)->patch(route('superadmin.sops.toggle'));
        $response2->assertRedirect(route('sops.index'));

        $setting->refresh();
        $this->assertTrue($setting->extra_metadata['sop_portal_enabled'] ?? false);
    }

    public function test_admin_and_staff_cannot_access_or_see_portal_when_turned_off(): void
    {
        $superadmin = User::where('email', 'superadmin@kaafi.org')->first();
        $admin = User::where('email', 'muse@kaafihospitals.org')->first();
        $pharmacyUser = User::factory()->create(['department' => 'pharmacy', 'role' => 'department_supervisor']);

        // Turn portal OFF
        $setting = HospitalSetting::current();
        $metadata = $setting->extra_metadata ?? [];
        $metadata['sop_portal_enabled'] = false;
        $setting->extra_metadata = $metadata;
        $setting->save();

        // Regular Admin visits /sops -> Must receive stealth 404
        $adminResponse = $this->actingAs($admin)->get('/sops');
        $adminResponse->assertStatus(404);

        // Staff visits /sops -> Must receive stealth 404
        $staffResponse = $this->actingAs($pharmacyUser)->get('/sops');
        $staffResponse->assertStatus(404);

        // Admin tries to toggle portal -> Must be unauthorized
        $toggleResponse = $this->actingAs($admin)->patch('/superadmin/sops/toggle');
        $toggleResponse->assertStatus(403);

        // SuperAdmin CAN still access portal when turned off to manage and preview
        $superadminResponse = $this->actingAs($superadmin)->get('/sops');
        $superadminResponse->assertStatus(200);
        $superadminResponse->assertSee('Portal: DISABLED');
    }

    public function test_download_permission_enforcement_by_superadmin(): void
    {
        $superadmin = User::where('email', 'superadmin@kaafi.org')->first();
        $admin = User::where('email', 'muse@kaafihospitals.org')->first();

        // Put a fake file in storage
        Storage::disk('public')->put('sops/test_restricted.pdf', 'dummy content');

        // Create restricted SOP (is_downloadable = false)
        $sop = Sop::create([
            'title' => 'Restricted Laboratory Protocol',
            'sop_code' => 'SOP-LAB-RESTRICTED',
            'department' => 'laboratory',
            'version' => '1.0',
            'file_path' => 'sops/test_restricted.pdf',
            'file_name' => 'test_restricted.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'is_downloadable' => false,
            'created_by' => $superadmin->id,
            'status' => 'approved',
        ]);

        // Regular admin tries to download -> Must receive 403 Forbidden
        $adminDownload = $this->actingAs($admin)->get(route('sops.download', $sop));
        $adminDownload->assertStatus(403);

        // Super admin tries to download -> Allowed
        $superDownload = $this->actingAs($superadmin)->get(route('sops.download', $sop));
        $superDownload->assertStatus(200);

        // SuperAdmin toggles download permission to ALLOWED
        $toggleDownload = $this->actingAs($superadmin)->patch(route('superadmin.sops.download.toggle', $sop));
        $toggleDownload->assertRedirect();
        $this->assertTrue($sop->fresh()->is_downloadable);

        // Now regular admin can download
        $adminDownloadAllowed = $this->actingAs($admin)->get(route('sops.download', $sop));
        $adminDownloadAllowed->assertStatus(200);
    }

    public function test_leaflet_photo_upload(): void
    {
        $superadmin = User::where('email', 'superadmin@kaafi.org')->first();

        $sop = Sop::create([
            'title' => 'CBC Testing Protocol',
            'sop_code' => 'SOP-LAB-CBC',
            'department' => 'laboratory',
            'version' => '1.0',
            'file_path' => 'sops/cbc.pdf',
            'file_name' => 'cbc.pdf',
            'file_type' => 'pdf',
            'file_size' => 1024,
            'is_downloadable' => true,
            'created_by' => $superadmin->id,
            'status' => 'approved',
        ]);

        $file = UploadedFile::fake()->create('leaflet_photo_1.png', 100, 'image/png');

        $response = $this->actingAs($superadmin)->post(route('sops.leaflets.upload', $sop), [
            'leaflets' => [$file],
        ]);

        $response->assertRedirect();
        $sop->refresh();
        $this->assertCount(1, $sop->leaflet_images);
    }

    public function test_sehtech_and_mohammad_sulieman_copyright_notice_present(): void
    {
        // 1. On login page
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('SEHTECH Healthcare Solutions');
        $loginResponse->assertSee('Mohammad Sulieman');

        // 2. On authenticated app pages (Dashboard)
        $admin = User::where('email', 'muse@kaafihospitals.org')->first();
        $dashboardResponse = $this->actingAs($admin)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('SEHTECH Healthcare Solutions');
        $dashboardResponse->assertSee('Mohammad Sulieman');
    }

    public function test_sop_template_auto_extraction_from_pasted_text(): void
    {
        $superadmin = User::where('email', 'superadmin@kaafi.org')->first();

        $templateText = <<<TXT
CREATINE KINASE-MB (CK-MB) — FINECARE™ FIA
KAAFI HOSPITALS — CLINICAL CHEMISTRY & CARDIAC BIOMARKERS
Benchtop Standard Operating Procedure: Creatine Kinase-MB (CK-MB)

Document Control Information\tDetails
Document Code\tKH-LAB-SOP-009-TEST
Effective Date\tOctober 2026
Version\t1.0 Bench Quick Reference
Kit Brand\tFinecare™ CK-MB Rapid Quantitative Test (Wondfo Biotech, Cat. No. W205)
Department\tClinical Chemistry / Emergency Diagnostics
Laboratory Manager\tMohammad Sulieman Ibrahim
Hospital Manager\tMuse Mohammed Abdulahi

________________________________________
1. Sample Type & Specimen Handling
●\tSpecimen Requirements: Human Serum, Plasma, or Venous Whole Blood.
●\tRejection Criteria: Hemolyzed samples, clotted whole blood, or specimens collected in citrate/unapproved anticoagulants.

________________________________________
3. System Parameters
Parameter\tSpecification / Operational Value
Method / Principle\tFluorescence Immunoassay (Sandwich Immunodetection)
Reaction / Incubation Time\t15 minutes

________________________________________
6. Normal Range (Reference Values) & Clinical Interpretation
CK-MB Concentration\tClinical Reference / Interpretation
0.0 – 5.0 ng/mL (≤ 5.0 ng/mL)\tNormal — Patient preliminarily determined not to have suffered myocardial infarction
> 5.0 ng/mL\tElevated / Abnormal — Indicative of probable Acute Myocardial Infarction (AMI)

●\tCritical Action Alert: Any result > 5.0 ng/mL requires immediate STAT notification to attending physician.
TXT;

        // Post with NO manual title, code, or department filled in
        $response = $this->actingAs($superadmin)->post(route('sops.store'), [
            'sop_text' => $templateText,
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $sop = Sop::where('sop_code', 'KH-LAB-SOP-009-TEST')->first();
        $this->assertNotNull($sop, 'SOP KH-LAB-SOP-009-TEST was auto-extracted and created.');
        $this->assertEquals('CREATINE KINASE-MB (CK-MB) — FINECARE™ FIA', $sop->title);
        $this->assertEquals('laboratory', $sop->department);
        $this->assertEquals('Mohammad Sulieman Ibrahim', $sop->author_name);
        $this->assertEquals('1.0 Bench Quick Reference', $sop->version);
        $this->assertStringContainsString('NORMAL', $sop->extracted_html);
        $this->assertStringContainsString('ELEVATED', $sop->extracted_html);
        $this->assertStringContainsString('CRITICAL ALERT', $sop->extracted_html);
        $this->assertStringContainsString('REJECTION CRITERIA', $sop->extracted_html);
    }
}
