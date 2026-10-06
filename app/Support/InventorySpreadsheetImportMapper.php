<?php

namespace App\Support;

use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;

class InventorySpreadsheetImportMapper
{
    /** @var array<string, array<int, string>> */
    private array $fieldAliases = [
        'item_name' => [
            'item_name', 'item', 'product_name', 'product', 'device_name', 'name_of_it_computer_machineries_equipments',
            'name_of_it/computer_machineries_&_equipments', 'it/computer_machineries_&_equipments',
        ],
        'category' => ['category'],
        'description' => [
            'description', 'product_description', 'product_details', 'details/specification',
            'it/computer_machineries_&_equipments_details/specification',
            'it_computer_machineries_equipments_details_specification',
        ],
        'serial_no' => ['serial_no', 'serial_no.', 'serial_number', 'unique_id', 'tag_no', 'tag_no.'],
        'condition' => ['condition', 'device_status', 'used_status', 'persent_condition', 'present_condition'],
        'quantity' => ['quantity', 'qty'],
        'unit_size' => ['unit', 'unit_size'],
        'price' => ['price', 'cost_value_/_purchase_value', 'cost_value_purchase_value', 'purchase_value', 'cost_value'],
        'brand' => ['brand', 'manufacturer', 'manufacturer_name,_manufacturing_year,_cuntry_of_origin,_product_details'],
        'asset_code' => ['device_name'],
        'supplier' => ['supplier'],
        'warranty' => ['warranty', 'service_warranty'],
        'set_uniq_no' => ['set_uniq_no', 'unique_id', 'tag_no', 'tag_no.'],
        'remarks' => ['remarks', 'remark'],
        'employee_name' => ['employee_name', 'from_employee_name', 'to_employee_name', 'user', 'employee_name_&_id'],
        'employee_id' => ['employee_id', 'from_employee_id', 'to_employee_id'],
    ];

    /**
     * @return array{
     *   rows: array<int, array<string, mixed>>,
     *   warnings: array<int, string>,
     *   detected_format: string,
     *   header_row_index: int|null,
     *   column_map: array<string, int|null>
     * }
     */
    public function parseFile(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $matrix = $sheet->toArray('', false, false, false);

        if (empty($matrix)) {
            return [
                'rows' => [],
                'warnings' => ['File is empty.'],
                'detected_format' => 'empty',
                'header_row_index' => null,
                'column_map' => [],
            ];
        }

        $wide = $this->tryParseWideAssetMatrix($matrix);
        if (!empty($wide['rows'])) {
            return $wide;
        }

        $headerRowIndex = $this->detectHeaderRowIndex($matrix);
        if ($headerRowIndex === null) {
            return [
                'rows' => [],
                'warnings' => ['Could not detect header row. Please use standard template or check file layout.'],
                'detected_format' => 'unknown',
                'header_row_index' => null,
                'column_map' => [],
            ];
        }

        $header = $this->normalizeHeaderRow($matrix[$headerRowIndex] ?? []);
        $columnMap = $this->buildColumnMap($header);
        $rows = [];
        $warnings = [];

        for ($i = $headerRowIndex + 1; $i < count($matrix); $i++) {
            $raw = $matrix[$i] ?? [];
            if (!is_array($raw)) {
                continue;
            }

            $mapped = $this->mapRow($raw, $columnMap);
            if ($this->looksLikeColumnHeaderLabel((string) ($mapped['item_name'] ?? ''))) {
                continue;
            }

            if (!$this->rowHasData($mapped)) {
                continue;
            }

            $mapped = $this->enrichMappedRow($mapped);
            $rows[] = $mapped;
        }

        if (empty($rows)) {
            $warnings[] = 'Header detected but no usable data rows found.';
        }

        return [
            'rows' => $rows,
            'warnings' => $warnings,
            'detected_format' => 'branch_inventory',
            'header_row_index' => $headerRowIndex + 1,
            'column_map' => $columnMap,
        ];
    }

