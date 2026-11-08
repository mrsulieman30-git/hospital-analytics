<?php

namespace App\Services;

use App\Models\DailyReport;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LmsLabReportParserService
{
    /**
     * Official 13 Lab Types from Kaafi Hospitals LMS.
     */
    public const LAB_TYPES = [
        'haematology' => [
            'code' => 'HAEMATOLOGY',
            'label' => 'Hematology & Hemostasis',
            'short' => 'Hematology',
            'color' => '#8B5CF6', // Purple
        ],
        'biochemistry' => [
            'code' => 'BIOCHEMISTRY',
            'label' => 'Clinical Biochemistry',
            'short' => 'Biochemistry',
            'color' => '#3B82F6', // Blue
        ],
        'serology' => [
            'code' => 'SEROLOGY',
            'label' => 'Infectious Serology & Immunology',
            'short' => 'Serology',
            'color' => '#EC4899', // Pink
        ],
        'hormones' => [
            'code' => 'HORMONES',
            'label' => 'Endocrinology & Special Hormones',
            'short' => 'Hormones',
            'color' => '#F59E0B', // Amber
        ],
        'urine' => [
            'code' => 'URINE',
            'label' => 'Urine Analysis & Microscopy',
            'short' => 'Urine',
            'color' => '#EAB308', // Yellow
        ],
        'stool' => [
            'code' => 'STOOL',
            'label' => 'Stool Examination & Parasitology',
            'short' => 'Stool',
            'color' => '#10B981', // Emerald
        ],
        'quantitative_test' => [
            'code' => 'QUANTITATIVE TEST',
            'label' => 'Quantitative Immunoassay & Proteins',
            'short' => 'Quantitative',
            'color' => '#06B6D4', // Cyan
        ],
        'coagulation_profile' => [
            'code' => 'COAGULATION PROFILE',
            'label' => 'Coagulation Profile & Clotting Factors',
            'short' => 'Coagulation',
            'color' => '#EF4444', // Red
        ],
        'microbiology_parasitology' => [
            'code' => 'MICROBIOLOGY AND PARASITOLOGY',
            'label' => 'Microbiology, Cultures & Parasitology',
            'short' => 'Microbiology',
            'color' => '#14B8A6', // Teal
        ],
        'blood_bank' => [
            'code' => 'BLOOD BANK',
            'label' => 'Blood Bank & Transfusion Crossmatch',
            'short' => 'Blood Bank',
            'color' => '#DC2626', // Deep Red
        ],
        'semen_analysis' => [
            'code' => 'SEMEN ANALYSIS',
            'label' => 'Semen Analysis & Fertility Diagnostics',
            'short' => 'Semen Analysis',
            'color' => '#6366F1', // Indigo
        ],
        'tb_analysis' => [
            'code' => 'TB ANALYSIS',
            'label' => 'Tuberculosis (TB) & Mycobacteriology',
            'short' => 'TB Analysis',
            'color' => '#D97706', // Ochre
        ],
        'body_fluids' => [
            'code' => 'BODY FLUIDS',
            'label' => 'Body Fluids & Cytopathology',
            'short' => 'Body Fluids',
            'color' => '#64748B', // Slate
        ],
    ];

    /**
     * Master Hospital LMS Test Catalog with Official Lab Type Mappings.
     *
     * @var array<string, string> Test Name => Lab Type Key
     */
    public const MASTER_TEST_CATALOG = [
        // HAEMATOLOGY
        'CBC' => 'haematology',
        'ESR' => 'haematology',
        'HB' => 'haematology',
        'BLOOD FILM FOR MALARIA' => 'haematology',
        'BLOOD GROUPING' => 'haematology',
        'BLOOD FLIM FOR LEUKEMIA' => 'haematology',
        'BLOOD FLIM' => 'haematology',
        'WBC' => 'haematology',
        'BLOOD FOR MICROFILARIASIS' => 'haematology',
        'BLOOD FILM FOR LEISHMANIASIS' => 'haematology',
        'LISHMENIA' => 'haematology',

        // URINE
        'URINE ANALYSIS' => 'urine',

        // BIOCHEMISTRY
        'GLUCOSE (RBS)' => 'biochemistry',
        'CREATININE' => 'biochemistry',
        'GPT' => 'biochemistry',
        'POTASSIUM (K)' => 'biochemistry',
        'UREA' => 'biochemistry',
        'CALCIUM' => 'biochemistry',
        'CHOLESTEROL' => 'biochemistry',
        'TOTAL BILIRUBIN' => 'biochemistry',
        'HBAIC' => 'biochemistry',
        'GOT' => 'biochemistry',
        'GLUCOSE (FBS)' => 'biochemistry',
        'SODIUM (NA)' => 'biochemistry',
        'ALP' => 'biochemistry',
        'ALBUMIN' => 'biochemistry',
        'TRIGLYCERIDE' => 'biochemistry',
        'DIRECT BILIRUBIN' => 'biochemistry',
        'URIC ACID' => 'biochemistry',
        'HDL CHOLESTEROL' => 'biochemistry',
        'TOTAL PROTEIN' => 'biochemistry',
        'LDH' => 'biochemistry',
        'AMYLASE' => 'biochemistry',
        'FERRITIN' => 'biochemistry',
        'CHLORIDE (CI)' => 'biochemistry',
        'CK-MB' => 'biochemistry',
        'MAGNESIUM' => 'biochemistry',
        'TOTAL IRON' => 'biochemistry',
        'MDRD GFR' => 'biochemistry',
        'TROPONIN' => 'biochemistry',
        'Pro-BNP' => 'biochemistry',
        'PH' => 'biochemistry',
        'BNP' => 'biochemistry',
        'CYSTATIN C' => 'biochemistry',
        'C-Peptide' => 'biochemistry',

        // SEROLOGY
        'TYPHOID IGM/IGG' => 'serology',
        'BRUCELLA ABORTUS' => 'serology',
        'BRUCELLA MELLITENINS' => 'serology',
        'HBSAG' => 'serology',
        'HCV' => 'serology',
        'HIV' => 'serology',
        'H-PYLORI' => 'serology',
        'RF' => 'serology',
        'ASO' => 'serology',
        'CRP' => 'serology',
        'PREGNANCY TEST' => 'serology',
        'WIDAL TEST' => 'serology',
        'TOXOPLASMA IGM/IGG' => 'serology',
        'MALARIA (RDT)' => 'serology',
        'SYPHILS' => 'serology',
        'DENGUE FEVER IGM/IGG' => 'serology',
        'Brucella IgM/IgG' => 'serology',
        'HAV' => 'serology',
        'CHLAMYDIA' => 'serology',
        'GONORRHEA' => 'serology',
        'TORCH' => 'serology',
        'HIV-F' => 'serology',
        'LEISHMANIA (RDT)' => 'serology',
        'HSV - HERPES SIMPLIX VIRUS' => 'serology',
        'CMV - CYTOMEGALOVIRUS' => 'serology',
        'RUBELLA VIRUS' => 'serology',
        'TB' => 'serology',
        'LV' => 'serology',
        'PSA serology' => 'serology',

        // HORMONES
        'TSH' => 'hormones',
        'T4' => 'hormones',
        'T3' => 'hormones',
        'PROLACTIN' => 'hormones',
        'FSH' => 'hormones',
        'LH' => 'hormones',
        'HCG' => 'hormones',
        'TESTOSTERONE' => 'hormones',
        'AMH' => 'hormones',
        'PROGESTERONE' => 'hormones',
        'FREE T4' => 'hormones',
        'FREE T3' => 'hormones',
        'TOTAL T4' => 'hormones',
        'T4 TOTAL' => 'hormones',
        'PROSTATE SPECIFIC ANTIGEN' => 'hormones',

        // STOOL
        'STOOL ANALYSIS' => 'stool',
        'H-PYLORI STOOL' => 'stool',
        'S-TYPHI STOOL' => 'stool',
        'FOB' => 'stool',
        'SALMONELLA TYPHI STOOL ANTIGEN' => 'stool',
        'HAV StTOOL ANTIGEN' => 'stool',
        'S.TYPHI' => 'stool',

        // QUANTITATIVE TEST
        'VITAMIN D' => 'quantitative_test',
        'CRP QUANTITATIVE TEST' => 'quantitative_test',
        'TOTAL IGE' => 'quantitative_test',
        'RF IGM QUANTITATIVE TEST' => 'quantitative_test',
        'VITAMIN B12' => 'quantitative_test',
        'PSA' => 'quantitative_test',
        'PROSTATE (PSA)' => 'quantitative_test',
        'IGE' => 'quantitative_test',
        'ANTI-CCP' => 'quantitative_test',
        'ASO QUANTITATIVE' => 'quantitative_test',
        'Calprotectin' => 'quantitative_test',
        'Complement C3' => 'quantitative_test',
        'ANTI-DSDNA' => 'quantitative_test',

        // COAGULATION PROFILE
        'PT' => 'coagulation_profile',
        'INR' => 'coagulation_profile',
        'PTT' => 'coagulation_profile',
        'CLOTTING TIME (CT)' => 'coagulation_profile',
        'BLEEDING TIME (PT)' => 'coagulation_profile',
        'D-DIMER' => 'coagulation_profile',

        // SEMEN ANALYSIS
        'SEMEN ANLYSIS' => 'semen_analysis',

        // BLOOD BANK
        'BLOOD TRANSFUSION' => 'blood_bank',
        'CROSS MATCH' => 'blood_bank',
        'SCREANING' => 'blood_bank',

        // MICROBIOLOGY AND PARASITOLOGY
        'URINE C/S' => 'microbiology_parasitology',
        'KOH' => 'microbiology_parasitology',
        'STOOL C/S' => 'microbiology_parasitology',
        'PUS C/S' => 'microbiology_parasitology',
        'THROAT SWAB' => 'microbiology_parasitology',
        'ABSCESS C/S' => 'microbiology_parasitology',
        'GENITAL SWAB' => 'microbiology_parasitology',
        'EAR SWAB' => 'microbiology_parasitology',
        'GRAM STAIN' => 'microbiology_parasitology',
        'SYNOVIAL FLUID C/S' => 'microbiology_parasitology',
        'Semen C/S' => 'microbiology_parasitology',
        'NASAL SWAB' => 'microbiology_parasitology',

        // TB ANALYSIS
        'SPUTUM' => 'tb_analysis',

        // BODY FLUIDS
        'PLEURAL FLUID' => 'body_fluids',
        'ASCITIC FLUID' => 'body_fluids',
        'SYNOVIAL FLUID' => 'body_fluids',
    ];

    /**
     * Get 135 Master LMS tests formatted as a list of structured arrays for frontend binding.
     *
     * @return array<int, array{test_name: string, lab_type: string, lab_type_name: string}>
     */
    public static function getFormattedCatalog(): array
    {
        $list = [];
        foreach (self::MASTER_TEST_CATALOG as $testName => $typeKey) {
            $list[] = [
                'test_name' => $testName,
                'lab_type' => $typeKey,
                'lab_type_name' => self::LAB_TYPES[$typeKey]['label'] ?? (self::LAB_TYPES[$typeKey]['short'] ?? $typeKey),
            ];
        }

        return $list;
    }

    /**
     * Convert an uploaded Excel OpenXML (.xlsx) file into a CSV text stream.
     * Uses PHP's native ZipArchive and SimpleXML with zero external dependencies.
     */
    public function convertXlsxToCsv(string $filePath): string
    {
        if (! class_exists(\ZipArchive::class) || ! file_exists($filePath)) {
            return '';
        }

        $zip = new \ZipArchive;
        if ($zip->open($filePath) !== true) {
            return '';
        }

        // 1. Parse shared strings table (xl/sharedStrings.xml)
        $sharedStrings = [];
        $stringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($stringsXml !== false) {
            $xml = @simplexml_load_string($stringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        $combined = '';
                        foreach ($si->r as $r) {
                            $combined .= (string) ($r->t ?? '');
                        }
                        $sharedStrings[] = $combined;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Locate active worksheet XML
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $stat['name'])) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if ($sheetXml === false) {
            return '';
        }

        $sheet = @simplexml_load_string($sheetXml);
        if (! $sheet || ! isset($sheet->sheetData)) {
            return '';
        }

        $csvLines = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            $lastCol = 0;

            foreach ($row->c as $c) {
                // Parse column letters from cell reference e.g. "B12"
                $ref = (string) ($c['r'] ?? '');
                $colLetters = preg_replace('/[^A-Za-z]/', '', $ref);
                $colIdx = 0;
                $len = strlen($colLetters);
                for ($ci = 0; $ci < $len; $ci++) {
                    $colIdx = $colIdx * 26 + (ord(strtoupper($colLetters[$ci])) - ord('A') + 1);
                }

                if ($colIdx === 0) {
                    $colIdx = $lastCol + 1;
                }

                while ($lastCol < $colIdx - 1) {
                    $cells[] = '';
                    $lastCol++;
                }

                $cellType = (string) ($c['t'] ?? '');
                $val = '';

                if ($cellType === 's') {
                    $strIndex = (int) ($c->v ?? 0);
                    $val = $sharedStrings[$strIndex] ?? '';
                } elseif ($cellType === 'inlineStr' && isset($c->is->t)) {
                    $val = (string) $c->is->t;
                } elseif (isset($c->v)) {
                    $val = (string) $c->v;
                }

                $cells[] = $val;
                $lastCol = $colIdx;
            }

            $escaped = array_map(function ($val) {
                $val = trim((string) $val);
                if (str_contains($val, ',') || str_contains($val, '"') || str_contains($val, "\n") || str_contains($val, "\r")) {
                    return '"'.str_replace('"', '""', $val).'"';
                }

                return $val;
            }, $cells);

            $csvLines[] = implode(',', $escaped);
        }

        return implode("\n", $csvLines);
    }

    /**
     * Map raw Lab Type text string from CSV to our standardized key.
     */
    public function normalizeLabType(string $rawType): string
    {
        $clean = strtoupper(trim($rawType));

        return match (true) {
            str_contains($clean, 'HAEMATOLOGY') || str_contains($clean, 'HEMATOLOGY') => 'haematology',
            str_contains($clean, 'BIOCHEMISTRY') || str_contains($clean, 'CHEMISTRY') => 'biochemistry',
            str_contains($clean, 'SEROLOGY') => 'serology',
            str_contains($clean, 'HORMONE') => 'hormones',
            $clean === 'URINE' || str_contains($clean, 'URINE') => 'urine',
            $clean === 'STOOL' || str_contains($clean, 'STOOL') => 'stool',
            str_contains($clean, 'QUANTITATIVE') => 'quantitative_test',
            str_contains($clean, 'COAGULATION') => 'coagulation_profile',
            str_contains($clean, 'MICROBIOLOGY') || str_contains($clean, 'PARASITOLOGY') => 'microbiology_parasitology',
            str_contains($clean, 'BLOOD BANK') || str_contains($clean, 'TRANSFUSION') => 'blood_bank',
            str_contains($clean, 'SEMEN') => 'semen_analysis',
            str_contains($clean, 'TB') || str_contains($clean, 'SPUTUM') => 'tb_analysis',
            str_contains($clean, 'BODY FLUID') || str_contains($clean, 'FLUID') => 'body_fluids',
            default => 'biochemistry',
        };
    }

    /**
     * Parse raw text/CSV file uploaded from the hospital's LMS.
     *
     * @param  string  $content  Raw CSV/text content
     * @param  string|null  $systemDate  Expected date (e.g. current report date YYYY-MM-DD)
     * @return array Parsed bench breakdown, tests ordered, catalog, and date check
     */
    public function parseLmsContent(string $content, ?string $systemDate = null): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        $extractedDate = null;
        $extractedPrintDateRaw = null;
        $dateRangeFrom = null;
        $dateRangeTo = null;

        // Initialize bench counters to 0
        $benchTotals = [];
        foreach (self::LAB_TYPES as $key => $meta) {
            $benchTotals[$key] = 0;
        }

        // Initialize master tests from catalog with 0 count
        $allTests = [];
        foreach (self::MASTER_TEST_CATALOG as $testName => $typeKey) {
            $allTests[$testName] = [
                'test_name' => $testName,
                'lab_type' => $typeKey,
                'lab_type_name' => self::LAB_TYPES[$typeKey]['label'],
                'count' => 0,
            ];
        }

        $headerFound = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            // Look for Print Date: e.g. "Print Date:,,10/28/2026 9:58:29 PM"
            if (preg_match('/Print\s*Date:\s*,*([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{2,4})/i', $line, $m)) {
                $extractedPrintDateRaw = trim($m[1]);
                try {
                    $extractedDate = Carbon::parse($extractedPrintDateRaw)->toDateString();
                } catch (\Exception $e) {
                    $extractedDate = null;
                }
            }

            // Look for Date range: "From: ... To: ..."
            if (preg_match('/From:\s*,*([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{2,4})\s*,*To:\s*,*([0-9]{1,2}[\/\-][0-9]{1,2}[\/\-][0-9]{2,4})/i', $line, $m)) {
                $dateRangeFrom = trim($m[1]);
                $dateRangeTo = trim($m[2]);
                if (! $extractedDate) {
                    try {
                        $extractedDate = Carbon::parse($dateRangeTo)->toDateString();
                    } catch (\Exception $e) {
                    }
                }
            }

            // Detect table header: ",Lab Examination,,,,Lab Type,,,,Examined,,,"
            if (stripos($line, 'Lab Examination') !== false && stripos($line, 'Lab Type') !== false) {
                $headerFound = true;

                continue;
            }

            if (! $headerFound) {
                continue;
            }

            // Parse CSV columns
            $cols = str_getcsv($line);
            $cleanCols = array_values(array_filter(array_map('trim', $cols), fn ($c) => $c !== ''));

            if (count($cleanCols) < 3) {
                // If row only has 1 or 2 entries, it might be the grand total row at the end
                continue;
            }

            $testName = strtoupper(trim($cleanCols[0]));
            $rawLabType = trim($cleanCols[1]);
            $examinedCount = (int) preg_replace('/[^0-9]/', '', $cleanCols[2]);

            // Skip total row or non-test rows
            if ($testName === 'TOTAL' || $testName === 'EXAMINED' || $examinedCount <= 0 && ! is_numeric($cleanCols[2])) {
                continue;
            }

            $typeKey = $this->normalizeLabType($rawLabType);

            // Add to bench total
            $benchTotals[$typeKey] = ($benchTotals[$typeKey] ?? 0) + $examinedCount;

            // Update or add test to all tests
            $allTests[$testName] = [
                'test_name' => $testName,
                'lab_type' => $typeKey,
                'lab_type_name' => self::LAB_TYPES[$typeKey]['label'],
                'count' => $examinedCount,
            ];
        }

        // Filter tests ordered on this shift/day (count > 0)
        $orderedTests = array_values(array_filter($allTests, fn ($item) => $item['count'] > 0));

        // Sort ordered tests by examined count descending
        usort($orderedTests, fn ($a, $b) => $b['count'] <=> $a['count']);

        $totalTestsExamined = array_sum($benchTotals);

        // Date mismatch check
        $systemDateFormatted = $systemDate ? Carbon::parse($systemDate)->toDateString() : now()->toDateString();
        $dateMatches = ($extractedDate !== null) ? ($extractedDate === $systemDateFormatted) : true;

        $dateWarning = null;
        if ($extractedDate !== null && ! $dateMatches) {
            $formattedUploaded = Carbon::parse($extractedDate)->format('M d, Y');
            $formattedSystem = Carbon::parse($systemDateFormatted)->format('M d, Y');
            $dateWarning = "Report Date Mismatch: The uploaded LMS file is dated {$formattedUploaded}, while your current report date is {$formattedSystem}. Please verify if you want to apply this shift file to today's report.";
        }

        return [
            'success' => true,
            'print_date_raw' => $extractedPrintDateRaw,
            'extracted_date' => $extractedDate,
            'system_date' => $systemDateFormatted,
            'date_matches' => $dateMatches,
            'date_warning' => $dateWarning,
            'bench_totals' => $benchTotals,
            'ordered_tests' => $orderedTests,
            'ordered_tests_count' => count($orderedTests),
            'all_tests' => array_values($allTests),
            'total_catalog_count' => count($allTests),
            'total_tests_examined' => $totalTestsExamined,
            'lab_types_meta' => self::LAB_TYPES,
        ];
    }

    /**
     * Provide timeline data for the Live Stock Market Chart in the Admin Panel.
     * Supports timeline filters: 'day', 'week', 'month', 'year', '5_years'.
     */
    public function getTimelineData(string $period = 'week', array $selectedTests = [], ?string $metric = 'total', ?string $labTypeFilter = null): array
    {
        $now = Carbon::now();
        $dataPoints = [];
        $labels = [];

        // Determine date range & step based on Google Finance style timeframe period
        [$startDate, $endDate, $interval] = match (strtolower($period)) {
            'day', '1d' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'hourly'],
            '5d' => [$now->copy()->subDays(4)->startOfDay(), $now->copy()->endOfDay(), 'daily'],
            'month', '1m' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), 'daily'],
            '6m' => [$now->copy()->subMonths(5)->startOfMonth(), $now->copy()->endOfMonth(), 'monthly'],
            'ytd' => [$now->copy()->startOfYear(), $now->copy()->endOfDay(), 'monthly'],
            'year', '1y' => [$now->copy()->subMonths(11)->startOfMonth(), $now->copy()->endOfMonth(), 'monthly'],
            '5_years', '5y' => [$now->copy()->subYears(4)->startOfYear(), $now->copy()->endOfYear(), 'yearly'],
            'max' => [$now->copy()->subYears(9)->startOfYear(), $now->copy()->endOfYear(), 'yearly'],
            default => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'daily'], // 'week'
        };

        // Query existing DailyReport records for laboratory within range
        $reports = DailyReport::where('department', 'laboratory')
            ->whereBetween('report_date', [$startDate->copy()->startOfDay()->toDateTimeString(), $endDate->copy()->endOfDay()->toDateTimeString()])
            ->orderBy('report_date')
            ->get()
            ->keyBy(fn ($r) => Carbon::parse($r->report_date)->toDateString());

        // Support 'ALL' token or empty
        $allCatalogTests = array_keys(self::MASTER_TEST_CATALOG);
        if (empty($selectedTests)) {
            $selectedTests = ['CBC', 'GLUCOSE (RBS)', 'URINE ANALYSIS', 'TYPHOID IGM/IGG', 'TSH'];
        } elseif (in_array('ALL', $selectedTests, true) || $selectedTests === ['ALL']) {
            if ($labTypeFilter && $labTypeFilter !== 'all') {
                $selectedTests = [];
                foreach (self::MASTER_TEST_CATALOG as $tName => $tKey) {
                    if ($tKey === $labTypeFilter) {
                        $selectedTests[] = $tName;
                    }
                }
            } else {
                $selectedTests = $allCatalogTests;
            }
        }

        // Color palette for multi-test series
        $palette = [
            '#8B5CF6', '#3B82F6', '#10B981', '#F59E0B', '#EC4899',
            '#06B6D4', '#EF4444', '#14B8A6', '#6366F1', '#D97706',
        ];

        // Prepare structure depending on interval
        if ($interval === 'hourly') {
            for ($h = 7; $h <= 21; $h += 2) {
                $labels[] = sprintf('%02d:00', $h);
            }
        } elseif ($interval === 'daily') {
            $periodDays = CarbonPeriod::create($startDate, $endDate);
            foreach ($periodDays as $d) {
                $labels[] = $d->format('M d');
            }
        } elseif ($interval === 'monthly') {
            $periodMonths = CarbonPeriod::create($startDate, '1 month', $endDate);
            foreach ($periodMonths as $m) {
                $labels[] = $m->format('M Y');
            }
        } else { // yearly
            for ($y = $startDate->year; $y <= $endDate->year; $y++) {
                $labels[] = (string) $y;
            }
        }

        // Benchmark weights derived from actual hospital LMS distribution (116,946 total tests)
        $testDistribution = [
            'CBC' => 0.096,
            'URINE ANALYSIS' => 0.093,
            'ESR' => 0.055,
            'GLUCOSE (RBS)' => 0.052,
            'TYPHOID IGM/IGG' => 0.052,
            'CREATININE' => 0.050,
            'BRUCELLA ABORTUS' => 0.047,
            'HB' => 0.045,
            'BRUCELLA MELLITENINS' => 0.045,
            'TSH' => 0.033,
            'BLOOD FILM FOR MALARIA' => 0.032,
            'GPT' => 0.029,
            'STOOL ANALYSIS' => 0.024,
            'HBSAG' => 0.021,
            'HCV' => 0.020,
            'H-PYLORI STOOL' => 0.020,
            'POTASSIUM (K)' => 0.018,
            'HIV' => 0.016,
            'UREA' => 0.015,
            'H-PYLORI' => 0.015,
            'BLOOD GROUPING' => 0.015,
            'CALCIUM' => 0.014,
            'CHOLESTEROL' => 0.013,
            'TOTAL BILIRUBIN' => 0.011,
            'HBAIC' => 0.011,
            'RF' => 0.010,
            'GOT' => 0.010,
            'T4' => 0.010,
            'ASO' => 0.008,
            'VITAMIN D' => 0.008,
            'GLUCOSE (FBS)' => 0.007,
            'T3' => 0.007,
            'SODIUM (NA)' => 0.006,
            'CRP' => 0.006,
            'PROLACTIN' => 0.005,
            'PREGNANCY TEST' => 0.005,
        ];

        $totalSeries = [];
        $testSeries = [];
        $colorIdx = 0;

        foreach ($selectedTests as $testName) {
            $typeKey = self::MASTER_TEST_CATALOG[$testName] ?? 'haematology';
            $typeLabel = self::LAB_TYPES[$typeKey]['short'] ?? (self::LAB_TYPES[$typeKey]['label'] ?? ucfirst($typeKey));

            $testSeries[$testName] = [
                'label' => $testName,
                'lab_type' => $typeLabel,
                'color' => $palette[$colorIdx % count($palette)],
                'data' => [],
            ];
            $colorIdx++;
        }

        // Build authentic data points purely from database DailyReport records
        $n = count($labels);

        if ($interval === 'hourly') {
            $todayDate = $startDate->toDateString();
            $todayReport = $reports->get($todayDate) ?? DailyReport::where('department', 'laboratory')
                ->where('report_date', $todayDate)
                ->latest()
                ->first();

            $todayTotal = 0;
            $todayTestCounts = array_fill_keys(array_map('strtoupper', $selectedTests), 0);

            if ($todayReport) {
                $todayTotal = (int) ($todayReport->form_data['section_a_volume']['total_tests'] ?? 0);
                $testsList = $todayReport->form_data['section_c_benches_and_quality']['tests'] ?? [];
                foreach ($testsList as $t) {
                    $tn = strtoupper($t['test_name'] ?? '');
                    if (isset($todayTestCounts[$tn])) {
                        $todayTestCounts[$tn] += (int) ($t['count'] ?? 0);
                    }
                }
            }

            // Distribute actual volume across hours up to current time, zero when no reports
            $currentHour = (int) $now->format('H');
            for ($i = 0; $i < $n; $i++) {
                $slotHour = (int) explode(':', $labels[$i])[0];
                if ($todayTotal > 0 && $slotHour <= $currentHour) {
                    // Cumulative progression of actual logged tests
                    $progress = min(1.0, max(0.2, ($slotHour - 6) / max(1, $currentHour - 6)));
                    $slotTotal = (int) round($todayTotal * $progress);
                    $totalSeries[] = $slotTotal;

                    foreach ($selectedTests as $testName) {
                        $actualCount = $todayTestCounts[strtoupper($testName)] ?? 0;
                        $testSeries[$testName]['data'][] = (int) round($actualCount * $progress);
                    }
                } else {
                    $totalSeries[] = 0;
                    foreach ($selectedTests as $testName) {
                        $testSeries[$testName]['data'][] = 0;
                    }
                }
            }
        } elseif ($interval === 'daily') {
            $periodDays = iterator_to_array(CarbonPeriod::create($startDate, $endDate));

            for ($i = 0; $i < $n; $i++) {
                $dtStr = $periodDays[$i]->toDateString();
                $dayReport = $reports->get($dtStr);

                if ($dayReport) {
                    $dayTotal = (int) ($dayReport->form_data['section_a_volume']['total_tests'] ?? 0);
                    $testsList = $dayReport->form_data['section_c_benches_and_quality']['tests'] ?? [];

                    if ($dayTotal === 0 && ! empty($testsList)) {
                        $dayTotal = array_sum(array_map(fn ($t) => (int) ($t['count'] ?? 0), $testsList));
                    }

                    $totalSeries[] = $dayTotal;

                    // Exact counts for selected tests
                    foreach ($selectedTests as $testName) {
                        $val = 0;
                        foreach ($testsList as $t) {
                            if (strcasecmp($t['test_name'] ?? '', $testName) === 0) {
                                $val = (int) ($t['count'] ?? 0);
                                break;
                            }
                        }
                        $testSeries[$testName]['data'][] = $val;
                    }
                } else {
                    $totalSeries[] = 0;
                    foreach ($selectedTests as $testName) {
                        $testSeries[$testName]['data'][] = 0;
                    }
                }
            }
        } elseif ($interval === 'monthly') {
            $periodMonths = iterator_to_array(CarbonPeriod::create($startDate, '1 month', $endDate));

            for ($i = 0; $i < $n; $i++) {
                $monthStr = $periodMonths[$i]->format('Y-m');
                $monthReports = $reports->filter(fn ($r) => str_starts_with($r->report_date, $monthStr));

                $monthTotal = 0;
                $testCounts = array_fill_keys(array_map('strtoupper', $selectedTests), 0);

                foreach ($monthReports as $r) {
                    $monthTotal += (int) ($r->form_data['section_a_volume']['total_tests'] ?? 0);
                    foreach ($r->form_data['section_c_benches_and_quality']['tests'] ?? [] as $t) {
                        $tn = strtoupper($t['test_name'] ?? '');
                        if (isset($testCounts[$tn])) {
                            $testCounts[$tn] += (int) ($t['count'] ?? 0);
                        }
                    }
                }

                $totalSeries[] = $monthTotal;
                foreach ($selectedTests as $testName) {
                    $testSeries[$testName]['data'][] = $testCounts[strtoupper($testName)] ?? 0;
                }
            }
        } else { // yearly
            for ($i = 0; $i < $n; $i++) {
                $targetYear = (int) $labels[$i];
                $yearReports = $reports->filter(fn ($r) => Carbon::parse($r->report_date)->year === $targetYear);

                $yearTotal = 0;
                $testCounts = array_fill_keys(array_map('strtoupper', $selectedTests), 0);

                foreach ($yearReports as $r) {
                    $yearTotal += (int) ($r->form_data['section_a_volume']['total_tests'] ?? 0);
                    foreach ($r->form_data['section_c_benches_and_quality']['tests'] ?? [] as $t) {
                        $tn = strtoupper($t['test_name'] ?? '');
                        if (isset($testCounts[$tn])) {
                            $testCounts[$tn] += (int) ($t['count'] ?? 0);
                        }
                    }
                }

                $totalSeries[] = $yearTotal;
                foreach ($selectedTests as $testName) {
                    $testSeries[$testName]['data'][] = $testCounts[strtoupper($testName)] ?? 0;
                }
            }
        }

        // Calculate selected tests aggregate curve for each step
        $isFullCatalog = count($selectedTests) >= count($allCatalogTests);
        $selectedAggregateSeries = [];
        for ($i = 0; $i < $n; $i++) {
            if ($isFullCatalog) {
                $selectedAggregateSeries[] = $totalSeries[$i];
            } else {
                $sumSlot = 0;
                foreach ($selectedTests as $tName) {
                    $sumSlot += ($testSeries[$tName]['data'][$i] ?? 0);
                }
                $selectedAggregateSeries[] = $sumSlot;
            }
        }

        // Compute high, low, mean, volume metrics for ticker based on active selection
        $activeSeries = $selectedAggregateSeries;
        $highTotal = ! empty($activeSeries) ? max($activeSeries) : 0;
        $lowTotal = ! empty($activeSeries) ? min($activeSeries) : 0;
        $sumTotal = ! empty($activeSeries) ? array_sum($activeSeries) : 0;
        $avgTotal = (! empty($activeSeries) && count($activeSeries) > 0) ? round($sumTotal / count($activeSeries)) : 0;

        // Change percentage vs baseline
        $firstVal = $activeSeries[0] ?? 0;
        $lastVal = end($activeSeries) ?: 0;
        $changeAbs = $lastVal - $firstVal;
        $changePct = ($firstVal > 0) ? round(($changeAbs / $firstVal) * 100, 1) : 0.0;
        $changeDirection = $changeAbs > 0 ? 'up' : ($changeAbs < 0 ? 'down' : 'flat');

        $testSummaryList = [];
        foreach ($selectedTests as $tName) {
            $dataPts = $testSeries[$tName]['data'] ?? [];
            $tSum = ! empty($dataPts) ? array_sum($dataPts) : 0;
            $tPeak = ! empty($dataPts) ? max($dataPts) : 0;
            $tFirst = $dataPts[0] ?? 0;
            $tLast = end($dataPts) ?: 0;
            $share = $sumTotal > 0 ? round(($tSum / $sumTotal) * 100, 1) : 0;

            $testSummaryList[] = [
                'test_name' => $tName,
                'lab_type' => $testSeries[$tName]['lab_type'] ?? 'Diagnostics',
                'total_period_volume' => $tSum,
                'peak_interval_volume' => $tPeak,
                'market_share' => $share,
                'trend_direction' => $tLast > $tFirst ? 'up' : ($tLast < $tFirst ? 'down' : 'flat'),
            ];
        }

        // Sort summary list by highest volume first
        usort($testSummaryList, fn ($a, $b) => $b['total_period_volume'] <=> $a['total_period_volume']);

        if ($metric === 'total') {
            $datasets = [
                [
                    'label' => $isFullCatalog ? 'All Laboratory Diagnostic Tests Volume' : 'Selected Tests Aggregate Volume ('.count($selectedTests).' Tests)',
                    'data' => $selectedAggregateSeries,
                    'borderColor' => '#10B981',
                ],
            ];
        } else {
            // In multi-test mode, if more than 8 tests are selected, display top 8 distinct series and group others
            if (count($testSeries) > 8) {
                uasort($testSeries, fn ($a, $b) => array_sum($b['data']) <=> array_sum($a['data']));
                $topSeries = array_slice($testSeries, 0, 8, true);
                $remainingSeries = array_slice($testSeries, 8, null, true);

                $otherData = array_fill(0, count($labels), 0);
                foreach ($remainingSeries as $rem) {
                    foreach ($rem['data'] as $idx => $v) {
                        $otherData[$idx] += $v;
                    }
                }

                $datasets = array_values($topSeries);
                $datasets[] = [
                    'label' => 'Other '.count($remainingSeries).' Tests Combined',
                    'color' => '#64748B',
                    'data' => $otherData,
                ];
            } else {
                $datasets = array_values($testSeries);
            }
        }

        $changeAbs = $lastVal - $firstVal;
        $ticker = [
            'latest_value' => $lastVal,
            'baseline_value' => $avgTotal,
            'total_volume' => $sumTotal,
            'average_volume' => $avgTotal,
            'period_high' => $highTotal,
            'period_low' => $lowTotal,
            'change_absolute' => ($changeAbs >= 0 ? '+' : '').number_format($changeAbs, 0),
            'change_percent' => ($changePct >= 0 ? '+' : '').$changePct.'%',
            'change_direction' => $changeDirection,
            'period_display' => match (strtolower($period)) {
                'day', '1d' => '1D',
                '5d' => '5D',
                'month', '1m' => '1M',
                '6m' => '6M',
                'ytd' => 'YTD',
                'year', '1y' => '1Y',
                '5_years', '5y' => '5Y',
                'max' => 'Max',
                default => '1W',
            },
        ];

        return [
            'period' => $period,
            'metric' => $metric,
            'tests' => $selectedTests,
            'timeline' => [
                'labels' => $labels,
                'datasets' => $datasets,
                'summary' => $testSummaryList,
            ],
            'ticker' => $ticker,
            'labels' => $labels,
            'total_series' => $totalSeries,
            'selected_total_series' => $selectedAggregateSeries,
            'test_series' => array_values($testSeries),
            'summary' => [
                'total_volume' => $sumTotal,
                'average_volume' => $avgTotal,
                'high_volume' => $highTotal,
                'low_volume' => $lowTotal,
                'change_pct' => $changePct,
                'period_label' => ucfirst(str_replace('_', ' ', $period)),
            ],
            'available_tests' => array_keys(self::MASTER_TEST_CATALOG),
            'lab_types' => self::LAB_TYPES,
        ];
    }
}
