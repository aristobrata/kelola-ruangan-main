<?php

namespace App\Libraries;

use ZipArchive;

/**
 * SimpleXlsxWriter
 * -----------------
 * Membuat file .xlsx (Office Open XML) asli tanpa dependency Composer
 * (hanya butuh ekstensi PHP `zip`, yang sudah aktif secara default di XAMPP).
 *
 * Dipakai untuk fitur "Export Laporan Excel" agar tidak perlu meng-install
 * library besar seperti PhpSpreadsheet.
 */
class SimpleXlsxWriter
{
    protected array $headers;
    protected array $rows;
    protected string $sheetTitle;
    protected array $columnWidths;

    /**
     * @param array $headers      Nama-nama kolom, mis. ['Nama', 'Tanggal', 'Status']
     * @param array $rows         Array baris, tiap baris array asosiatif/berurutan sesuai urutan header
     * @param string $sheetTitle  Nama sheet (maks 31 karakter, tanpa karakter terlarang Excel)
     * @param array $columnWidths Lebar kolom opsional, mis. [20, 15, 12]
     */
    public function __construct(array $headers, array $rows, string $sheetTitle = 'Sheet1', array $columnWidths = [])
    {
        $this->headers      = array_values($headers);
        $this->rows         = $rows;
        $this->sheetTitle   = $this->sanitizeSheetTitle($sheetTitle);
        $this->columnWidths = $columnWidths;
    }

    protected function sanitizeSheetTitle(string $title): string
    {
        $title = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $title);
        return mb_substr($title, 0, 31);
    }

    /** Konversi index kolom (0-based) menjadi huruf kolom Excel (A, B, ..., Z, AA, AB, ...) */
    protected function colLetter(int $index): string
    {
        $letter = '';
        $index++;
        while ($index > 0) {
            $mod    = ($index - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $index  = intdiv($index - $mod, 26);
        }
        return $letter;
    }

    protected function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    protected function contentTypesXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
    <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
    <Default Extension="xml" ContentType="application/xml"/>
    <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
    <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
    <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML;
    }

    protected function rootRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML;
    }

    protected function workbookXml(): string
    {
        $title = $this->escapeXml($this->sheetTitle);
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
    <sheets>
        <sheet name="{$title}" sheetId="1" r:id="rId1"/>
    </sheets>
</workbook>
XML;
    }

    protected function workbookRelsXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
    <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
    <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML;
    }

    protected function stylesXml(): string
    {
        // cellXfs index 0 = teks biasa, index 1 = header (bold, putih, background merah maroon)
        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <fonts count="2">
        <font><sz val="11"/><name val="Calibri"/></font>
        <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
    </fonts>
    <fills count="3">
        <fill><patternFill patternType="none"/></fill>
        <fill><patternFill patternType="gray125"/></fill>
        <fill><patternFill patternType="solid"><fgColor rgb="FF8B1A24"/><bgColor indexed="64"/></patternFill></fill>
    </fills>
    <borders count="1">
        <border><left/><right/><top/><bottom/><diagonal/></border>
    </borders>
    <cellStyleXfs count="1">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
    </cellStyleXfs>
    <cellXfs count="2">
        <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
        <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
    </cellXfs>
    <cellStyles count="1">
        <cellStyle name="Normal" xfId="0" builtinId="0"/>
    </cellStyles>
</styleSheet>
XML;
    }

    protected function sheetXml(): string
    {
        $colCount = count($this->headers);

        // Lebar kolom
        $cols = '<cols>';
        for ($i = 0; $i < $colCount; $i++) {
            $width = $this->columnWidths[$i] ?? 18;
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1"/>';
        }
        $cols .= '</cols>';

        $rowsXml   = '';
        $rowNumber = 1;

        // Baris header (bold, style index 1)
        $rowsXml .= '<row r="' . $rowNumber . '">';
        foreach ($this->headers as $i => $h) {
            $ref = $this->colLetter($i) . $rowNumber;
            $rowsXml .= '<c r="' . $ref . '" t="inlineStr" s="1"><is><t xml:space="preserve">' . $this->escapeXml((string) $h) . '</t></is></c>';
        }
        $rowsXml .= '</row>';
        $rowNumber++;

        // Baris data
        foreach ($this->rows as $row) {
            $rowsXml .= '<row r="' . $rowNumber . '">';
            $i = 0;
            foreach ($row as $value) {
                $ref = $this->colLetter($i) . $rowNumber;
                if (is_int($value) || is_float($value)) {
                    $rowsXml .= '<c r="' . $ref . '"><v>' . $value . '</v></c>';
                } else {
                    $rowsXml .= '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">' . $this->escapeXml((string) $value) . '</t></is></c>';
                }
                $i++;
            }
            $rowsXml .= '</row>';
            $rowNumber++;
        }

        $lastCol   = $this->colLetter(max($colCount - 1, 0));
        $dimension = 'A1:' . $lastCol . max($rowNumber - 1, 1);

        return <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
    <dimension ref="{$dimension}"/>
    <sheetViews>
        <sheetView workbookViewId="0"/>
    </sheetViews>
    <sheetFormatPr defaultRowHeight="15"/>
    {$cols}
    <sheetData>
        {$rowsXml}
    </sheetData>
    <pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>
</worksheet>
XML;
    }

    /**
     * Membuat file .xlsx dan mengirimkannya langsung sebagai file download ke browser.
     * Method ini menghentikan eksekusi (exit) setelah selesai mengirim file.
     */
    public function download(string $filename): void
    {
        if (!str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        $tmpFile = tempnam(sys_get_temp_dir(), 'xlsx_');

        $zip = new ZipArchive();
        $zip->open($tmpFile, ZipArchive::OVERWRITE);
        $zip->addEmptyDir('_rels');
        $zip->addEmptyDir('xl');
        $zip->addEmptyDir('xl/_rels');
        $zip->addEmptyDir('xl/worksheets');

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheetXml());
        $zip->close();

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($tmpFile));
        header('Cache-Control: max-age=0');

        readfile($tmpFile);
        unlink($tmpFile);
        exit;
    }
}
