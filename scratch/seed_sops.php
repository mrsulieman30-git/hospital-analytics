<?php

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
use App\Models\Sop;
use App\Models\User;
use App\Services\SopDocumentParserService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Storage;

$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Seeding SOPs with valid DOCX files and leaflets...\n";

// Ensure storage directories exist
Storage::disk('public')->makeDirectory('sops');
Storage::disk('public')->makeDirectory('sops/leaflets');

$superadmin = User::where('email', 'superadmin@reports.com')->first();
$creatorId = $superadmin ? $superadmin->id : 1;

// Function to generate a real DOCX file
function generateDocx(string $destinationPath, string $bodyXml): void
{
    $zip = new ZipArchive;
    if ($zip->open($destinationPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new RuntimeException("Could not create ZIP archive at: {$destinationPath}");
    }

    $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'.
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'.
        '<Default Extension="xml" ContentType="application/xml"/>'.
        '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'.
        '</Types>';

    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'.
        '</Relationships>';

    $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".
        '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'.
        '<w:body>'.$bodyXml.'</w:body>'.
        '</w:document>';

    $zip->addFromString('[Content_Types].xml', $contentTypes);
    $zip->addEmptyDir('_rels');
    $zip->addFromString('_rels/.rels', $rels);
    $zip->addEmptyDir('word');
    $zip->addFromString('word/document.xml', $documentXml);
    $zip->close();
}

// Function to generate an SVG test kit leaflet insert
function generateLeafletSvg(string $title, string $catalogNo, string $storageRelativePath): string
{
    $fullPath = storage_path('app/public/'.$storageRelativePath);
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="1000" viewBox="0 0 800 1000">
  <rect width="800" height="1000" fill="#FFFFFF"/>
  <rect width="800" height="60" fill="#124E78"/>
  <text x="30" y="40" fill="#FFFFFF" font-family="Arial, sans-serif" font-size="22" font-weight="bold">DIAGNOSTIC PACKAGE INSERT • CLINICAL LEAFLET</text>
  
  <rect x="30" y="80" width="740" height="100" rx="8" fill="#F8FAFC" stroke="#CBD5E1" stroke-width="1.5"/>
  <text x="50" y="115" fill="#1E293B" font-family="Arial, sans-serif" font-size="18" font-weight="bold">{$title}</text>
  <text x="50" y="145" fill="#64748B" font-family="Arial, sans-serif" font-size="13">Catalog Ref: {$catalogNo} | Storage: 2°C – 30°C | In-Vitro Diagnostic Use Only</text>
  <text x="50" y="165" fill="#059669" font-family="Arial, sans-serif" font-size="12" font-weight="bold">CE-IVD Certified • ISO 15189 Quality Compliant</text>

  <!-- Section 1 -->
  <rect x="30" y="200" width="740" height="35" fill="#F1F5F9"/>
  <text x="45" y="224" fill="#124E78" font-family="Arial, sans-serif" font-size="15" font-weight="bold">1. PRINCIPLE OF THE TEST</text>
  <text x="35" y="260" fill="#334155" font-family="Arial, sans-serif" font-size="13">The test utilizes specific monoclonal antibody-antigen reactions with fluorescence or colloidal gold</text>
  <text x="35" y="280" fill="#334155" font-family="Arial, sans-serif" font-size="13">detection to quantify analyte concentration in human whole blood, serum, or plasma specimens.</text>

  <!-- Section 2: Reagents & Components -->
  <rect x="30" y="310" width="740" height="35" fill="#F1F5F9"/>
  <text x="45" y="334" fill="#124E78" font-family="Arial, sans-serif" font-size="15" font-weight="bold">2. REAGENTS AND MATERIAL SUPPLIED</text>
  <rect x="30" y="355" width="740" height="30" fill="#E2E8F0"/>
  <text x="45" y="375" fill="#0F172A" font-family="Arial, sans-serif" font-size="12" font-weight="bold">Item Description</text>
  <text x="350" y="375" fill="#0F172A" font-family="Arial, sans-serif" font-size="12" font-weight="bold">Quantity</text>
  <text x="550" y="375" fill="#0F172A" font-family="Arial, sans-serif" font-size="12" font-weight="bold">Active Ingredient</text>

  <rect x="30" y="385" width="740" height="30" fill="#FFFFFF" stroke="#E2E8F0"/>
  <text x="45" y="405" fill="#334155" font-family="Arial, sans-serif" font-size="12">Individual Test Cassettes</text>
  <text x="350" y="405" fill="#334155" font-family="Arial, sans-serif" font-size="12">25 Cassettes / Box</text>
  <text x="550" y="405" fill="#334155" font-family="Arial, sans-serif" font-size="12">Conjugated Specific Antibodies</text>

  <rect x="30" y="415" width="740" height="30" fill="#F8FAFC" stroke="#E2E8F0"/>
  <text x="45" y="435" fill="#334155" font-family="Arial, sans-serif" font-size="12">Sample Diluent Buffer</text>
  <text x="350" y="435" fill="#334155" font-family="Arial, sans-serif" font-size="12">1 x 5.0 mL Dropper</text>
  <text x="550" y="435" fill="#334155" font-family="Arial, sans-serif" font-size="12">Phosphate Buffer pH 7.4, 0.05% NaN3</text>

  <!-- Section 3: Step-by-Step Procedure -->
  <rect x="30" y="465" width="740" height="35" fill="#F1F5F9"/>
  <text x="45" y="489" fill="#124E78" font-family="Arial, sans-serif" font-size="15" font-weight="bold">3. ASSAY PROCEDURE</text>
  
  <rect x="30" y="515" width="230" height="140" rx="6" fill="#F8FAFC" stroke="#CBD5E1"/>
  <text x="45" y="540" fill="#124E78" font-family="Arial, sans-serif" font-size="13" font-weight="bold">Step 1: Equilibration</text>
  <text x="45" y="565" fill="#475569" font-family="Arial, sans-serif" font-size="12">Bring test cassettes,</text>
  <text x="45" y="585" fill="#475569" font-family="Arial, sans-serif" font-size="12">buffer and specimens to</text>
  <text x="45" y="605" fill="#475569" font-family="Arial, sans-serif" font-size="12">room temperature (18-25°C)</text>
  <text x="45" y="625" fill="#475569" font-family="Arial, sans-serif" font-size="12">before opening foil pouch.</text>

  <rect x="285" y="515" width="230" height="140" rx="6" fill="#F8FAFC" stroke="#CBD5E1"/>
  <text x="300" y="540" fill="#124E78" font-family="Arial, sans-serif" font-size="13" font-weight="bold">Step 2: Sample Loading</text>
  <text x="300" y="565" fill="#475569" font-family="Arial, sans-serif" font-size="12">Transfer 10 µL of serum</text>
  <text x="300" y="585" fill="#475569" font-family="Arial, sans-serif" font-size="12">or 20 µL whole blood to sample</text>
  <text x="300" y="605" fill="#475569" font-family="Arial, sans-serif" font-size="12">well (S), add 2 drops of</text>
  <text x="300" y="625" fill="#475569" font-family="Arial, sans-serif" font-size="12">diluent buffer vertically.</text>

  <rect x="540" y="515" width="230" height="140" rx="6" fill="#F8FAFC" stroke="#CBD5E1"/>
  <text x="555" y="540" fill="#124E78" font-family="Arial, sans-serif" font-size="13" font-weight="bold">Step 3: Timing & Reading</text>
  <text x="555" y="565" fill="#475569" font-family="Arial, sans-serif" font-size="12">Read quantitative results</text>
  <text x="555" y="585" fill="#475569" font-family="Arial, sans-serif" font-size="12">precisely at 15 minutes.</text>
  <text x="555" y="605" fill="#DC2626" font-family="Arial, sans-serif" font-size="12" font-weight="bold">DO NOT interpret result</text>
  <text x="555" y="625" fill="#DC2626" font-family="Arial, sans-serif" font-size="12" font-weight="bold">after 20 minutes.</text>

  <!-- Section 4: Performance & Reference Values -->
  <rect x="30" y="675" width="740" height="35" fill="#F1F5F9"/>
  <text x="45" y="699" fill="#124E78" font-family="Arial, sans-serif" font-size="15" font-weight="bold">4. REFERENCE INTERVALS & CLINICAL CUT-OFF</text>
  <rect x="30" y="720" width="740" height="110" fill="#FFFFFF" stroke="#CBD5E1"/>
  <text x="50" y="750" fill="#1E293B" font-family="Arial, sans-serif" font-size="13" font-weight="bold">Normal Clinical Cut-off: &lt; 0.05 ng/mL</text>
  <text x="50" y="775" fill="#475569" font-family="Arial, sans-serif" font-size="12">• Linearity Range: 0.01 ng/mL to 50.0 ng/mL</text>
  <text x="50" y="795" fill="#475569" font-family="Arial, sans-serif" font-size="12">• Precision Within-Run CV: &lt; 8.5% | Between-Run CV: &lt; 10.0%</text>
  <text x="50" y="815" fill="#475569" font-family="Arial, sans-serif" font-size="12">• Cross-reactivity: Non-detectable with Skeletal Troponin I, Myoglobin, or CK-MB up to 1000 ng/mL.</text>

  <!-- Footer Banner -->
  <rect x="0" y="940" width="800" height="60" fill="#0F172A"/>
  <text x="30" y="975" fill="#94A3B8" font-family="Arial, sans-serif" font-size="12">Sehtech Healthcare Solutions • Package Leaflet Archive ID: {$catalogNo}</text>
</svg>
SVG;
    file_put_contents($fullPath, $svg);

    return $storageRelativePath;
}

$parser = new SopDocumentParserService;

// SOP 1: Mindray BC-5000 CBC Analyzer SOP
$cbcFileName = 'SOP-LAB-CBC-001_Mindray_BC5000.docx';
$cbcRelPath = 'sops/'.$cbcFileName;
$cbcFullPath = storage_path('app/public/'.$cbcRelPath);

$cbcBodyXml = <<<'XML'
<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>STANDARD OPERATING PROCEDURE: MINDRAY BC-5000 AUTO HEMATOLOGY ANALYZER</w:t></w:r></w:p>
<w:p><w:r><w:t>Document Code: SOP-LAB-CBC-001 | Revision: 2.1 | Effective Date: 2026-01-15 | Department: Laboratory</w:t></w:r></w:p>
<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>1. PURPOSE &amp; CLINICAL PRINCIPLE</w:t></w:r></w:p>
<w:p><w:r><w:t>This standard operating procedure defines the rigorous workflow for running Complete Blood Count (CBC) with 5-part white blood cell differential using the Mindray BC-5000 Hematology Analyzer. The analyzer uses tri-angle semiconductor laser scatter combined with chemical dye technology to classify white blood cells into 5 sub-populations (Neutrophils, Lymphocytes, Monocytes, Eosinophils, and Basophils).</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>2. SPECIMEN REQUIREMENTS &amp; STABILITY</w:t></w:r></w:p>
<w:p><w:r><w:t>• Specimen Type: Venous whole blood collected in K2-EDTA or K3-EDTA evacuated tubes (Lavender top).</w:t></w:r></w:p>
<w:p><w:r><w:t>• Minimum Volume: 1.0 mL (Whole blood mode requires only 15 µL of sample aspiration).</w:t></w:r></w:p>
<w:p><w:r><w:t>• Stability: Up to 8 hours at controlled room temperature (18-25°C), or up to 24 hours refrigerated at 2-8°C.</w:t></w:r></w:p>
<w:p><w:r><w:t>• Rejection Criteria: Clotted specimens, severe hemolysis, grossly underfilled tubes (ratio &lt; 1:9 EDTA), or unlabelled tubes.</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>3. DAILY STARTUP &amp; QUALITY CONTROL (QC) PROTOCOL</w:t></w:r></w:p>
<w:p><w:r><w:t>1. Inspect reagent levels: M-52D Diluent, M-52DIFF Lyse, M-52LH Lyse, and Probe Cleanser.</w:t></w:r></w:p>
<w:p><w:r><w:t>2. Power on the analyzer. The system executes automated self-checks (hydraulic background, optic alignment, temperature stabilization).</w:t></w:r></w:p>
<w:p><w:r><w:t>3. Background Check: Verify that WBC &lt;= 0.20 x10^9/L, RBC &lt;= 0.02 x10^12/L, HGB &lt;= 1 g/L, PLT &lt;= 10 x10^9/L.</w:t></w:r></w:p>
<w:p><w:r><w:t>4. Run Tri-level Controls (B-30 Tri-level CBC controls: Low, Normal, High) daily before processing any patient specimens.</w:t></w:r></w:p>
<w:p><w:r><w:t>5. Confirm Westgard rules compliance (1-2s, 1-3s, 2-2s). If violation occurs, recalibrate before patient testing.</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>4. ADULT REFERENCE RANGES</w:t></w:r></w:p>
<w:tbl>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Parameter</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Conventional Units</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Male Reference</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Female Reference</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>WBC (White Blood Cells)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>10^9 / L</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4.0 – 10.0</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4.0 – 10.0</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>RBC (Red Blood Cells)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>10^12 / L</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4.5 – 5.9</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>4.0 – 5.2</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>HGB (Hemoglobin)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>g / dL</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>13.5 – 17.5</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>12.0 – 15.5</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>HCT (Hematocrit)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>%</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>41.0 – 53.0</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>36.0 – 46.0</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>PLT (Platelets)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>10^9 / L</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>150 – 450</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>150 – 450</w:t></w:r></w:p></w:tc>
  </w:tr>
</w:tbl>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>5. CRITICAL ACTION THRESHOLDS (PANIC VALUES)</w:t></w:r></w:p>
<w:p><w:r><w:t>Notify ordering physician immediately if any of the following panic cutoffs are triggered:</w:t></w:r></w:p>
<w:p><w:r><w:t>• WBC: &lt; 2.0 x10^9/L or &gt; 30.0 x10^9/L</w:t></w:r></w:p>
<w:p><w:r><w:t>• Hemoglobin: &lt; 7.0 g/dL or &gt; 20.0 g/dL</w:t></w:r></w:p>
<w:p><w:r><w:t>• Platelets: &lt; 40 x10^9/L or &gt; 1000 x10^9/L</w:t></w:r></w:p>
XML;

generateDocx($cbcFullPath, $cbcBodyXml);
$cbcExtracted = $parser->parse($cbcFullPath, 'docx');

// Generate Leaflet SVG for Mindray Reagents
$cbcLeaflet1 = generateLeafletSvg('M-52D Reagent Diluent & DIFF Lyse Package Insert', 'MIN-BC5000-INS-01', 'sops/leaflets/leaflet_mindray_cbc_1.svg');
$cbcLeaflet2 = generateLeafletSvg('Tri-Level Hematology Control Pack Calibration Insert', 'CTL-B30-LOT904', 'sops/leaflets/leaflet_mindray_cbc_2.svg');

Sop::updateOrCreate(
    ['sop_code' => 'SOP-LAB-CBC-001'],
    [
        'title' => 'Standard Procedure for Mindray BC-5000 5-Part Differential CBC Analyzer',
        'department' => 'laboratory',
        'category' => 'Hematology & Coagulation',
        'version' => '2.1',
        'file_path' => $cbcRelPath,
        'file_name' => $cbcFileName,
        'file_type' => 'docx',
        'file_size' => filesize($cbcFullPath),
        'is_downloadable' => true,
        'extracted_summary' => $cbcExtracted['summary'],
        'extracted_html' => $cbcExtracted['html'],
        'extracted_metadata' => $cbcExtracted['metadata'],
        'leaflet_images' => [$cbcLeaflet1, $cbcLeaflet2],
        'effective_date' => '2026-01-15',
        'review_date' => '2027-01-15',
        'author_name' => 'Dr. Liban - Head of Laboratory',
        'created_by' => $creatorId,
        'status' => 'approved',
    ]
);

// SOP 2: Troponin I Rapid Quantitative Immunoassay
$tropFileName = 'SOP-LAB-TROP-002_Troponin_I_Protocol.docx';
$tropRelPath = 'sops/'.$tropFileName;
$tropFullPath = storage_path('app/public/'.$tropRelPath);

$tropBodyXml = <<<'XML'
<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>CLINICAL PROTOCOL: HIGH-SENSITIVITY CARDIAC TROPONIN I (cTnI) QUANTITATIVE RAPID ASSAY</w:t></w:r></w:p>
<w:p><w:r><w:t>Document Code: SOP-LAB-TROP-002 | Revision: 1.4 | Effective Date: 2026-02-01 | Department: Laboratory</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>1. CLINICAL UTILITY &amp; INTENDED USE</w:t></w:r></w:p>
<w:p><w:r><w:t>Cardiac Troponin I (cTnI) is the gold standard diagnostic biomarker for myocardial necrosis and acute coronary syndrome (ACS / NSTEMI). This assay provides quantitative fluorescence-immunochromatographic measurement within 15 minutes of blood draw.</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>2. SPECIMEN COLLECTION &amp; HANDLING</w:t></w:r></w:p>
<w:p><w:r><w:t>• Specimen: Venous whole blood or serum in lithium heparin or red top tube.</w:t></w:r></w:p>
<w:p><w:r><w:t>• Centrifugation: Centrifuge clotted blood at 3000 RPM for 10 minutes to obtain clear serum.</w:t></w:r></w:p>
<w:p><w:r><w:t>• Turnaround Time (TAT) Target: Under 30 minutes from arrival in lab to critical result notification.</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>3. PROCEDURE &amp; TEST SEQUENCE</w:t></w:r></w:p>
<w:p><w:r><w:t>1. Insert ID chip into fluorescence analyzer slot.</w:t></w:r></w:p>
<w:p><w:r><w:t>2. Pipette 75 µL of serum specimen into the dilution tube buffer and mix gently 10 times.</w:t></w:r></w:p>
<w:p><w:r><w:t>3. Dispense 75 µL of the sample-buffer mixture into sample port (S) of test cartridge.</w:t></w:r></w:p>
<w:p><w:r><w:t>4. Insert cartridge into analyzer tray and select Standard Mode (15 minutes incubation).</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>4. REFERENCE RANGES &amp; CLINICAL DECISION LIMITS</w:t></w:r></w:p>
<w:tbl>
  <w:tr>
    <w:tc><w:p><w:r><w:t>cTnI Level</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Interpretation</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Mandatory Action</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>&lt; 0.04 ng/mL</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Normal / Non-elevated</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Routine report; repeat at 3 hours if clinical suspicion remains</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>0.04 - 0.10 ng/mL</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Borderline Elevation</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Notify ER Doctor; order serial delta check at 2 hours</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>&gt; 0.10 ng/mL</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Positive Myocardial Necrosis</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>CRITICAL PANIC CALL: Immediate verbal alert to Cardiology/ER</w:t></w:r></w:p></w:tc>
  </w:tr>
</w:tbl>
XML;

generateDocx($tropFullPath, $tropBodyXml);
$tropExtracted = $parser->parse($tropFullPath, 'docx');

$tropLeaflet = generateLeafletSvg('Cardiac Troponin I Reagent Kit Technical Leaflet & Calibration Guide', 'FIA-CTNI-9020', 'sops/leaflets/leaflet_troponin_insert.svg');

Sop::updateOrCreate(
    ['sop_code' => 'SOP-LAB-TROP-002'],
    [
        'title' => 'Cardiac Troponin I (cTnI) Quantitative Rapid Immunoassay Protocol',
        'department' => 'laboratory',
        'category' => 'Cardiovascular & Emergency Biomarkers',
        'version' => '1.4',
        'file_path' => $tropRelPath,
        'file_name' => $tropFileName,
        'file_type' => 'docx',
        'file_size' => filesize($tropFullPath),
        'is_downloadable' => false, // Set to false to test Super Admin download restrictions!
        'extracted_summary' => $tropExtracted['summary'],
        'extracted_html' => $tropExtracted['html'],
        'extracted_metadata' => $tropExtracted['metadata'],
        'leaflet_images' => [$tropLeaflet],
        'effective_date' => '2026-02-01',
        'review_date' => '2027-02-01',
        'author_name' => 'Dr. Amina - Senior Biochemist',
        'created_by' => $creatorId,
        'status' => 'approved',
    ]
);

// SOP 3: The exact user template for CK-MB Finecare FIA
$ckmbTemplateText = <<<'TXT'
CREATINE KINASE-MB (CK-MB) — FINECARE™ FIA
KAAFI HOSPITALS — CLINICAL CHEMISTRY & CARDIAC BIOMARKERS
Benchtop Standard Operating Procedure: Creatine Kinase-MB (CK-MB)

Document Control Information	Details
Document Code	KH-LAB-SOP-009
Effective Date	October 2026
Version	1.0 Bench Quick Reference
Kit Brand	Finecare™ CK-MB Rapid Quantitative Test (Wondfo Biotech, Cat. No. W205)
Department	Clinical Chemistry / Emergency Diagnostics
Laboratory Manager	Mohammad Sulieman Ibrahim
Hospital Manager	Muse Mohammed Abdulahi

________________________________________
1. Sample Type & Specimen Handling
●	Specimen Requirements: Human Serum, Plasma, or Venous Whole Blood.
●	Approved Anticoagulants: EDTA or Heparin only (do NOT use Sodium Citrate or other anticoagulants, as they have not been evaluated for this assay).
●	Separation Requirement: Separate serum or plasma from blood as soon as possible to prevent hemolysis.
●	Storage & Stability: Venous Whole Blood: Test immediately (within 8 hours), or store at 2–8°C for up to 2 days. Do not freeze whole blood. Serum / Plasma: Test within 8 hours, store at 2–8°C for up to 7 days, or freeze below -20°C for up to 12 months (avoid repeated freeze-thaw cycles).
●	Pre-Testing Requirement: Bring all refrigerated/thawed specimens to room temperature and mix well before testing.
●	Rejection Criteria: Hemolyzed samples, clotted whole blood, or specimens collected in citrate/unapproved anticoagulants.

________________________________________
2. Reagent Preparation & Storage
●	Kit Components Provided (25 Tests): 25 sealed Test Cartridges (with desiccant), 25 Detection Buffer Tubes, 25 Pipette Tips, 1 Lot-Specific ID Chip, and Instructions for Use.
●	Preparation: Ready to use. Equilibrate sealed Test Cartridge and Detection Buffer Tube to room temperature (15–30°C) before opening.
●	Lot Matching Requirement: Ensure the Lot Number on the Test Cartridge, Detection Buffer Tube, and ID Chip all match exactly. Insert the matching ID Chip into the Finecare™ FIA Meter before testing.
●	Storage & Shelf Life: Unopened Kit: Stable for 24 months when stored at 4–30°C in a dry place away from direct sunlight. Opened Cartridge Stability: Once removed from the foil pouch, the Test Cartridge must be used within 1 hour.
●	Indicator Check: Unused cartridges display a blue control line on the test membrane, which disappears after sample loading. Discard cartridge if pouch is punctured or blue line is missing before use.

________________________________________
3. System Parameters

Parameter	Specification / Operational Value
Method / Principle	Fluorescence Immunoassay (Sandwich Immunodetection)
Compatible Analyzers	Finecare™ FIA Meter (Models FS-112, FS-113, FS-114, FS-205)
Calibration	Lot-specific ID Chip (Standardized against internal reference material)
Specimen Volume (Step 1)	75 µL (Whole Blood, Serum, or Plasma into Detection Buffer)
Sample Mixture Volume (Step 2)	75 µL (Diluted mixture loaded into Cartridge Sample Well)
Reaction Temperature	Room Temperature (15–30°C)
Reaction / Incubation Time	15 minutes
Result Unit	ng/mL
Limit of Detection (LoD)	0.3 ng/mL
Measuring / Linearity Range	0.3 – 100.0 ng/mL (r ≥ 0.9900)
Precision (Intra-Lot & Inter-Lot)	CV ≤ 15%

________________________________________
4. Analytical Procedure
1.	Preparation: Bring reagents and sample to room temperature. Confirm Lot No. on Test Cartridge, Detection Buffer, and ID Chip match. Insert ID Chip into the meter.
Step	Action	Volume / Parameter
Step 1: ID Chip	Verify matching Lot No. and insert ID Chip into FIA Meter	Lot-matched
Step 2: Sampling	Draw specimen and dispense into Detection Buffer Tube	75 µL
Step 3: Mixing	Close buffer tube lid and shake thoroughly	~10 inversions
Step 4: Loading	Draw mixed buffer-sample and dispense into Cartridge well	75 µL
Step 5: Testing	Incubate and scan in Finecare™ FIA Meter	15 minutes
2.	Sampling: Using a transfer pipette, draw 75 µL of serum, plasma, or whole blood and add it into the Detection Buffer Tube.
3.	Mixing: Close the Detection Buffer Tube lid tightly and shake about 10 times to mix thoroughly.
4.	Loading: Pipette 75 µL of the sample mixture and load it into the sample well of the Test Cartridge.
5.	Testing (Choose Mode): Standard Test Mode: Immediately insert the loaded Test Cartridge into the meter holder and press "Test" (FS-112) or "Start Test" (FS-113, FS-114). For FS-205: press "Test", input specimen type, press "Start", and insert cartridge. The meter automatically incubates and displays the result at 15 minutes. Quick Test Mode: Start a 15-minute timer immediately after loading the sample mixture and incubate the cartridge on the bench at room temperature. Exactly at 15 minutes, insert the cartridge into the meter and press "Test" / "Start Test" to read results immediately.

________________________________________
5. Calculations & Linearity
Automated Calculation & Linearity
Automated Calculation: The Finecare™ FIA Meter automatically measures fluorescence intensity from the detector antibody-antigen sandwich complexes and calculates CK-MB concentration using the pre-programmed lot calibration curve on the ID Chip. Results are displayed directly as XXX.XX ng/mL.
Linearity & Reporting Limits:
●	Analytical measuring range is 0.3 – 100.0 ng/mL.
●	Results below 0.3 ng/mL are reported as "< 0.30 ng/mL".
●	Results above 100.0 ng/mL are reported as "> 100.00 ng/mL".

________________________________________
6. Normal Range (Reference Values) & Clinical Interpretation
Reference Interval Table

CK-MB Concentration	Clinical Reference / Interpretation
0.0 – 5.0 ng/mL (≤ 5.0 ng/mL)	Normal — Patient preliminarily determined not to have suffered myocardial infarction
> 5.0 ng/mL	Elevated / Abnormal — Indicative of probable Acute Myocardial Infarction (AMI)

●	Temporal Kinetics: Following acute myocardial infarction, serum CK-MB rises within 3–8 hours, reaches peak levels at 8–24 hours, and returns to normal baseline within 3 days (72 hours).
●	Critical Action Alert: Any result > 5.0 ng/mL in a patient presenting with chest pain or suspected acute coronary syndrome requires immediate STAT notification to the attending physician and serial monitoring alongside cardiac Troponin I (cTnI) and ECG findings.

________________________________________
7. Troubleshooting & Analytical Interferences

Problem / Interference	Suspected Root Cause	Corrective Action
"Invalid" Flag / Insufficient Signal	Insufficient sample mixture volume (< 75 µL), clogged membrane from micro-clots, or failed capillary migration.	Verify specimen has no clots, mix buffer thoroughly, and repeat test with a new Test Cartridge.
Lot Mismatch Error	Cartridge Lot No. does not match inserted ID Chip.	Insert the correct Lot-specific ID Chip packaged with the current kit box.
Blue Line Absent Before Use	Pouch seal compromised or moisture damage/previously used cartridge.	Discard cartridge and open a fresh sealed pouch.
Falsely Altered Results	Hemolyzed sample, unapproved anticoagulant (e.g., citrate), or thermal degradation of CK-MB isoenzyme.	Reject hemolyzed or citrate tubes; request fresh redraw in EDTA, heparin, or plain serum tube.
Tolerated Interferences (Verified No Interference)	Cholesterol ≤ 60 mg/mL (6,000 mg/dL), Bilirubin ≤ 2 mg/mL (200 mg/dL), and Triglycerides ≤ 40 mg/mL (4,000 mg/dL) do NOT interfere.	No interference observed within these limits.
QC Out of Range	Meter optical drift or degraded reagent.	Run Finecare™ CK-MB Control (Cat. No. W807 / W810) or cTnI/CK-MB/Myo Multi-Control (Cat. No. W817) and verify meter calibration.
TXT;

$ckmbFileName = 'KH-LAB-SOP-009_CK_MB_Finecare_FIA.docx';
$ckmbRelPath = 'sops/'.$ckmbFileName;
$ckmbFullPath = storage_path('app/public/'.$ckmbRelPath);

// Create both the docx file and save text file
$ckmbBodyXml = <<<'XML'
<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>CREATINE KINASE-MB (CK-MB) — FINECARE™ FIA</w:t></w:r></w:p>
<w:p><w:r><w:t>KAAFI HOSPITALS — CLINICAL CHEMISTRY &amp; CARDIAC BIOMARKERS</w:t></w:r></w:p>
<w:p><w:r><w:t>Benchtop Standard Operating Procedure: Creatine Kinase-MB (CK-MB)</w:t></w:r></w:p>

<w:tbl>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Document Control Information</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Details</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Document Code</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>KH-LAB-SOP-009</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Effective Date</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>October 2026</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Version</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>1.0 Bench Quick Reference</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Kit Brand</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Finecare™ CK-MB Rapid Quantitative Test (Wondfo Biotech, Cat. No. W205)</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Department</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Clinical Chemistry / Emergency Diagnostics</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Laboratory Manager</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Mohammad Sulieman Ibrahim</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Hospital Manager</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Muse Mohammed Abdulahi</w:t></w:r></w:p></w:tc>
  </w:tr>
</w:tbl>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>1. Sample Type &amp; Specimen Handling</w:t></w:r></w:p>
<w:p><w:r><w:t>● Specimen Requirements: Human Serum, Plasma, or Venous Whole Blood.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Approved Anticoagulants: EDTA or Heparin only (do NOT use Sodium Citrate or other anticoagulants, as they have not been evaluated for this assay).</w:t></w:r></w:p>
<w:p><w:r><w:t>● Separation Requirement: Separate serum or plasma from blood as soon as possible to prevent hemolysis.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Storage &amp; Stability: Venous Whole Blood: Test immediately (within 8 hours), or store at 2–8°C for up to 2 days. Do not freeze whole blood. Serum / Plasma: Test within 8 hours, store at 2–8°C for up to 7 days, or freeze below -20°C for up to 12 months (avoid repeated freeze-thaw cycles).</w:t></w:r></w:p>
<w:p><w:r><w:t>● Pre-Testing Requirement: Bring all refrigerated/thawed specimens to room temperature and mix well before testing.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Rejection Criteria: Hemolyzed samples, clotted whole blood, or specimens collected in citrate/unapproved anticoagulants.</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>2. Reagent Preparation &amp; Storage</w:t></w:r></w:p>
<w:p><w:r><w:t>● Kit Components Provided (25 Tests): 25 sealed Test Cartridges (with desiccant), 25 Detection Buffer Tubes, 25 Pipette Tips, 1 Lot-Specific ID Chip, and Instructions for Use.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Preparation: Ready to use. Equilibrate sealed Test Cartridge and Detection Buffer Tube to room temperature (15–30°C) before opening.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Lot Matching Requirement: Ensure the Lot Number on the Test Cartridge, Detection Buffer Tube, and ID Chip all match exactly. Insert the matching ID Chip into the Finecare™ FIA Meter before testing.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Storage &amp; Shelf Life: Unopened Kit: Stable for 24 months when stored at 4–30°C in a dry place away from direct sunlight. Opened Cartridge Stability: Once removed from the foil pouch, the Test Cartridge must be used within 1 hour.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Indicator Check: Unused cartridges display a blue control line on the test membrane, which disappears after sample loading. Discard cartridge if pouch is punctured or blue line is missing before use.</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>3. System Parameters</w:t></w:r></w:p>
<w:tbl>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Parameter</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Specification / Operational Value</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Method / Principle</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Fluorescence Immunoassay (Sandwich Immunodetection)</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Compatible Analyzers</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Finecare™ FIA Meter (Models FS-112, FS-113, FS-114, FS-205)</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Calibration</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Lot-specific ID Chip (Standardized against internal reference material)</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Specimen Volume (Step 1)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>75 µL (Whole Blood, Serum, or Plasma into Detection Buffer)</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Sample Mixture Volume (Step 2)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>75 µL (Diluted mixture loaded into Cartridge Sample Well)</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Reaction Temperature</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Room Temperature (15–30°C)</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Reaction / Incubation Time</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>15 minutes</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Result Unit</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>ng/mL</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Limit of Detection (LoD)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>0.3 ng/mL</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Measuring / Linearity Range</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>0.3 – 100.0 ng/mL (r ≥ 0.9900)</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Precision (Intra-Lot &amp; Inter-Lot)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>CV ≤ 15%</w:t></w:r></w:p></w:tc>
  </w:tr>
</w:tbl>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>4. Analytical Procedure</w:t></w:r></w:p>
<w:p><w:r><w:t>1. Preparation: Bring reagents and sample to room temperature. Confirm Lot No. on Test Cartridge, Detection Buffer, and ID Chip match. Insert ID Chip into the meter.</w:t></w:r></w:p>

<w:tbl>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Step</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Action</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Volume / Parameter</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Step 1: ID Chip</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Verify matching Lot No. and insert ID Chip into FIA Meter</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Lot-matched</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Step 2: Sampling</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Draw specimen and dispense into Detection Buffer Tube</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>75 µL</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Step 3: Mixing</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Close buffer tube lid and shake thoroughly</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>~10 inversions</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Step 4: Loading</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Draw mixed buffer-sample and dispense into Cartridge well</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>75 µL</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Step 5: Testing</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Incubate and scan in Finecare™ FIA Meter</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>15 minutes</w:t></w:r></w:p></w:tc>
  </w:tr>
</w:tbl>

<w:p><w:r><w:t>2. Sampling: Using a transfer pipette, draw 75 µL of serum, plasma, or whole blood and add it into the Detection Buffer Tube.</w:t></w:r></w:p>
<w:p><w:r><w:t>3. Mixing: Close the Detection Buffer Tube lid tightly and shake about 10 times to mix thoroughly.</w:t></w:r></w:p>
<w:p><w:r><w:t>4. Loading: Pipette 75 µL of the sample mixture and load it into the sample well of the Test Cartridge.</w:t></w:r></w:p>
<w:p><w:r><w:t>5. Testing (Choose Mode): Standard Test Mode: Immediately insert the loaded Test Cartridge into the meter holder and press "Test" (FS-112) or "Start Test" (FS-113, FS-114). For FS-205: press "Test", input specimen type, press "Start", and insert cartridge. The meter automatically incubates and displays the result at 15 minutes. Quick Test Mode: Start a 15-minute timer immediately after loading the sample mixture and incubate the cartridge on the bench at room temperature. Exactly at 15 minutes, insert the cartridge into the meter and press "Test" / "Start Test" to read results immediately.</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>5. Calculations &amp; Linearity</w:t></w:r></w:p>
<w:p><w:r><w:t>Automated Calculation: The Finecare™ FIA Meter automatically measures fluorescence intensity from the detector antibody-antigen sandwich complexes and calculates CK-MB concentration using the pre-programmed lot calibration curve on the ID Chip. Results are displayed directly as XXX.XX ng/mL.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Analytical measuring range is 0.3 – 100.0 ng/mL.</w:t></w:r></w:p>
<w:p><w:r><w:t>● Results below 0.3 ng/mL are reported as "&lt; 0.30 ng/mL".</w:t></w:r></w:p>
<w:p><w:r><w:t>● Results above 100.0 ng/mL are reported as "&gt; 100.00 ng/mL".</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>6. Normal Range (Reference Values) &amp; Clinical Interpretation</w:t></w:r></w:p>
<w:tbl>
  <w:tr>
    <w:tc><w:p><w:r><w:t>CK-MB Concentration</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Clinical Reference / Interpretation</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>0.0 – 5.0 ng/mL (≤ 5.0 ng/mL)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Normal — Patient preliminarily determined not to have suffered myocardial infarction</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>&gt; 5.0 ng/mL</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Elevated / Abnormal — Indicative of probable Acute Myocardial Infarction (AMI)</w:t></w:r></w:p></w:tc>
  </w:tr>
</w:tbl>
<w:p><w:r><w:t>● Temporal Kinetics: Following acute myocardial infarction, serum CK-MB rises within 3–8 hours, reaches peak levels at 8–24 hours, and returns to normal baseline within 3 days (72 hours).</w:t></w:r></w:p>
<w:p><w:r><w:t>● Critical Action Alert: Any result &gt; 5.0 ng/mL in a patient presenting with chest pain or suspected acute coronary syndrome requires immediate STAT notification to the attending physician and serial monitoring alongside cardiac Troponin I (cTnI) and ECG findings.</w:t></w:r></w:p>

<w:p><w:pPr><w:pStyle w:val="Heading2"/></w:pPr><w:r><w:t>7. Troubleshooting &amp; Analytical Interferences</w:t></w:r></w:p>
<w:tbl>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Problem / Interference</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Suspected Root Cause</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Corrective Action</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>"Invalid" Flag / Insufficient Signal</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Insufficient sample mixture volume (&lt; 75 µL), clogged membrane from micro-clots, or failed capillary migration.</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Verify specimen has no clots, mix buffer thoroughly, and repeat test with a new Test Cartridge.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Lot Mismatch Error</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Cartridge Lot No. does not match inserted ID Chip.</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Insert the correct Lot-specific ID Chip packaged with the current kit box.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Blue Line Absent Before Use</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Pouch seal compromised or moisture damage/previously used cartridge.</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Discard cartridge and open a fresh sealed pouch.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Falsely Altered Results</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Hemolyzed sample, unapproved anticoagulant (e.g., citrate), or thermal degradation of CK-MB isoenzyme.</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Reject hemolyzed or citrate tubes; request fresh redraw in EDTA, heparin, or plain serum tube.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>Tolerated Interferences (Verified No Interference)</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Cholesterol ≤ 60 mg/mL (6,000 mg/dL), Bilirubin ≤ 2 mg/mL (200 mg/dL), and Triglycerides ≤ 40 mg/mL (4,000 mg/dL) do NOT interfere.</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>No interference observed within these limits.</w:t></w:r></w:p></w:tc>
  </w:tr>
  <w:tr>
    <w:tc><w:p><w:r><w:t>QC Out of Range</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Meter optical drift or degraded reagent.</w:t></w:r></w:p></w:tc>
    <w:tc><w:p><w:r><w:t>Run Finecare™ CK-MB Control (Cat. No. W807 / W810) or cTnI/CK-MB/Myo Multi-Control (Cat. No. W817) and verify meter calibration.</w:t></w:r></w:p></w:tc>
  </w:tr>
</w:tbl>
XML;

generateDocx($ckmbFullPath, $ckmbBodyXml);
$ckmbExtracted = $parser->parse($ckmbFullPath, 'docx');

$ckmbLeaflet = generateLeafletSvg('Finecare™ CK-MB Rapid Quantitative Reagent Kit Package Insert (Wondfo Biotech, Cat. W205)', 'WON-CKMB-W205', 'sops/leaflets/leaflet_finecare_ckmb_insert.svg');

Sop::updateOrCreate(
    ['sop_code' => 'KH-LAB-SOP-009'],
    [
        'title' => 'CREATINE KINASE-MB (CK-MB) — FINECARE™ FIA',
        'department' => 'laboratory',
        'category' => 'Clinical Chemistry & Cardiac Biomarkers',
        'version' => '1.0 Bench Quick Reference',
        'file_path' => $ckmbRelPath,
        'file_name' => $ckmbFileName,
        'file_type' => 'docx',
        'file_size' => filesize($ckmbFullPath),
        'is_downloadable' => true,
        'extracted_summary' => $ckmbExtracted['summary'],
        'extracted_html' => $ckmbExtracted['html'],
        'extracted_metadata' => $ckmbExtracted['metadata'],
        'leaflet_images' => [$ckmbLeaflet],
        'effective_date' => '2026-10-01',
        'review_date' => '2027-10-01',
        'author_name' => 'Mohammad Sulieman Ibrahim',
        'created_by' => $creatorId,
        'status' => 'approved',
    ]
);

echo "Successfully seeded SOPs with DOCX files and leaflets!\n";
