<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PdfGeneratorService
{
    /**
     * Generate PDF binary content from HTML string.
     */
    public function renderHtmlToPdf(string $html): string
    {
        $browsers = $this->findBrowserBinaries();

        foreach ($browsers as $browserPath) {
            $pdf = $this->renderWithBrowser($browserPath, $html);
            if ($pdf !== null && strlen($pdf) > 1000) {
                return $pdf;
            }
        }

        // Fallback: build a clean structured PDF using pure PHP
        return $this->renderFallbackPdf($html);
    }

    /**
     * Attempt rendering via headless Chrome or Edge with isolated user-data-dir.
     */
    protected function renderWithBrowser(string $browserBinary, string $html): ?string
    {
        $tempDir = sys_get_temp_dir();
        $uniqueId = uniqid('kaafi_pdf_', true);
        $tempHtml = $tempDir.DIRECTORY_SEPARATOR.$uniqueId.'.html';
        $tempPdf = $tempDir.DIRECTORY_SEPARATOR.$uniqueId.'.pdf';
        $userDataDir = $tempDir.DIRECTORY_SEPARATOR.'k_prof_'.$uniqueId;

        @mkdir($userDataDir, 0777, true);

        try {
            file_put_contents($tempHtml, $html);

            $commonFlags = [
                '--disable-gpu',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-extensions',
                '--disable-background-networking',
                '--disable-default-apps',
                '--disable-sync',
                '--hide-scrollbars',
                '--no-first-run',
                '--no-pdf-header-footer',
                "--user-data-dir={$userDataDir}",
                "--print-to-pdf={$tempPdf}",
                $tempHtml,
            ];

            // 1. Try modern headless mode
            $args = array_merge([$browserBinary, '--headless=new'], $commonFlags);
            $process = new Process($args);
            $process->setTimeout(25);
            $process->run();

            // 2. Retry with standard legacy headless if empty
            if (! file_exists($tempPdf) || filesize($tempPdf) === 0) {
                $args[1] = '--headless';
                $retryProcess = new Process($args);
                $retryProcess->setTimeout(25);
                $retryProcess->run();
            }

            if (file_exists($tempPdf) && filesize($tempPdf) > 0) {
                $content = file_get_contents($tempPdf);
                if ($content !== false && strlen($content) > 0) {
                    return $content;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Headless browser PDF generation error: '.$e->getMessage());
        } finally {
            if (file_exists($tempHtml)) {
                @unlink($tempHtml);
            }
            if (file_exists($tempPdf)) {
                @unlink($tempPdf);
            }
            $this->removeDirectoryRecursively($userDataDir);
        }

        return null;
    }

    /**
     * Locate installed Chrome and Edge binaries across platforms.
     *
     * @return array<int, string>
     */
    public function findBrowserBinaries(): array
    {
        $candidates = [
            // Windows Chrome & Edge paths
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA').'\\Google\\Chrome\\Application\\chrome.exe' : null,
            getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA').'\\Microsoft\\Edge\\Application\\msedge.exe' : null,
            // Linux / Docker paths
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/snap/bin/chromium',
        ];

        $valid = [];
        foreach ($candidates as $path) {
            if ($path && file_exists($path)) {
                $valid[] = $path;
            }
        }

        return $valid;
    }

    /**
     * Cleanly remove temporary profile directory.
     */
    protected function removeDirectoryRecursively(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        try {
            $files = array_diff(scandir($dir) ?: [], ['.', '..']);
            foreach ($files as $file) {
                $p = $dir.DIRECTORY_SEPARATOR.$file;
                if (is_dir($p)) {
                    $this->removeDirectoryRecursively($p);
                } else {
                    @unlink($p);
                }
            }
            @rmdir($dir);
        } catch (\Throwable) {
            // Ignore non-critical cleanup errors
        }
    }

    /**
     * Lightweight pure-PHP PDF fallback generator.
     * Guaranteed to strip all <style>, <script>, and markup tags so CSS code is NEVER printed.
     */
    protected function renderFallbackPdf(string $html): string
    {
        // 1. Completely strip style and script tags and their contents
        $clean = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $clean);
        $clean = preg_replace('/<!--.*?-->/s', '', $clean);

        // 2. Format structure
        $clean = str_replace(
            ['<br>', '<br/>', '<br />', '</tr>', '</div>', '</p>', '</h1>', '</h2>', '</h3>', '</h4>'],
            "\n",
            $clean
        );
        $clean = str_replace(['</td>', '</th>'], '  |  ', $clean);

        $plainText = strip_tags($clean);
        $lines = array_filter(array_map('trim', explode("\n", $plainText)), fn ($l) => $l !== '');

        $pdfLines = [];
        $pdfLines[] = '%PDF-1.4';
        $pdfLines[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $pdfLines[] = '2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj';
        $pdfLines[] = '3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595.28 841.89] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj';

        // Build text stream
        $stream = "BT\n/F1 9 Tf\n36 800 Td\n13 TL\n";
        $lineCount = 0;
        foreach ($lines as $line) {
            if ($lineCount > 55) {
                break;
            }
            // Sanitize ASCII for raw PDF text
            $sanitized = preg_replace('/[^a-zA-Z0-9 \-_:.,\/()$%#&|]/', '', $line);
            if ($sanitized === '') {
                continue;
            }
            if (strlen($sanitized) > 95) {
                $sanitized = substr($sanitized, 0, 95).'...';
            }
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $sanitized);
            $stream .= "({$escaped}) '\n";
            $lineCount++;
        }
        $stream .= 'ET';

        $streamLength = strlen($stream);
        $pdfLines[] = "4 0 obj << /Length {$streamLength} >>\nstream\n{$stream}\nendstream\nendobj";
        $pdfLines[] = '5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        // xref table
        $body = '';
        $offsets = [0];

        foreach ($pdfLines as $obj) {
            $offsets[] = strlen($body);
            $body .= $obj."\n";
        }

        $xrefOffset = strlen($body);
        $body .= "xref\n0 ".count($offsets)."\n";
        $body .= "0000000000 65535 f \n";
        for ($i = 1; $i < count($offsets); $i++) {
            $body .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $body .= 'trailer << /Size '.count($offsets)." /Root 1 0 R >>\n";
        $body .= "startxref\n{$xrefOffset}\n%%EOF";

        return $body;
    }
}
