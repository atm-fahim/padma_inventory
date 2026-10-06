<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Human-friendly SET_UNIQ_NO: PBL-{item abbreviation}-{sequence}.
 */
class SetUniqNoFormatter
{
    /**
     * Common IT item phrases → short codes (longer keys first for strpos).
     */
    private const KNOWN_ABBREVIATIONS = [
        'desktop' => 'DK',
        'all-in-one' => 'AIO',
        'all in one' => 'AIO',
        'laptop' => 'LP',
        'notebook' => 'LP',
        'netbook' => 'LP',
        'keyboard' => 'KB',
        'key board' => 'KB',
        'mouse' => 'MS',
        'server' => 'SV',
        'workstation' => 'WS',
        'monitor' => 'MN',
        'printer' => 'PR',
        'router' => 'RT',
        'switch' => 'SW',
        'webcam' => 'WC',
        'headphone' => 'HP',
        'earphone' => 'EP',
        'projector' => 'PJ',
        'scanner' => 'SC',
        'tablet' => 'TB',
        'smartphone' => 'SP',
        'charger' => 'CH',
        'cable' => 'CB',
        'memory' => 'RM',
        'processor' => 'CPU',
        'ssd' => 'SD',
        'hdd' => 'HD',
        'ups' => 'UP',
    ];

    public static function format(string $abbreviation, int $sequence): string
    {
        $abbr = self::sanitizeAbbreviation($abbreviation);

        return 'PBL-' . $abbr . '-' . $sequence;
    }

    public static function abbreviationFromItemTitle(string $itemTitle): string
    {
        $title = trim(mb_strtolower($itemTitle, 'UTF-8'));
        if ($title === '') {
            return 'IT';
        }

        foreach (self::KNOWN_ABBREVIATIONS as $needle => $code) {
            if (str_contains($title, $needle)) {
                return $code;
            }
        }

        return self::deriveAbbreviationFromWords($title);
    }

    private static function deriveAbbreviationFromWords(string $lowerTitle): string
    {
        $skip = ['the', 'and', 'for', 'with', 'new', 'inch', 'gb', 'tb', 'mb', 'pcs', 'pc', 'of', 'a', 'an'];
        $parts = preg_split('/[^a-z0-9]+/u', $lowerTitle, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $significant = [];
        foreach ($parts as $w) {
            if (strlen($w) < 2 && !ctype_digit($w)) {
                continue;
            }
            if (in_array($w, $skip, true)) {
                continue;
            }
            $significant[] = $w;
        }

        if (count($significant) >= 2) {
            $a = mb_substr($significant[0], 0, 1, 'UTF-8');
            $b = mb_substr($significant[1], 0, 1, 'UTF-8');
            $abbr = self::sanitizeAbbreviation($a . $b);

            return strlen($abbr) >= 2 ? $abbr : self::sanitizeAbbreviation(mb_substr($significant[0], 0, 2, 'UTF-8'));
        }

        if (count($significant) === 1) {
            return self::sanitizeAbbreviation(mb_substr($significant[0], 0, 2, 'UTF-8'));
        }

        return self::sanitizeAbbreviation(mb_substr($lowerTitle, 0, 2, 'UTF-8'));
    }

    private static function sanitizeAbbreviation(string $abbr): string
    {
        $abbr = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $abbr) ?? '');
        if ($abbr === '') {
            return 'IT';
        }
        if (strlen($abbr) > 8) {
            $abbr = substr($abbr, 0, 8);
        }

        return $abbr;
    }

    /**
     * Numeric sequence from various legacy and new SET_UNIQ_NO shapes.
     */
    public static function parseSequence(?string $setUniqNo): ?int
    {
        if ($setUniqNo === null || $setUniqNo === '') {
            return null;
        }
        $setUniqNo = trim($setUniqNo);

        if (preg_match('/^PBL-[A-Z0-9]+-(\d+)$/i', $setUniqNo, $m)) {
            return (int) $m[1];
        }

        if (preg_match('/^PBL-?(\d+)/i', $setUniqNo, $m)) {
            return (int) $m[1];
        }

        $parts = explode('-', $setUniqNo);
        if (isset($parts[1]) && is_numeric($parts[1])) {
            return (int) $parts[1];
        }

        return null;
    }

    /**
     * Highest sequence already used for this org/branch (default floor 5000).
     */
    public static function maxSequenceForOrgBranch(int $orgId, $branchId, int $floor = 5000): int
    {
        $max = $floor;
        $query = DB::table('item_inventory')
            ->where('org_id', $orgId)
            ->where('status', '<>', 9)
            ->whereNotNull('set_uniq_no')
            ->where('set_uniq_no', '<>', '')
            ->where(function ($q) {
                $q->where('set_uniq_no', 'like', 'PBL-%')
                    ->orWhere('set_uniq_no', 'like', 'BR%-%');
            });

        if ($branchId !== null && $branchId !== '') {
            $query->where('branch_id', $branchId);
        }

        foreach ($query->cursor() as $row) {
            $n = self::parseSequence($row->set_uniq_no);
            if ($n !== null) {
                $max = max($max, $n);
            }
        }

        return $max;
    }
}
