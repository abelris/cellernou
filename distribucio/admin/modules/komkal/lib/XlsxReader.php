<?php
/**
 * Lightweight XLSX reader using ZipArchive + SimpleXML.
 * No external dependencies (no PhpSpreadsheet/Composer required).
 *
 * Usage:
 *   $rows = XlsxReader::read('/path/to/file.xlsx');
 *   // $rows[0] = ['A' => 'val', 'B' => 'val', ...]
 */
class XlsxReader
{
    /**
     * Read all rows from the first sheet of an XLSX file.
     *
     * @param string $filePath Absolute path to .xlsx file
     * @param bool $skipHeader Skip the first row (header)
     * @return array Array of associative arrays keyed by column letter (A, B, C, ...)
     * @throws RuntimeException on errors
     */
    public static function read(string $filePath, bool $skipHeader = true): array
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("File not found: $filePath");
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new RuntimeException("Cannot open XLSX as ZIP: $filePath");
        }

        // 1) Read shared strings (text values are stored here by index)
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $ss = new SimpleXMLElement($ssXml);
            foreach ($ss->si as $si) {
                // Handle both simple <t> and rich text <r><t>
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $text = '';
                    foreach ($si->r as $r) {
                        $text .= (string)$r->t;
                    }
                    $sharedStrings[] = $text;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }

        // 2) Read the first worksheet
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new RuntimeException("Cannot find sheet1.xml in XLSX");
        }

        $sheet = new SimpleXMLElement($sheetXml);
        $zip->close();

        // 3) Parse rows
        $rows = [];
        $isFirstRow = true;

        foreach ($sheet->sheetData->row as $row) {
            if ($skipHeader && $isFirstRow) {
                $isFirstRow = false;
                continue;
            }
            $isFirstRow = false;

            $rowData = [];
            foreach ($row->c as $cell) {
                $ref = (string)$cell['r']; // e.g. "A1", "B2", "AA10"
                $col = preg_replace('/[0-9]+/', '', $ref); // Extract column letter(s)

                $value = '';
                $type = (string)$cell['t']; // 's' = shared string, 'str' = inline string, '' = number

                if ($type === 's') {
                    $idx = (int)(string)$cell->v;
                    $value = $sharedStrings[$idx] ?? '';
                } elseif ($type === 'str' || $type === 'inlineStr') {
                    if (isset($cell->is->t)) {
                        $value = (string)$cell->is->t;
                    } else {
                        $value = (string)$cell->v;
                    }
                } else {
                    $value = (string)$cell->v;
                }

                $rowData[$col] = $value;
            }

            if (!empty($rowData)) {
                $rows[] = $rowData;
            }
        }

        return $rows;
    }

    /**
     * Parse a Komkal price string into a float or null.
     * Handles: "4,58 EUR", "4,58\xa0€", "Consultar", empty
     */
    public static function parsePrice(string $raw): ?float
    {
        $clean = trim($raw);
        if (empty($clean) || stripos($clean, 'consultar') !== false) {
            return null;
        }
        // Remove everything except digits, comma and dot
        $clean = preg_replace('/[^\d,.]/', '', $clean);
        // Spanish format: comma is decimal separator
        $clean = str_replace(',', '.', $clean);
        $val = (float)$clean;
        return $val > 0 ? $val : null;
    }

    /**
     * Clean a Komkal product name (remove trailing asterisks, excess whitespace).
     */
    public static function cleanName(string $name): string
    {
        $name = trim($name);
        $name = preg_replace('/\*+$/', '', $name); // trailing asterisks
        $name = preg_replace('/\s+/', ' ', $name); // collapse whitespace
        return trim($name);
    }
}
