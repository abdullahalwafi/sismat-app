<?php

namespace App\Support;

use Illuminate\Support\Collection;

class ReportExport
{
    public function pdf(Collection $groups, string $period): string
    {
        $lines = ['SISMAT - LAPORAN ASET', 'RS UMMI', 'Periode perolehan: '.$period, '', 'Kelompok | Jumlah | Nilai perolehan'];
        $total = 0;
        $value = 0;
        foreach ($groups as $name => $assets) {
            $lines[] = $name.' | '.$assets->count().' aset | Rp '.number_format($assets->sum('price'), 0, ',', '.');
            $total += $assets->count();
            $value += $assets->sum('price');
        }
        $lines[] = '';
        $lines[] = 'TOTAL: '.$total.' aset | Rp '.number_format($value, 0, ',', '.');
        $lines[] = 'Dibuat: '.now()->timezone('Asia/Jakarta')->format('d/m/Y H:i').' WIB';
        $pages = array_chunk($lines, 44);
        $objects = [1 => '<< /Type /Catalog /Pages 2 0 R >>', 3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
        $kids = [];
        foreach ($pages as $index => $pageLines) {
            $pageId = 4 + $index * 2;
            $contentId = $pageId + 1;
            $kids[] = $pageId.' 0 R';
            $stream = "BT /F1 11 Tf 16 TL 40 790 Td\n";
            foreach (array_merge($pageLines, ['', 'Halaman '.($index + 1).' / '.count($pages)]) as $line) {
                $text = iconv('UTF-8', 'Windows-1252//TRANSLIT', $line);
                $text = str_replace(['\\', '(', ')', "\r", "\n"], ['\\\\', '\\(', '\\)', ' ', ' '], $text === false ? '' : $text);
                $stream .= '('.$text.") Tj T*\n";
            }
            $stream .= 'ET';
            $objects[$pageId] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 3 0 R >> >> /Contents '.$contentId.' 0 R >>';
            $objects[$contentId] = '<< /Length '.strlen($stream)." >>\nstream\n".$stream."\nendstream";
        }
        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $kids).'] /Count '.count($pages).' >>';
        ksort($objects);
        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $id => $content) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id." 0 obj\n".$content."\nendobj\n";
        }
        $xref = strlen($pdf);
        $pdf .= 'xref'."\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($objects as $id => $content) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        return $pdf.'trailer << /Size '.(count($objects) + 1).' /Root 1 0 R >>'."\nstartxref\n".$xref."\n%%EOF";
    }

    public function spreadsheetCell(string $value): string
    {
        return preg_match('/^[=+\-@\t\r\n]/', $value) ? "'".$value : $value;
    }
}
