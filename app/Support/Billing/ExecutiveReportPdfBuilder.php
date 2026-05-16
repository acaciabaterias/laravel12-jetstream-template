<?php

declare(strict_types=1);

namespace App\Support\Billing;

class ExecutiveReportPdfBuilder
{
    /**
     * @param  array<int, string>  $lines
     */
    public function build(array $lines): string
    {
        $sanitizedLines = collect($lines)
            ->flatMap(fn (string $line): array => explode("\n", wordwrap($this->sanitize($line), 92)))
            ->take(45)
            ->values()
            ->all();

        $content = "BT\n/F1 11 Tf\n14 TL\n50 790 Td\n";
        foreach ($sanitizedLines as $index => $line) {
            $content .= ($index === 0 ? '' : "T*\n").'('.$this->escapeText($line).") Tj\n";
        }
        $content .= 'ET';

        return $this->assemblePdf([
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
            '<< /Length '.strlen($content)." >>\nstream\n".$content."\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ]);
    }

    private function sanitize(string $value): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return preg_replace('/[^\x20-\x7E]/', '?', $ascii !== false ? $ascii : $value) ?? '';
    }

    private function escapeText(string $value): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $value);
    }

    /**
     * @param  array<int, string>  $objects
     */
    private function assemblePdf(array $objects): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [];

        foreach ($objects as $index => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $index + 1, $object);
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";

        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n".$xrefOffset."\n%%EOF";

        return $pdf;
    }
}
