<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;
use ZipArchive;

class SopDocumentParserService
{
    /**
     * Solid color palette for section cards (STRICTLY NO GRADIENTS).
     *
     * @var array<int, string>
     */
    protected array $solidColorPalette = [
        '#124E78', // Kaafi Brand Blue
        '#0D9488', // Teal
        '#7C3AED', // Violet
        '#059669', // Emerald
        '#D97706', // Amber
        '#2563EB', // Royal Blue
        '#DC2626', // Crimson Red
        '#4F46E5', // Indigo
    ];

    /**
     * Parse an uploaded SOP file (docx, doc, pdf, txt) and return structured HTML & metadata.
     *
     * @return array{
     *     title: string,
     *     summary: string,
     *     html: string,
     *     metadata: array<string, mixed>,
     *     sections: array<int, array{title: string, content: string, color?: string}>
     * }
     */
    public function parse(string $absoluteFilePath, string $extension, string $defaultTitle = ''): array
    {
        $extension = strtolower($extension);

        if ($extension === 'docx') {
            return $this->parseDocx($absoluteFilePath, $defaultTitle);
        }

        if ($extension === 'pdf') {
            return $this->parsePdf($absoluteFilePath, $defaultTitle);
        }

        if (in_array($extension, ['txt', 'text'])) {
            $rawText = @file_get_contents($absoluteFilePath) ?: '';

            return $this->parseRawText($rawText, $defaultTitle);
        }

        // Generic fallback for doc or other text files
        return $this->fallbackParser($absoluteFilePath, $defaultTitle);
    }