    private function detectHeaderRowIndex(array $matrix): ?int
    {
        $bestIndex = null;
        $bestScore = 0;
        $limit = min(count($matrix), 40);

        for ($i = 0; $i < $limit; $i++) {
            $cells = array_map(fn ($v) => $this->normalizeKey((string) $v), $matrix[$i] ?? []);
            $nonEmpty = array_filter($cells, fn ($v) => $v !== '');
            if (count($nonEmpty) < 3) {
                continue;
            }

            $score = 0;
            foreach ($cells as $cell) {
                if ($cell === '') {
                    continue;
                }
                if (str_contains($cell, 'product') || str_contains($cell, 'item') || str_contains($cell, 'serial')) {
                    $score += 3;
                }
                if (str_contains($cell, 'employee') || str_contains($cell, 'branch') || str_contains($cell, 'quantity')) {
                    $score += 2;
                }
                if (str_contains($cell, 'condition') || str_contains($cell, 'status') || str_contains($cell, 'description')) {
                    $score += 2;
                }
                if ($cell === 'sl' || $cell === 'sl_no' || str_starts_with($cell, 'sl.')) {
                    $score += 1;
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestIndex = $i;
            }
        }

        return $bestScore >= 4 ? $bestIndex : null;
    }

    /**
     * @return array{rows: array, warnings: array, detected_format: string, header_row_index: int|null, column_map: array}
     */
    private function tryParseWideAssetMatrix(array $matrix): array
    {
        $groupHeaderIndex = null;
        $subHeaderIndex = null;

        for ($i = 0; $i < min(count($matrix), 25); $i++) {
            $line = implode(' ', array_map(fn ($v) => strtolower(trim((string) $v)), $matrix[$i] ?? []));
            if ($groupHeaderIndex === null && str_contains($line, 'laptop') && str_contains($line, 'monitor')) {
                $groupHeaderIndex = $i;
                continue;
            }
            if ($groupHeaderIndex !== null && $subHeaderIndex === null) {
                $cells = array_map(fn ($v) => strtolower(trim((string) $v)), $matrix[$i] ?? []);
                $brandCount = count(array_filter($cells, fn ($c) => str_contains($c, 'brand')));
                $serialCount = count(array_filter($cells, fn ($c) => str_contains($c, 'serial')));
                if ($brandCount >= 2 && $serialCount >= 2) {
                    $subHeaderIndex = $i;
                    break;
                }
            }
        }

        if ($groupHeaderIndex === null || $subHeaderIndex === null) {
            return ['rows' => [], 'warnings' => [], 'detected_format' => '', 'header_row_index' => null, 'column_map' => []];
        }

        $groupHeaders = $matrix[$groupHeaderIndex];
        $subHeaders = $matrix[$subHeaderIndex];
        $groups = $this->detectWideGroups($groupHeaders, $subHeaders);
        if (empty($groups)) {
            return ['rows' => [], 'warnings' => [], 'detected_format' => '', 'header_row_index' => null, 'column_map' => []];
        }

        $rows = [];
        for ($r = $subHeaderIndex + 1; $r < count($matrix); $r++) {
            $line = $matrix[$r] ?? [];
            $employeeCell = trim((string) ($line[1] ?? ''));
            if ($employeeCell === '') {
                continue;
            }

            foreach ($groups as $group) {
                $brand = trim((string) ($line[$group['brand_col']] ?? ''));
                $serial = trim((string) ($line[$group['serial_col']] ?? ''));
                if ($this->isEmptyValue($brand) && $this->isEmptyValue($serial)) {
                    continue;
                }

                $itemName = $group['item_name'];
                if (!$this->isEmptyValue($brand) && $this->isEmptyValue($serial)) {
                    $description = 'Brand: ' . $brand;
                } elseif ($this->isEmptyValue($brand) && !$this->isEmptyValue($serial)) {
                    $description = 'Serial: ' . $serial;
                } else {
                    $description = 'Brand: ' . $brand;
                }

                $rows[] = $this->enrichMappedRow([
                    'item_name' => $itemName,
                    'category' => 'IT Equipment',
                    'brand' => $this->isEmptyValue($brand) ? '' : $brand,
                    'supplier' => '',
                    'serial_no' => $this->isEmptyValue($serial) ? '' : $serial,
                    'description' => $description . ' | ' . $employeeCell,
                    'condition' => 'good',
                    'quantity' => '1',
                    'price' => '0',
                    'unit_size' => 'NOS',
                    'set_uniq_no' => '',
                    'ram' => '',
                    'storage' => '',
                    'processor' => '',
                ]);
            }
        }

        return [
            'rows' => $rows,
            'warnings' => empty($rows) ? ['Wide asset sheet detected but no rows extracted.'] : ['Detected wide asset matrix format and expanded rows automatically.'],
            'detected_format' => 'wide_asset_matrix',
            'header_row_index' => $groupHeaderIndex + 1,
            'column_map' => ['format' => 'wide_asset_matrix'],
        ];
    }

    private function detectWideGroups(array $groupHeaders, array $subHeaders): array
    {
        $groups = [];
        $currentItem = '';
        $max = max(count($groupHeaders), count($subHeaders));

        for ($c = 0; $c < $max; $c++) {
            $groupTitle = trim((string) ($groupHeaders[$c] ?? ''));
            if ($groupTitle !== '') {
                $currentItem = preg_replace('/\s*\(\d+\)\s*$/', '', $groupTitle) ?? $groupTitle;
            }

            $sub = strtolower(trim((string) ($subHeaders[$c] ?? '')));
            if ($currentItem === '' || $sub === '') {
                continue;
            }

            if (str_contains($sub, 'brand')) {
                $groups[] = [
                    'item_name' => $this->normalizeItemName($currentItem),
                    'brand_col' => $c,
                    'serial_col' => $c + 1,
                ];
            }
        }

        return $groups;
    }

    private function normalizeHeaderRow(array $row): array
    {
        $header = [];
        foreach ($row as $idx => $value) {
            $header[$idx] = $this->normalizeKey((string) $value);
        }

        return $header;
    }

    private function buildColumnMap(array $header): array
    {
        $map = [];
        foreach (array_keys($this->fieldAliases) as $field) {
            $map[$field] = null;
        }

        foreach ($header as $idx => $col) {
            if ($col === '') {
                continue;
            }
            foreach ($this->fieldAliases as $field => $aliases) {
                if ($map[$field] !== null) {
                    continue;
                }
                if (in_array($col, $aliases, true)) {
                    $map[$field] = (int) $idx;
                    continue;
                }
                foreach ($aliases as $alias) {
                    if ($alias === '' || strlen($alias) < 4) {
                        continue;
                    }
                    if ($field === 'category' && !str_contains($col, 'category')) {
                        continue;
                    }
                    if ($field === 'employee_id' && !(str_contains($col, 'employee') && str_contains($col, 'id'))) {
                        continue;
                    }
                    if ($field === 'employee_name' && !str_contains($col, 'employee') && $col !== 'user') {
                        continue;
                    }
                    if ($field === 'item_name' && str_contains($col, 'employee') && !str_contains($col, 'equipment')) {
                        continue;
                    }
                    // Match only when the header contains the alias (never alias contains header —
                    // e.g. alias "category" wrongly matched column "item").
                    if ($col === $alias || str_contains($col, $alias)) {
                        $map[$field] = (int) $idx;
                        break;
                    }
                }
            }
        }

        if ($map['category'] !== null && $map['category'] === $map['item_name']) {
            $map['category'] = null;
        }
        if ($map['category'] !== null && in_array($map['category'], [$map['employee_id'], $map['serial_no'], $map['employee_name']], true)) {
            $map['category'] = null;
        }

        // Common branch templates
        if ($map['item_name'] === null) {
            foreach ($header as $idx => $col) {
                if (str_contains($col, 'product') && str_contains($col, 'name')) {
                    $map['item_name'] = (int) $idx;
                    break;
                }
            }
        }

        return $map;
    }

    private function mapRow(array $rawRow, array $columnMap): array
    {
        $pick = function (string $field) use ($rawRow, $columnMap): string {
            $idx = $columnMap[$field] ?? null;
            if ($idx === null) {
                return '';
            }

            return trim((string) ($rawRow[$idx] ?? ''));
        };

        $itemName = $pick('item_name');
        if ($itemName === '') {
            $deviceName = $pick('brand');
            if ($deviceName !== '' && !str_contains(strtolower($deviceName), 'n/a')) {
                // Some sheets use "Device Name" as model; keep brand empty if used as fallback name.
            }
        }

        $setUniqNo = $pick('set_uniq_no');
        $assetCode = $pick('asset_code');
        if (($setUniqNo === '' || $setUniqNo === '-') && $assetCode !== '' && !$this->isEmptyValue($assetCode)) {
            $setUniqNo = $assetCode;
        }

        $mapped = [
            'item_name' => $itemName !== '' ? $itemName : $pick('brand'),
            'category' => $pick('category'),
            'brand' => $pick('brand'),
            'supplier' => $pick('supplier'),
            'price' => $pick('price'),
            'quantity' => $pick('quantity'),
            'unit_size' => $pick('unit_size'),
            'serial_no' => $pick('serial_no'),
            'condition' => $pick('condition'),
            'warranty' => $pick('warranty'),
            'service_warranty' => '',
            'description' => $pick('description'),
            'set_uniq_no' => $setUniqNo,
            'remarks' => $pick('remarks'),
            'employee_name' => $pick('employee_name'),
            'employee_id' => $pick('employee_id'),
            'ram' => '',
            'storage' => '',
            'processor' => '',
        ];

        if ($mapped['description'] === '' && $mapped['remarks'] !== '') {
            $mapped['description'] = $mapped['remarks'];
        }

        return $this->reconcileMisplacedFields($mapped);
    }

    private function enrichMappedRow(array $row): array
    {
        $row = $this->reconcileMisplacedFields($row);
        $row['item_name'] = $this->normalizeItemName((string) ($row['item_name'] ?? ''));
        if ($row['item_name'] === '') {
            return $row;
        }

        $row['category'] = $this->defaultBranchImportCategory();

        if (trim((string) ($row['quantity'] ?? '')) === '') {
            $row['quantity'] = '1';
        }
        if (trim((string) ($row['price'] ?? '')) === '') {
            $row['price'] = '0';
        }
        if (trim((string) ($row['unit_size'] ?? '')) === '') {
            $row['unit_size'] = 'NOS';
        }

        $row['condition'] = $this->normalizeCondition((string) ($row['condition'] ?? ''));

        $specs = $this->parseSpecsFromText((string) ($row['description'] ?? ''));
        $row['ram'] = trim((string) ($row['ram'] ?? ''));
        $row['storage'] = trim((string) ($row['storage'] ?? ''));
        $row['processor'] = trim((string) ($row['processor'] ?? ''));
        if ($row['ram'] === '' && !empty($specs['ram'])) {
            $row['ram'] = $specs['ram'];
        }
        if ($row['storage'] === '' && !empty($specs['storage'])) {
            $row['storage'] = $specs['storage'];
        }
        if ($row['processor'] === '' && !empty($specs['processor'])) {
            $row['processor'] = $specs['processor'];
        }

        return $row;
    }

    private function rowHasData(array $row): bool
    {
        foreach (['item_name', 'serial_no', 'description', 'set_uniq_no'] as $key) {
            if (trim((string) ($row[$key] ?? '')) !== '') {
                return true;
            }
        }

        return false;
    }

    private function normalizeKey(string $value): string
    {
        $value = strtolower(trim($value));
        $value = str_replace(['/', '\\', '.', ',', '(', ')', '&', '-'], ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return Str::of($value)->trim()->replace(' ', '_')->toString();
    }

    private function normalizeItemName(string $name): string
    {
        $name = trim($name);
        if ($this->isEmptyValue($name)) {
            return '';
        }

        return $name;
    }

    private function normalizeCondition(string $condition): string
    {
        $c = strtolower(trim($condition));
        if ($c === '') {
            return 'good';
        }
        if (in_array($c, ['ok', 'good', 'used', 'not used', 'normal'], true)) {
            return $c === 'not used' ? 'unused' : ($c === 'ok' ? 'good' : $c);
        }
        if (str_contains($c, 'damage')) {
            return 'damage';
        }
        if (str_contains($c, 'bad')) {
            return 'bad';
        }

        return $c;
    }

    public function isLikelyEmployeeId(string $value): bool
    {
        $digits = preg_replace('/\D+/', '', trim($value)) ?? '';
        if (strlen($digits) < 10 || strlen($digits) > 14) {
            return false;
        }

        return (bool) preg_match('/^20\d{6,}/', $digits);
    }

    public function looksLikeColumnHeaderLabel(string $value): bool
    {
        $lower = strtolower(trim($value));
        if ($lower === '') {
            return false;
        }

        if (in_array($lower, ['brand', 'serial no', 'serial_no', 'serial no.', 'model', 'tag no', 'quantity', 'sl', 'sl no'], true)) {
            return true;
        }

        if (preg_match('/\b(serial\s*no|brand|model\s*no|tag\s*no|unique\s*id)\b/i', $value)) {
            return true;
        }

        return (bool) preg_match(
            '/^(laptop|desktop pc|desktop|monitor|keyboard|mouse|printer|ups|scanner|server|patch panel)\s+(serial\s*no|brand)\.?$/i',
            $value
        );
    }

    public function defaultBranchImportCategory(): string
    {
        return 'IT Equipment';
    }

    public function resolveCategoryLabel(string $rawCategory, string $itemName): string
    {
        return $this->defaultBranchImportCategory();
    }

    public function categoryLabelMatchesItemName(string $categoryLabel, string $itemName): bool
    {
        $categoryLabel = strtolower(trim($categoryLabel));
        $itemName = strtolower(trim($itemName));
        if ($categoryLabel === '' || $itemName === '') {
            return false;
        }

        if ($categoryLabel === $itemName) {
            return true;
        }

        $escaped = preg_quote($categoryLabel, '/');

        return (bool) preg_match('/^[\s\-–—\/|]*' . $escaped . '[\s\-–—\/|]*$/u', $itemName);
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function parseEmployeeNameAndId(string $cell): array
    {
        $cell = trim($cell);
        if ($cell === '') {
            return ['', ''];
        }

        if (preg_match('#^(.*?)[\s\-–—/|]*(\d{10,14})\s*$#u', $cell, $m)) {
            $name = trim($m[1], " \t\n\r\0\x0B,-–—/|");
            $id = trim($m[2]);

            return [$name, $id];
        }

        if ($this->isLikelyEmployeeId($cell)) {
            return ['', $cell];
        }

        return [$cell, ''];
    }

    private function reconcileMisplacedFields(array $row): array
    {
        $category = trim((string) ($row['category'] ?? ''));
        $itemName = trim((string) ($row['item_name'] ?? ''));
        $serial = trim((string) ($row['serial_no'] ?? ''));
        $employeeId = trim((string) ($row['employee_id'] ?? ''));

        if ($this->isLikelyEmployeeId($category)) {
            if ($employeeId === '') {
                $row['employee_id'] = $category;
            }
            $row['category'] = '';
        }

        if ($this->isLikelyEmployeeId($itemName)) {
            if ($employeeId === '') {
                $row['employee_id'] = $itemName;
            }
            $row['item_name'] = '';
        }

        [$empName, $empIdFromCell] = $this->parseEmployeeNameAndId((string) ($row['employee_name'] ?? ''));
        if ($empName !== '') {
            $row['employee_name'] = $empName;
        }
        if ($empIdFromCell !== '' && trim((string) ($row['employee_id'] ?? '')) === '') {
            $row['employee_id'] = $empIdFromCell;
        }

        if ($this->isLikelyEmployeeId($serial)
            && trim((string) ($row['employee_id'] ?? '')) === ''
            && !$this->isLikelyHardwareSerial($serial)
        ) {
            $row['employee_id'] = $serial;
            $row['serial_no'] = '';
        }

        return $row;
    }

    private function isLikelyHardwareSerial(string $value): bool
    {
        $value = trim($value);

        return (bool) preg_match('/[A-Za-z]/', $value);
    }

    public function inferCategoryLabelFromItemName(string $itemName): string
    {
        $n = strtolower($itemName);
        // Branch CSV: only major types get their own category; peripherals use IT Equipment.
        $map = [
            'laptop' => 'Laptop',
            'notebook' => 'Laptop',
            'desktop set' => 'Desktop PC',
            'desktop' => 'Desktop PC',
            'system unit' => 'Desktop PC',
            'cpu' => 'Desktop PC',
            'monitor' => 'Monitor',
            'printer' => 'Printer',
            'server' => 'Server',
        ];
        foreach ($map as $needle => $category) {
            if (str_contains($n, $needle)) {
                if ($this->categoryLabelMatchesItemName($category, $itemName)) {
                    return 'IT Equipment';
                }

                return $category;
            }
        }

        return 'IT Equipment';
    }

    /**
     * @return array{ram?: string, storage?: string, processor?: string}
     */
    private function parseSpecsFromText(string $text): array
    {
        $out = [];
        if ($text === '') {
            return $out;
        }

        if (preg_match('/ram[\s:-]*([0-9]+\s*gb)/i', $text, $m)) {
            $out['ram'] = strtoupper(trim($m[1]));
        }
        if (preg_match('/processor[\s:-]*([^,\n\r]+)/i', $text, $m)) {
            $proc = trim($m[1]);
            if (!preg_match('/^ram[\s:-]/i', $proc)) {
                $out['processor'] = $proc;
            }
        }
        if (preg_match('/(\d+\s*gb\s*(ssd|hdd))/i', $text, $m)) {
            $out['storage'] = strtoupper(trim($m[1]));
        }

        return $out;
    }

    private function isEmptyValue(string $value): bool
    {
        $v = strtolower(trim($value));

        return $v === '' || in_array($v, ['-', 'n/a', 'na', 'nil', 'null', 'none'], true);
    }
}
