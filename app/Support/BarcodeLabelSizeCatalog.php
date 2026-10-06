<?php

namespace App\Support;

/**
 * Label sizes aligned with Microsoft Word / Avery-style A4 sheet templates.
 */
class BarcodeLabelSizeCatalog
{
    public const DEFAULT_SHEET_KEY = 'word_30_a4';

    public const DEFAULT_DENSITY_KEY = 'medium';

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function sheetPresets(): array
    {
        return [
            'word_65_a4' => [
                'label' => 'Word A4 — 65 labels (38.1 × 21.2 mm)',
                'width_mm' => 38.1,
                'height_mm' => 21.2,
                'per_sheet' => 65,
                'font_item_pt' => 6,
                'font_code_pt' => 5,
                'barcode_max_height_mm' => 8,
            ],
            'word_30_a4' => [
                'label' => 'Word A4 — 30 labels (38.1 × 25.4 mm) — Recommended',
                'width_mm' => 38.1,
                'height_mm' => 25.4,
                'per_sheet' => 30,
                'font_item_pt' => 7,
                'font_code_pt' => 6,
                'barcode_max_height_mm' => 10,
            ],
            'word_40_a4' => [
                'label' => 'Word A4 — 40 labels (45.7 × 25.4 mm)',
                'width_mm' => 45.7,
                'height_mm' => 25.4,
                'per_sheet' => 40,
                'font_item_pt' => 7,
                'font_code_pt' => 6,
                'barcode_max_height_mm' => 11,
            ],
            'word_24_a4' => [
                'label' => 'Word A4 — 24 labels (63.5 × 33.9 mm)',
                'width_mm' => 63.5,
                'height_mm' => 33.9,
                'per_sheet' => 24,
                'font_item_pt' => 8,
                'font_code_pt' => 7,
                'barcode_max_height_mm' => 14,
            ],
            'word_21_a4' => [
                'label' => 'Word A4 — 21 labels (63.5 × 38.1 mm)',
                'width_mm' => 63.5,
                'height_mm' => 38.1,
                'per_sheet' => 21,
                'font_item_pt' => 8,
                'font_code_pt' => 7,
                'barcode_max_height_mm' => 16,
            ],
            'word_18_a4' => [
                'label' => 'Word A4 — 18 labels (63.5 × 46.6 mm)',
                'width_mm' => 63.5,
                'height_mm' => 46.6,
                'per_sheet' => 18,
                'font_item_pt' => 9,
                'font_code_pt' => 7,
                'barcode_max_height_mm' => 18,
            ],
            'word_16_a4' => [
                'label' => 'Word A4 — 16 labels (99.1 × 33.9 mm)',
                'width_mm' => 99.1,
                'height_mm' => 33.9,
                'per_sheet' => 16,
                'font_item_pt' => 9,
                'font_code_pt' => 8,
                'barcode_max_height_mm' => 14,
            ],
            'word_14_a4' => [
                'label' => 'Word A4 — 14 labels (99.1 × 38.1 mm)',
                'width_mm' => 99.1,
                'height_mm' => 38.1,
                'per_sheet' => 14,
                'font_item_pt' => 9,
                'font_code_pt' => 8,
                'barcode_max_height_mm' => 16,
            ],
            'word_10_a4' => [
                'label' => 'Word A4 — 10 labels (99.1 × 57 mm)',
                'width_mm' => 99.1,
                'height_mm' => 57,
                'per_sheet' => 10,
                'font_item_pt' => 10,
                'font_code_pt' => 8,
                'barcode_max_height_mm' => 22,
            ],
            'word_8_a4' => [
                'label' => 'Word A4 — 8 labels (99.1 × 67.7 mm)',
                'width_mm' => 99.1,
                'height_mm' => 67.7,
                'per_sheet' => 8,
                'font_item_pt' => 11,
                'font_code_pt' => 9,
                'barcode_max_height_mm' => 26,
            ],
            'word_4_a4' => [
                'label' => 'Word A4 — 4 labels (99.1 × 139 mm)',
                'width_mm' => 99.1,
                'height_mm' => 139,
                'per_sheet' => 4,
                'font_item_pt' => 12,
                'font_code_pt' => 10,
                'barcode_max_height_mm' => 45,
            ],
        ];
    }

    /**
     * Bar width factor & PNG height for Picqer CODE128.
     *
     * @return array<string, array{label: string, width_factor: int, height_px: int}>
     */
    public static function densityPresets(): array
    {
        return [
            'small' => ['label' => 'Small bars', 'width_factor' => 1, 'height_px' => 45],
            'medium' => ['label' => 'Medium bars (default)', 'width_factor' => 2, 'height_px' => 60],
            'large' => ['label' => 'Large bars', 'width_factor' => 3, 'height_px' => 75],
            'xlarge' => ['label' => 'Extra large bars', 'width_factor' => 4, 'height_px' => 90],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function resolveSheet(string $key): array
    {
        $presets = self::sheetPresets();

        return $presets[$key] ?? $presets[self::DEFAULT_SHEET_KEY];
    }

    /**
     * @return array{width_factor: int, height_px: int, label: string}
     */
    public static function resolveDensity(string $key): array
    {
        $presets = self::densityPresets();

        return $presets[$key] ?? $presets[self::DEFAULT_DENSITY_KEY];
    }

    /**
     * Inline CSS variables for label layout.
     */
    public static function cssVariables(string $sheetKey): string
    {
        $sheet = self::resolveSheet($sheetKey);

        return sprintf(
            '--label-w:%1$smm;--label-h:%2$smm;--font-item:%3$spt;--font-code:%4$spt;--barcode-max-h:%5$smm;',
            $sheet['width_mm'],
            $sheet['height_mm'],
            $sheet['font_item_pt'],
            $sheet['font_code_pt'],
            $sheet['barcode_max_height_mm']
        );
    }
}