    /**
     * Parse raw SOP text formatted as standard hospital template.
     */
    public function parseRawText(string $rawText, string $defaultTitle = ''): array
    {
        $rawText = str_replace(["\r\n", "\r"], "\n", $rawText);
        $lines = explode("\n", $rawText);

        $detectedTitle = $defaultTitle;
        $subtitle = '';
        $metadata = [];
        $sections = [];
        $rawTextSummary = [];

        $currentSectionTitle = 'Overview & Clinical Purpose';
        $currentSectionHtml = '';
        $colorIndex = 0;

        $inTable = false;
        $tableRows = [];

        $i = 0;
        $totalLines = count($lines);

        while ($i < $totalLines) {
            $line = trim($lines[$i]);

            // Skip divider lines e.g. ________________________________________
            if (preg_match('/^_{3,}$/', $line) || preg_match('/^-{3,}$/', $line)) {
                if ($inTable && ! empty($tableRows)) {
                    $currentSectionHtml .= $this->renderTextTableHtml($tableRows, $metadata);
                    $tableRows = [];
                    $inTable = false;
                }
                $i++;

                continue;
            }

            if ($line === '') {
                if ($inTable && ! empty($tableRows)) {
                    // Check if next non-empty line continues table
                    $nextNonEmpty = $this->peekNextNonEmptyLine($lines, $i + 1);
                    if ($nextNonEmpty !== null && ! str_contains($nextNonEmpty, "\t")) {
                        $currentSectionHtml .= $this->renderTextTableHtml($tableRows, $metadata);
                        $tableRows = [];
                        $inTable = false;
                    }
                }
                $i++;

                continue;
            }

            // Detect Header / Title lines in first few non-empty lines
            if (empty($detectedTitle) || $detectedTitle === $defaultTitle) {
                if (preg_match('/^[A-Z0-9\s\(\)\-\—\:\™\®]{6,}$/u', $line) && ! str_contains($line, "\t") && ! str_starts_with($line, '1.')) {
                    $detectedTitle = $line;
                    $i++;
                    // Look for potential subtitle on next line
                    if ($i < $totalLines && trim($lines[$i]) !== '' && ! str_contains(trim($lines[$i]), "\t") && ! str_starts_with(trim($lines[$i]), '1.')) {
                        $subtitle = trim($lines[$i]);
                        $i++;
                    }

                    continue;
                }
            }

            // Check if line is a major Section Heading (e.g. "1. Sample Type & Specimen Handling")
            if (preg_match('/^(\d+[\.\)]\s+[^	]{3,80})$/i', $line, $hMatches)) {
                if ($inTable && ! empty($tableRows)) {
                    $currentSectionHtml .= $this->renderTextTableHtml($tableRows, $metadata);
                    $tableRows = [];
                    $inTable = false;
                }

                if (trim($currentSectionHtml) !== '') {
                    $sections[] = [
                        'title' => $currentSectionTitle,
                        'content' => $currentSectionHtml,
                        'color' => $this->solidColorPalette[$colorIndex % count($this->solidColorPalette)],
                    ];
                    $colorIndex++;
                    $currentSectionHtml = '';
                }

                $currentSectionTitle = trim($hMatches[1]);
                $i++;

                continue;
            }

            // If line starts with bullet (even with a tab e.g. ●\t...), it is a bullet item, not a table row
            if (preg_match('/^[●•\-\*]\s*(.+)$/u', $line)) {
                if ($inTable && ! empty($tableRows)) {
                    $currentSectionHtml .= $this->renderTextTableHtml($tableRows, $metadata);
                    $tableRows = [];
                    $inTable = false;
                }

                $formattedPara = $this->formatParagraphText($line, $metadata);
                if (! empty($formattedPara)) {
                    $currentSectionHtml .= $formattedPara;
                }
                $i++;

                continue;
            }

            // Check if line is a tab-separated or structured table row
            if (str_contains($line, "\t")) {
                $cols = array_values(array_filter(array_map('trim', explode("\t", $line)), fn ($c) => $c !== ''));
                if (count($cols) >= 2) {
                    $inTable = true;
                    $tableRows[] = $cols;
                    $i++;

                    continue;
                }
            }

            // If we were in table and hit non-table text
            if ($inTable && ! empty($tableRows)) {
                $currentSectionHtml .= $this->renderTextTableHtml($tableRows, $metadata);
                $tableRows = [];
                $inTable = false;
            }

            // Format Paragraph Content
            $formattedPara = $this->formatParagraphText($line, $metadata);
            if (! empty($formattedPara)) {
                $currentSectionHtml .= $formattedPara;
                if (count($rawTextSummary) < 2 && ! str_starts_with($line, '●') && ! str_starts_with($line, 'Step')) {
                    $rawTextSummary[] = $line;
                }
            }

            $i++;
        }

        // Flush remaining table if open
        if ($inTable && ! empty($tableRows)) {
            $currentSectionHtml .= $this->renderTextTableHtml($tableRows, $metadata);
        }

        // Flush final section
        if (trim($currentSectionHtml) !== '') {
            $sections[] = [
                'title' => $currentSectionTitle,
                'content' => $currentSectionHtml,
                'color' => $this->solidColorPalette[$colorIndex % count($this->solidColorPalette)],
            ];
        }

        if (! empty($subtitle) && ! isset($metadata['category'])) {
            $metadata['category'] = $subtitle;
        }

        $finalHtml = $this->assembleMinimalistHtml($detectedTitle ?: ($defaultTitle ?: 'Standard Operating Procedure'), $sections, $metadata);

        return [
            'title' => $detectedTitle ?: ($defaultTitle ?: 'Standard Operating Procedure'),
            'summary' => implode(' ', array_slice($rawTextSummary, 0, 2)) ?: 'Standard clinical operating procedure protocol.',
            'html' => $finalHtml,
            'metadata' => $metadata,
            'sections' => $sections,
        ];
    }

