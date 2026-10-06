<?php

namespace App\Support;

/**
 * Build and parse "1 Year, 5 Months" style warranty strings.
 */
class WarrantyPeriodFormatter
{
    /**
     * Primary UI: separate year + month counts → "1 Year, 5 Months".
     *
     * @param mixed $years
     * @param mixed $months
     */
    public static function formatYearsMonths($years, $months): string
    {
        $y = (int) trim((string) ($years ?? ''));
        $m = (int) trim((string) ($months ?? ''));
        $parts = [];
        if ($y > 0) {
            $parts[] = $y . ' ' . ($y === 1 ? 'Year' : 'Years');
        }
        if ($m > 0) {
            $parts[] = $m . ' ' . ($m === 1 ? 'Month' : 'Months');
        }

        return implode(', ', $parts);
    }

    /**
     * @return array{years: int|string, months: int|string}
     */
    public static function parseYearsMonths(?string $value): array
    {
        $out = ['years' => '', 'months' => ''];
        if ($value === null || trim($value) === '') {
            return $out;
        }
        $v = trim($value);
        if (preg_match('/(\d+)\s*(Year|Years)\b/iu', $v, $m)) {
            $out['years'] = (int) $m[1];
        }
        if (preg_match('/(\d+)\s*(Month|Months)\b/iu', $v, $m)) {
            $out['months'] = (int) $m[1];
        }

        return $out;
    }

    /**
     * @param mixed $qty1
     * @param mixed $unit1 Year|Month
     * @param mixed $qty2
     * @param mixed $unit2
     */
    public static function format($qty1, $unit1, $qty2, $unit2): string
    {
        $parts = [];
        foreach ([[$qty1, $unit1], [$qty2, $unit2]] as [$q, $u]) {
            $n = (int) trim((string) ($q ?? ''));
            $unit = trim((string) ($u ?? ''));
            if ($n <= 0 || $unit === '') {
                continue;
            }
            $norm = strtolower($unit);
            if (! in_array($norm, ['year', 'years', 'month', 'months'], true)) {
                continue;
            }
            $isYear = str_starts_with($norm, 'year');
            if ($isYear) {
                $parts[] = $n . ' ' . ($n === 1 ? 'Year' : 'Years');
            } else {
                $parts[] = $n . ' ' . ($n === 1 ? 'Month' : 'Months');
            }
        }

        return implode(', ', $parts);
    }

    /**
     * @return array{qty1: int|string, unit1: string, qty2: int|string, unit2: string}
     * @deprecated Prefer parseYearsMonths(); kept for any legacy callers.
     */
    public static function parseToParts(?string $value): array
    {
        $ym = self::parseYearsMonths($value);
        $y = $ym['years'];
        $m = $ym['months'];
        if ($y !== '' && $y !== null && (int) $y > 0) {
            return [
                'qty1' => $y,
                'unit1' => 'Year',
                'qty2' => ($m !== '' && $m !== null && (int) $m > 0) ? $m : '',
                'unit2' => ($m !== '' && $m !== null && (int) $m > 0) ? 'Month' : '',
            ];
        }
        if ($m !== '' && $m !== null && (int) $m > 0) {
            return ['qty1' => $m, 'unit1' => 'Month', 'qty2' => '', 'unit2' => ''];
        }

        return ['qty1' => '', 'unit1' => '', 'qty2' => '', 'unit2' => ''];
    }
}
