<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class LmsLabReportingAndStockMarketChartTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $labLead;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->admin = User::where('email', 'muse@kaafihospitals.org')->first();
        $this->labLead = $this->admin;
    }

    public function test_lms_report_parsing_extracts_all_13_bench_totals_and_individual_tests(): void
    {
        $csvContent = implode("\n", [
            'Summary Lab Examined,,,,,,,,,,,,',
            ',,,,,,,,,,Print Date:,,10/28/2026 9:58:29 PM',
            ',Lab Examination,,,,Lab Type,,,,Examined,,,',
            ',CBC,,,,HAEMATOLOGY,,,,11253,,,',
            ',URINE ANALYSIS,,,,URINE,,,,10844,,,',
            ',GLUCOSE (RBS),,,,BIOCHEMISTRY,,,,6130,,,',
            ',TYPHOID IGM/IGG,,,,SEROLOGY,,,,6080,,,',
            ',TSH,,,,HORMONES,,,,3883,,,',
            ',STOOL ANALYSIS,,,,STOOL,,,,2775,,,',
            ',VITAMIN D,,,,QUANTITATIVE TEST,,,,915,,,',
            ',PT,,,,COAGULATION PROFILE,,,,323,,,',
            ',URINE C/S,,,,MICROBIOLOGY AND PARASITOLOGY,,,,120,,,',
            ',BLOOD TRANSFUSION,,,,BLOOD BANK,,,,142,,,',
            ',SEMEN ANLYSIS,,,,SEMEN ANALYSIS,,,,153,,,',
            ',SPUTUM,,,,TB ANALYSIS,,,,46,,,',
            ',PLEURAL FLUID,,,,BODY FLUIDS,,,,7,,,',
        ]);

        $file = UploadedFile::fake()->createWithContent('lms_shift_report.csv', $csvContent);

        $response = $this->actingAs($this->labLead)
            ->post(route('reports.lab.parse-lms'), [
                'lms_file' => $file,
                'current_date' => '2026-10-28',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'extracted_date' => '2026-10-28',
            'date_matches' => true,
            'date_warning' => null,
            'total_tests_examined' => 42671,
            'ordered_tests_count' => 13,
        ]);

        $json = $response->json();
        $this->assertEquals(11253, $json['bench_totals']['haematology']);
        $this->assertEquals(10844, $json['bench_totals']['urine']);
        $this->assertEquals(6130, $json['bench_totals']['biochemistry']);
        $this->assertEquals(6080, $json['bench_totals']['serology']);
        $this->assertEquals(3883, $json['bench_totals']['hormones']);
        $this->assertEquals(2775, $json['bench_totals']['stool']);
        $this->assertEquals(915, $json['bench_totals']['quantitative_test']);
        $this->assertEquals(323, $json['bench_totals']['coagulation_profile']);
        $this->assertEquals(120, $json['bench_totals']['microbiology_parasitology']);
        $this->assertEquals(142, $json['bench_totals']['blood_bank']);
        $this->assertEquals(153, $json['bench_totals']['semen_analysis']);
        $this->assertEquals(46, $json['bench_totals']['tb_analysis']);
        $this->assertEquals(7, $json['bench_totals']['body_fluids']);

        // Check individual test counts
        $allTests = collect($json['all_tests'])->keyBy('test_name');
        $this->assertEquals(11253, $allTests['CBC']['count']);
        $this->assertEquals(6130, $allTests['GLUCOSE (RBS)']['count']);
    }

    public function test_lms_report_parsing_warns_on_date_mismatch(): void
    {
        $csvContent = implode("\n", [
            'Summary Lab Examined,,,,,,,,,,,,',
            ',,,,,,,,,,Print Date:,,10/27/2026 8:00:00 PM',
            ',Lab Examination,,,,Lab Type,,,,Examined,,,',
            ',CBC,,,,HAEMATOLOGY,,,,150,,,',
        ]);

        $file = UploadedFile::fake()->createWithContent('shift_report.csv', $csvContent);

        $response = $this->actingAs($this->labLead)
            ->post(route('reports.lab.parse-lms'), [
                'lms_file' => $file,
                'current_date' => '2026-10-28', // Different from 2026-10-27
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'extracted_date' => '2026-10-27',
            'system_date' => '2026-10-28',
            'date_matches' => false,
        ]);

        $this->assertNotEmpty($response->json('date_warning'));
        $this->assertStringContainsString('Report Date Mismatch', $response->json('date_warning'));
    }

    public function test_lab_test_chart_data_endpoint_returns_timeline_and_ticker_for_periods(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson(route('analytics.lab-tests.chart', [
                'period' => 'month',
                'metric' => 'total',
            ]));

        $response->assertOk();
        $response->assertJsonStructure([
            'period',
            'metric',
            'timeline' => [
                'labels',
                'datasets',
                'summary',
            ],
            'ticker' => [
                'total_volume',
                'change_percent',
                'change_direction',
                'period_high',
                'period_low',
                'average_volume',
            ],
            'tests',
        ]);

        // Multi-test comparison request
        $multiResponse = $this->actingAs($this->admin)
            ->getJson(route('analytics.lab-tests.chart', [
                'period' => 'week',
                'metric' => 'multi',
                'tests' => 'CBC,GLUCOSE (RBS),TSH',
            ]));

        $multiResponse->assertOk();
        $datasets = $multiResponse->json('timeline.datasets');
        $this->assertCount(3, $datasets);
        $this->assertEquals('CBC', $datasets[0]['label']);
        $this->assertEquals('GLUCOSE (RBS)', $datasets[1]['label']);
        $this->assertEquals('TSH', $datasets[2]['label']);
    }

    public function test_lms_report_parsing_accepts_xlsx_files_and_extracts_correct_totals(): void
    {
        $rows = [
            ['Summary Lab Examined', '', '', ''],
            ['', '', 'Print Date:', '10/28/2026 9:58:29 PM'],
            ['', 'Lab Examination', 'Lab Type', 'Examined'],
            ['', 'CBC', 'HAEMATOLOGY', 4500],
            ['', 'GLUCOSE (RBS)', 'BIOCHEMISTRY', 3200],
            ['', 'TYPHOID IGM/IGG', 'SEROLOGY', 1200],
        ];

        $file = $this->createFakeXlsxFile($rows, 'lms_shift_report.xlsx');

        $response = $this->actingAs($this->labLead)
            ->post(route('reports.lab.parse-lms'), [
                'lms_file' => $file,
                'current_date' => '2026-10-28',
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'extracted_date' => '2026-10-28',
            'date_matches' => true,
            'total_tests_examined' => 8900,
        ]);

        $json = $response->json();
        $this->assertEquals(4500, $json['bench_totals']['haematology']);
        $this->assertEquals(3200, $json['bench_totals']['biochemistry']);
        $this->assertEquals(1200, $json['bench_totals']['serology']);
    }

    public function test_laboratory_report_view_renders_all_13_specialties_and_lms_upload_button(): void
    {
        DailyReport::where('department', 'laboratory')
            ->whereDate('report_date', '2026-11-01')
            ->delete();

        $response = $this->actingAs($this->labLead)
            ->get(route('reports.create', ['department' => 'laboratory', 'date' => '2026-11-01']));

        $response->assertOk();
        $response->assertSee('Upload LMS Shift Report');
        $response->assertSee('HAEMATOLOGY');
        $response->assertSee('BIOCHEMISTRY');
        $response->assertSee('SEROLOGY');
        $response->assertSee('HORMONES');
        $response->assertSee('URINE ANALYSIS');
        $response->assertSee('STOOL');
        $response->assertSee('QUANTITATIVE TEST');
        $response->assertSee('COAGULATION PROFILE');
        $response->assertSee('MICROBIOLOGY');
        $response->assertSee('BLOOD BANK');
        $response->assertSee('SEMEN ANALYSIS');
        $response->assertSee('TB ANALYSIS');
        $response->assertSee('BODY FLUIDS');
        $response->assertSee('SMART TEST REGISTER');
    }

    public function test_dashboard_renders_universal_live_stock_market_chart_for_admin(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('liveLabDiagnosticChart');
        $response->assertSee('1D');
        $response->assertSee('5D');
        $response->assertSee('1M');
        $response->assertSee('6M');
        $response->assertSee('YTD');
        $response->assertSee('1Y');
        $response->assertSee('5Y');
        $response->assertSee('Max');
    }

    public function test_google_finance_chart_endpoint_supports_all_timeframe_periods(): void
    {
        $periods = ['1d', '5d', '1m', '6m', 'ytd', '1y', '5y', 'max'];
        foreach ($periods as $p) {
            $response = $this->actingAs($this->admin)
                ->get(route('analytics.lab-tests.chart', ['period' => $p, 'metric' => 'total']));
            $response->assertOk();
            $data = $response->json();
            $this->assertArrayHasKey('ticker', $data);
            $this->assertArrayHasKey('baseline_value', $data['ticker']);
        }
    }

    protected function createFakeXlsxFile(array $rows, string $filename = 'test.xlsx'): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new \ZipArchive;
        $zip->open($tempPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        $sharedStrings = [];
        $strIndexMap = [];
        $sheetRowsXml = '';

        foreach ($rows as $rIdx => $row) {
            $rowNum = $rIdx + 1;
            $sheetRowsXml .= '<row r="'.$rowNum.'">';
            foreach ($row as $cIdx => $val) {
                $colLetter = chr(65 + $cIdx);
                $cellRef = $colLetter.$rowNum;
                if (is_numeric($val) && ! is_string($val)) {
                    $sheetRowsXml .= '<c r="'.$cellRef.'"><v>'.$val.'</v></c>';
                } else {
                    $valStr = (string) $val;
                    if (! isset($strIndexMap[$valStr])) {
                        $strIndexMap[$valStr] = count($sharedStrings);
                        $sharedStrings[] = $valStr;
                    }
                    $sIdx = $strIndexMap[$valStr];
                    $sheetRowsXml .= '<c r="'.$cellRef.'" t="s"><v>'.$sIdx.'</v></c>';
                }
            }
            $sheetRowsXml .= '</row>';
        }

        $sstXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $sstXml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($sharedStrings).'" uniqueCount="'.count($sharedStrings).'">';
        foreach ($sharedStrings as $str) {
            $sstXml .= '<si><t>'.htmlspecialchars($str).'</t></si>';
        }
        $sstXml .= '</sst>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $sheetXml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        $sheetXml .= '<sheetData>'.$sheetRowsXml.'</sheetData>';
        $sheetXml .= '</worksheet>';

        $zip->addFromString('xl/sharedStrings.xml', $sstXml);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        return new UploadedFile($tempPath, $filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