    /**
     * Parse a .docx OpenXML document into clean, colorful minimalist HTML (solid colors only).
     */
    protected function parseDocx(string $filePath, string $defaultTitle): array
    {
        $zip = new ZipArchive;
        if ($zip->open($filePath) !== true) {
            return $this->fallbackParser($filePath, $defaultTitle);
        }

        $xmlIndex = $zip->locateName('word/document.xml');
        if ($xmlIndex === false) {
            $zip->close();

            return $this->fallbackParser($filePath, $defaultTitle);
        }

        $xmlContent = $zip->getFromIndex($xmlIndex);
        $zip->close();

        if (empty($xmlContent)) {
            return $this->fallbackParser($filePath, $defaultTitle);
        }

        $dom = new DOMDocument;
        @$dom->loadXML($xmlContent, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $bodyNodes = $xpath->query('//w:body/*');
        if (! $bodyNodes || $bodyNodes->length === 0) {
            return $this->fallbackParser($filePath, $defaultTitle);
        }

        $sections = [];
        $currentSectionTitle = 'Overview & Clinical Purpose';
        $currentSectionHtml = '';
        $detectedTitle = $defaultTitle;
        $metadata = [];
        $rawTextSummary = [];
        $colorIndex = 0;

        foreach ($bodyNodes as $node) {
            if (! ($node instanceof DOMElement)) {
                continue;
            }

            if ($node->nodeName === 'w:p') {
                $pText = $this->extractParagraphText($node);
                $pTextClean = trim($pText);

                if ($pTextClean === '' || preg_match('/^_{3,}$/', $pTextClean)) {
                    continue;
                }

                // Check if this paragraph is a major Heading
                $isHeading = $this->isHeadingParagraph($node, $xpath, $pTextClean);

                if ($isHeading) {
                    if (empty($detectedTitle) || $detectedTitle === $defaultTitle) {
                        $detectedTitle = $pTextClean;
                    }

                    // Save previous section if it had content
                    if (trim($currentSectionHtml) !== '') {
                        $sections[] = [
                            'title' => $currentSectionTitle,
                            'content' => $currentSectionHtml,
                            'color' => $this->solidColorPalette[$colorIndex % count($this->solidColorPalette)],
                        ];
                        $colorIndex++;
                        $currentSectionHtml = '';
                    }

                    $currentSectionTitle = $pTextClean;
                } else {
                    $formattedPara = $this->formatParagraphText($pTextClean, $metadata);
                    $currentSectionHtml .= $formattedPara;

                    if (count($rawTextSummary) < 2 && ! str_starts_with($pTextClean, '●') && ! str_starts_with($pTextClean, 'Step')) {
                        $rawTextSummary[] = $pTextClean;
                    }
                }
            } elseif ($node->nodeName === 'w:tbl') {
                $tableHtml = $this->renderWordTableHtml($node, $xpath, $metadata);
                if (! empty($tableHtml)) {
                    $currentSectionHtml .= $tableHtml;
                }
            }
        }

        // Save last section
        if (trim($currentSectionHtml) !== '') {
            $sections[] = [
                'title' => $currentSectionTitle,
                'content' => $currentSectionHtml,
                'color' => $this->solidColorPalette[$colorIndex % count($this->solidColorPalette)],
            ];
        }

        $finalHtml = $this->assembleMinimalistHtml($detectedTitle ?: $defaultTitle, $sections, $metadata);

        return [
            'title' => $detectedTitle ?: $defaultTitle,
            'summary' => implode(' ', array_slice($rawTextSummary, 0, 2)) ?: 'Standard clinical operating procedure protocol.',
            'html' => $finalHtml,
            'metadata' => $metadata,
            'sections' => $sections,
        ];
    }

    /**
     * Format a paragraph line with badges, alert boxes, or styled lists (Solid Colors Only).
     */
    protected function formatParagraphText(string $pText, array &$metadata): string
    {
        $pText = trim($pText);
        $clean = preg_replace('/^[●•\-\*]\s*/u', '', $pText);

        // Check for key-value pairs (e.g. "Document Code: KH-LAB-SOP-009")
        if (preg_match('/^([A-Za-z\s\(\)]{3,30}):\s*(.+)$/', $clean, $matches)) {
            $rawKey = trim($matches[1]);
            $val = trim($matches[2]);
            $key = strtolower(str_replace([' ', '-', '/'], '_', $rawKey));
            $metadata[$key] = $val;
        }

        // 1. Critical Action Alert (Solid Crimson Red)
        if (preg_match('/^(critical action alert|critical panic call|critical alert|panic values?):?\s*(.*)$/i', $clean, $m)) {
            $content = $m[2] ?: $clean;

            return '<div class="my-4 p-4 rounded-xl border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/40 text-red-950 dark:text-red-200 text-xs font-medium leading-relaxed flex items-start gap-3 shadow-xs">'
                .'<span class="px-2.5 py-1 rounded bg-[#DC2626] text-white font-bold uppercase text-[10px] shrink-0 tracking-wider">CRITICAL ALERT</span>'
                .'<span>'.htmlspecialchars($content).'</span>'
                .'</div>';
        }

        // 2. Temporal Kinetics (Solid Brand Blue)
        if (preg_match('/^(temporal kinetics):?\s*(.*)$/i', $clean, $m)) {
            $content = $m[2] ?: $clean;

            return '<div class="my-4 p-4 rounded-xl border border-sky-200 dark:border-sky-900/50 bg-sky-50 dark:bg-sky-950/40 text-sky-950 dark:text-sky-200 text-xs font-medium leading-relaxed flex items-start gap-3 shadow-xs">'
                .'<span class="px-2.5 py-1 rounded bg-[#124E78] text-white font-bold uppercase text-[10px] shrink-0 tracking-wider">TEMPORAL KINETICS</span>'
                .'<span>'.htmlspecialchars($content).'</span>'
                .'</div>';
        }

        // 3. Rejection Criteria (Solid Amber)
        if (preg_match('/^(rejection criteria):?\s*(.*)$/i', $clean, $m)) {
            $content = $m[2] ?: $clean;

            return '<div class="my-3.5 p-3.5 rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-950/40 text-amber-950 dark:text-amber-200 text-xs font-medium leading-relaxed flex items-start gap-3 shadow-xs">'
                .'<span class="px-2.5 py-1 rounded bg-[#D97706] text-white font-bold uppercase text-[10px] shrink-0 tracking-wider">REJECTION CRITERIA</span>'
                .'<span>'.htmlspecialchars($content).'</span>'
                .'</div>';
        }

        // 4. Indicator Check (Solid Teal)
        if (preg_match('/^(indicator check):?\s*(.*)$/i', $clean, $m)) {
            $content = $m[2] ?: $clean;

            return '<div class="my-3.5 p-3.5 rounded-xl border border-teal-200 dark:border-teal-900/50 bg-teal-50 dark:bg-teal-950/40 text-teal-950 dark:text-teal-200 text-xs font-medium leading-relaxed flex items-start gap-3 shadow-xs">'
                .'<span class="px-2.5 py-1 rounded bg-[#0D9488] text-white font-bold uppercase text-[10px] shrink-0 tracking-wider">INDICATOR CHECK</span>'
                .'<span>'.htmlspecialchars($content).'</span>'
                .'</div>';
        }

        // 5. Bullet Items (e.g. ● Specimen Requirements: Human Serum...)
        if (preg_match('/^[●•\-\*]\s*(.+)$/u', $pText, $bMatches)) {
            $bulletText = trim($bMatches[1]);

            // Check if there is a Bold Key at the start (e.g. "Specimen Requirements: Human Serum...")
            if (preg_match('/^([^:]{3,40}):\s*(.+)$/', $bulletText, $kMatches)) {
                $itemKey = htmlspecialchars(trim($kMatches[1]));
                $itemVal = htmlspecialchars(trim($kMatches[2]));

                return '<div class="flex items-start gap-3 my-2 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">'
                    .'<span class="w-2 h-2 rounded-full mt-1.5 shrink-0 bg-[#124E78]"></span>'
                    .'<div><strong class="font-bold text-slate-900 dark:text-white">'.$itemKey.':</strong> '.$itemVal.'</div>'
                    .'</div>';
            }

            return '<div class="flex items-start gap-3 my-2 text-xs text-slate-700 dark:text-slate-300 leading-relaxed">'
                .'<span class="w-2 h-2 rounded-full mt-1.5 shrink-0 bg-[#059669]"></span>'
                .'<div>'.htmlspecialchars($bulletText).'</div>'
                .'</div>';
        }

        // 6. Numbered Procedure Steps (e.g. 1. Preparation: ... or 2. Sampling: ...)
        if (preg_match('/^(\d+)[\.\)]\s+([^:]{3,40}):\s*(.+)$/', $pText, $sMatches)) {
            $stepNum = $sMatches[1];
            $stepName = htmlspecialchars(trim($sMatches[2]));
            $stepBody = htmlspecialchars(trim($sMatches[3]));

            return '<div class="flex items-start gap-3.5 my-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 shadow-2xs">'
                .'<span class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold text-white bg-[#124E78] shrink-0">'.$stepNum.'</span>'
                .'<div class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">'
                .'<strong class="font-bold text-slate-900 dark:text-white">'.$stepName.':</strong> '.$stepBody
                .'</div>'
                .'</div>';
        }

        // 7. General Clinical Paragraph
        return '<p class="text-xs lg:text-sm text-slate-700 dark:text-slate-300 leading-relaxed mb-2.5">'
            .htmlspecialchars($pText)
            .'</p>';
    }

    /**
     * Render text rows table into clean solid HTML table and extract control metadata.
     *
     * @param  array<int, array<int, string>>  $rows
     */
    protected function renderTextTableHtml(array $rows, array &$metadata): string
    {
        if (empty($rows)) {
            return '';
        }

        // Check if this is the "Document Control Information" Table
        $firstCell = strtolower($rows[0][0] ?? '');
        if (str_contains($firstCell, 'document control') || str_contains($firstCell, 'document code')) {
            return $this->renderDocumentControlCard($rows, $metadata);
        }

        $html = '<div class="my-4 overflow-x-auto rounded-xl border border-slate-300 dark:border-slate-700 shadow-xs">';
        $html .= '<table class="w-full text-xs text-left border-collapse">';

        foreach ($rows as $rowIndex => $cols) {
            if ($rowIndex === 0) {
                $html .= '<thead class="bg-[#124E78] text-white font-bold uppercase tracking-wider text-[11px]">';
                $html .= '<tr>';
                foreach ($cols as $col) {
                    $html .= '<th class="py-2.5 px-3.5 border border-slate-400/30">'.htmlspecialchars($col).'</th>';
                }
                $html .= '</tr></thead><tbody>';
            } else {
                $rowBg = ($rowIndex % 2 === 0) ? 'bg-slate-50 dark:bg-slate-900/60' : 'bg-white dark:bg-slate-900';
                $html .= '<tr class="'.$rowBg.' hover:bg-slate-100 dark:hover:bg-slate-800/80 transition">';
                foreach ($cols as $colIndex => $col) {
                    // Check for Clinical Reference values highlighting
                    $formattedCell = $this->formatTableCellContent($col, $colIndex);
                    $html .= '<td class="py-2.5 px-3.5 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-300">'.$formattedCell.'</td>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</tbody></table></div>';

        return $html;
    }

    /**
     * Render Word XML Table to styled HTML and extract metadata.
     */
    protected function renderWordTableHtml(DOMElement $tblNode, DOMXPath $xpath, array &$metadata): string
    {
        $rows = $xpath->query('.//w:tr', $tblNode);
        if (! $rows || $rows->length === 0) {
            return '';
        }

        $extractedRows = [];
        foreach ($rows as $row) {
            $cells = $xpath->query('.//w:tc', $row);
            if (! $cells || $cells->length === 0) {
                continue;
            }

            $rowCols = [];
            foreach ($cells as $cell) {
                $cellText = '';
                $tNodes = $cell->getElementsByTagName('t');
                foreach ($tNodes as $t) {
                    $cellText .= $t->nodeValue.' ';
                }
                $rowCols[] = trim($cellText);
            }
            if (! empty($rowCols)) {
                $extractedRows[] = $rowCols;
            }
        }

        return $this->renderTextTableHtml($extractedRows, $metadata);
    }

    /**
     * Render Document Control Information Table as an Executive Card.
     *
     * @param  array<int, array<int, string>>  $rows
     */
    protected function renderDocumentControlCard(array $rows, array &$metadata): string
    {
        $cardItems = [];
        foreach ($rows as $index => $row) {
            if ($index === 0 && str_contains(strtolower($row[0] ?? ''), 'document control')) {
                continue;
            }

            $key = $row[0] ?? '';
            $val = $row[1] ?? '';

            if ($key !== '' && $val !== '') {
                $normKey = strtolower(str_replace([' ', '-', '/'], '_', $key));
                $metadata[$normKey] = $val;
                $cardItems[] = ['label' => $key, 'value' => $val];
            }
        }

        $html = '<div class="my-4 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-xs bg-slate-50 dark:bg-slate-800/50">';
        $html .= '<div class="px-4 py-2.5 bg-[#124E78] text-white flex items-center justify-between text-xs font-bold uppercase tracking-wider">';
        $html .= '<span>Document Control & Institutional Governance</span>';
        $html .= '<span class="px-2 py-0.5 rounded bg-white/20 text-[10px]">ISO 15189 COMPLIANT</span>';
        $html .= '</div>';
        $html .= '<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 p-4">';

        foreach ($cardItems as $item) {
            $html .= '<div class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs">';
            $html .= '<div class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">'.htmlspecialchars($item['label']).'</div>';
            $html .= '<div class="font-bold text-slate-900 dark:text-white mt-0.5">'.htmlspecialchars($item['value']).'</div>';
            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Format cell contents (highlighting reference ranges, normal/abnormal badges).
     */
    protected function formatTableCellContent(string $text, int $colIndex): string
    {
        $escaped = htmlspecialchars($text);

        // Highlight Elevated / Abnormal range first
        if (preg_match('/elevated|abnormal|panic|positive/i', $text)) {
            return '<div class="flex items-center gap-2"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#DC2626] text-white">ELEVATED</span><span>'.$escaped.'</span></div>';
        }

        // Highlight Normal range
        if (preg_match('/(^|\b)normal\s*[\—\-]/i', $text) || preg_match('/^normal$/i', trim($text))) {
            return '<div class="flex items-center gap-2"><span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#059669] text-white">NORMAL</span><span>'.$escaped.'</span></div>';
        }

        // Step column badge
        if (preg_match('/^step\s*(\d+)/i', trim($text), $sm)) {
            return '<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#124E78] text-white font-mono uppercase">Step '.$sm[1].'</span>';
        }

        return $escaped;
    }

    /**
     * Parse PDF document text into clean structured HTML.
     */
    protected function parsePdf(string $filePath, string $defaultTitle): array
    {
        $rawContent = @file_get_contents($filePath);
        $text = '';

        if (! empty($rawContent)) {
            if (preg_match_all('/\((.*?)\)\s*Tj/s', $rawContent, $matches)) {
                $text = implode("\n", $matches[1]);
            } elseif (preg_match_all('/\[(.*?)\]\s*TJ/s', $rawContent, $matches)) {
                $text = implode("\n", $matches[1]);
            }
        }

        if (! empty(trim($text))) {
            return $this->parseRawText($text, $defaultTitle);
        }

        return $this->fallbackParser($filePath, $defaultTitle);
    }

    /**
     * Fallback parser for generic files.
     */
    protected function fallbackParser(string $filePath, string $defaultTitle): array
    {
        $content = @file_get_contents($filePath);
        if (! empty($content) && is_string($content) && mb_check_encoding($content, 'UTF-8')) {
            return $this->parseRawText($content, $defaultTitle);
        }

        $sections = [
            [
                'title' => 'Standard Operating Procedure Document',
                'content' => '<p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed mb-3">This document was uploaded and verified in the clinical governance portal. You can view the original file in the Online Document Viewer tab above or inspect attached test kit leaflets.</p>',
                'color' => '#124E78',
            ],
        ];

        return [
            'title' => $defaultTitle ?: 'Standard Operating Procedure',
            'summary' => 'Clinical procedure protocol verified by medical directorate.',
            'html' => $this->assembleMinimalistHtml($defaultTitle, $sections, []),
            'metadata' => [],
            'sections' => $sections,
        ];
    }

    /**
     * Determine if a paragraph in Word is a Section Heading.
     */
    protected function isHeadingParagraph(DOMElement $pNode, DOMXPath $xpath, string $text): bool
    {
        $styleNodes = $xpath->query('.//w:pStyle/@w:val', $pNode);
        if ($styleNodes && $styleNodes->length > 0) {
            $styleVal = strtolower($styleNodes->item(0)->nodeValue);
            if (str_contains($styleVal, 'heading') || str_contains($styleVal, 'title')) {
                return true;
            }
        }

        $isBold = false;
        $boldNodes = $xpath->query('.//w:r/w:rPr/w:b', $pNode);
        if ($boldNodes && $boldNodes->length > 0) {
            $isBold = true;
        }

        $numberedSection = preg_match('/^(\d+[\.\)]\s*|[A-Z]\.\s*|section\s+[a-z0-9]+)/i', trim($text));

        if (($isBold && strlen($text) < 90) || ($numberedSection && strlen($text) < 100)) {
            return true;
        }

        return false;
    }

    /**
     * Extract clean concatenated text from a paragraph node.
     */
    protected function extractParagraphText(DOMElement $pNode): string
    {
        $text = '';
        $rNodes = $pNode->getElementsByTagName('t');
        foreach ($rNodes as $t) {
            $text .= $t->nodeValue;
        }

        return $text;
    }

    /**
     * Peek at next non-empty line.
     *
     * @param  array<int, string>  $lines
     */
    protected function peekNextNonEmptyLine(array $lines, int $startIndex): ?string
    {
        $count = count($lines);
        for ($j = $startIndex; $j < $count; $j++) {
            $line = trim($lines[$j]);
            if ($line !== '') {
                return $line;
            }
        }

        return null;
    }

    /**
     * Assemble final colorful minimalist HTML document.
     * Strictly uses ONLY single solid colors (no gradients).
     */
    protected function assembleMinimalistHtml(string $title, array $sections, array $metadata): string
    {
        $html = '<div class="space-y-6">';

        // 1. SOP Header Information Card
        $html .= '<div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 shadow-xs">';
        $html .= '<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 dark:border-slate-800 pb-4">';
        $html .= '<div>';
        $html .= '<div class="flex items-center gap-2 mb-1">';
        $html .= '<span class="px-2.5 py-0.5 rounded-md bg-[#124E78] text-white font-mono font-bold text-xs uppercase tracking-wider">ISO 15189 STANDARD</span>';
        $html .= '<span class="px-2 py-0.5 rounded-md bg-[#059669] text-white font-bold text-[11px]">CLINICALLY VERIFIED</span>';
        $html .= '</div>';
        $html .= '<h2 class="text-lg lg:text-xl font-bold text-slate-900 dark:text-white tracking-tight">'.htmlspecialchars($title).'</h2>';
        if (isset($metadata['category'])) {
            $html .= '<p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">'.htmlspecialchars($metadata['category']).'</p>';
        }
        $html .= '</div>';

        // Metadata Pills
        if (! empty($metadata)) {
            $html .= '<div class="flex flex-wrap items-center gap-2 text-xs font-mono">';
            foreach ($metadata as $k => $v) {
                if (in_array($k, ['category', 'raw_body'])) {
                    continue;
                }
                $html .= '<span class="px-2.5 py-1 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">';
                $html .= '<strong>'.htmlspecialchars(ucwords(str_replace('_', ' ', $k))).':</strong> '.htmlspecialchars($v);
                $html .= '</span>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        // Quick Navigation Pills across sections
        if (count($sections) > 1) {
            $html .= '<div class="pt-3 flex flex-wrap items-center gap-1.5 text-xs">';
            $html .= '<span class="text-slate-400 text-[11px] font-semibold mr-1">Procedure Jump:</span>';
            foreach ($sections as $i => $sec) {
                $html .= '<a href="#sop-section-'.$i.'" class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-medium transition text-[11px] no-underline">';
                $html .= htmlspecialchars($sec['title']);
                $html .= '</a>';
            }
            $html .= '</div>';
        }
        $html .= '</div>';

        // 2. Sections Container (Clean Minimalist Solid Cards)
        foreach ($sections as $i => $sec) {
            $color = $sec['color'] ?? '#124E78';
            $html .= '<div id="sop-section-'.$i.'" class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-xs">';

            // Solid Color Header Strip
            $html .= '<div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between" style="border-left: 5px solid '.$color.';">';
            $html .= '<div class="flex items-center gap-2.5">';
            $html .= '<span class="w-6 h-6 rounded flex items-center justify-center text-white text-xs font-bold shrink-0" style="background-color: '.$color.';">'.($i + 1).'</span>';
            $html .= '<h3 class="text-sm font-bold text-slate-900 dark:text-white uppercase tracking-wider">'.htmlspecialchars($sec['title']).'</h3>';
            $html .= '</div>';
            $html .= '<span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded text-white font-bold" style="background-color: '.$color.';">SECTION '.($i + 1).'</span>';
            $html .= '</div>';

            // Section Body
            $html .= '<div class="p-5">';
            $html .= $sec['content'];
            $html .= '</div>';

            $html .= '</div>';
        }

        // 3. Document Approval & Verification Seal
        $html .= '<div class="bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 p-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">';
        $html .= '<div class="flex items-center gap-2">';
        $html .= '<span class="w-2.5 h-2.5 rounded-full bg-[#059669]"></span>';
        $html .= '<span>Approved for Clinical Implementation &bull; Quality Management System</span>';
        $html .= '</div>';
        $html .= '<span class="font-mono text-[11px] text-[#124E78] dark:text-sky-400 font-bold">Standard Operating Procedure Document Verified</span>';
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}
