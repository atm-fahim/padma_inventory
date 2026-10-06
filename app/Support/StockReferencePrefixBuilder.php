<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Prefixes for stock reference numbers (branch / division) before PBL-style SET_UNIQ segments.
 */
class StockReferencePrefixBuilder
{
    /**
     * Resolve branch row by inventory branch_id (matches branch.hr_branch_id or branch.id).
     */
    public static function findBranchRow(?int $branchId): ?object
    {
        if ($branchId === null || (int) $branchId <= 0) {
            return null;
        }

        return DB::table('branch')
            ->where(function ($q) use ($branchId) {
                $q->where('hr_branch_id', $branchId)
                    ->orWhere('id', $branchId);
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '<>', 9);
            })
            ->orderByDesc('id')
            ->first();
    }

    public static function branchTag(?int $branchId): string
    {
        if ($branchId === null || (int) $branchId <= 0) {
            return '';
        }

        $branch = self::findBranchRow($branchId);
        if (! $branch) {
            return 'B' . (int) $branchId;
        }

        $slug = trim((string) data_get($branch, 'slug', ''));
        if ($slug !== '') {
            $tag = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', Str::slug($slug, '')));
            if (strlen($tag) > 10) {
                $tag = substr($tag, 0, 10);
            }
            if ($tag !== '') {
                return $tag;
            }
        }

        $hrId = (int) (data_get($branch, 'hr_branch_id') ?: $branchId);

        return 'B' . $hrId;
    }

    public static function divisionTagForBranch(?int $branchId): string
    {
        if ($branchId === null || (int) $branchId <= 0) {
            return '';
        }

        $branch = self::findBranchRow($branchId);
        $divisionId = (int) data_get($branch, 'division_id', 0);
        if ($divisionId <= 0) {
            return '';
        }

        $division = DB::table('division')->where('id', $divisionId)->first();
        if (! $division) {
            return 'D' . $divisionId;
        }

        $short = trim((string) data_get($division, 'short_name', ''));
        if ($short !== '') {
            $t = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $short));

            return $t !== '' ? substr($t, 0, 10) : 'D' . $divisionId;
        }

        $slug = trim((string) data_get($division, 'slug', ''));
        if ($slug !== '') {
            $t = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', Str::slug($slug, '')));

            return $t !== '' ? substr($t, 0, 10) : 'D' . $divisionId;
        }

        return 'D' . $divisionId;
    }

    /**
     * Prefer division code when branch is linked to a division; otherwise branch tag.
     */
    public static function locationPrefix(?int $branchId): string
    {
        if ($branchId === null || (int) $branchId <= 0) {
            return '';
        }

        $div = self::divisionTagForBranch($branchId);
        if ($div !== '') {
            return $div;
        }

        return self::branchTag($branchId);
    }

    /**
     * First two alphanumeric characters from branch name/slug (for SI-/SO- PBL segment).
     */
    public static function branchTwoCharCode(?int $branchId): string
    {
        if ($branchId === null || (int) $branchId <= 0) {
            return 'NA';
        }

        $branch = self::findBranchRow($branchId);
        if (! $branch) {
            return str_pad((string) (abs((int) $branchId) % 100), 2, '0', STR_PAD_LEFT);
        }

        $name = trim((string) data_get($branch, 'branch_name', ''));
        $fromName = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $name));
        if (strlen($fromName) >= 2) {
            return substr($fromName, 0, 2);
        }
        if (strlen($fromName) === 1) {
            return $fromName . '0';
        }

        $slug = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', Str::slug((string) data_get($branch, 'slug', ''), '')));
        if (strlen($slug) >= 2) {
            return substr($slug, 0, 2);
        }
        if (strlen($slug) === 1) {
            return $slug . '0';
        }

        $hrId = (int) (data_get($branch, 'hr_branch_id') ?: $branchId);

        return str_pad((string) ($hrId % 100), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Stock-in number: SI-PBL-{BR2}-{SET_UNIQ_NO body} (BR2 = first 2 chars of branch).
     */
    public static function formatStockInNumber(string $setUniqNo, ?int $branchId): string
    {
        $setUniqNo = trim($setUniqNo);
        $setUniqNo = preg_replace('/^PBL-?/i', '', $setUniqNo) ?? $setUniqNo;
        $setUniqNo = ltrim($setUniqNo, '-');
        $two = self::branchTwoCharCode($branchId);

        return 'SI-PBL' . $two . $setUniqNo;
    }

    /**
     * Stock-out number body prefix (after SO-): PBL-{BR2}-{Ymd}-{fromBranchId}-
     *
     * @param  int  $toBranchId  Destination (to) branch — first 2 chars derived from this branch.
     */
    public static function formatStockOutPrefix(int $fromBranchId, int $toBranchId): string
    {
        $datePart = now()->format('Ymd');
        $fromBranchId = (int) $fromBranchId;
        $toBranchId = (int) $toBranchId;
        $two = self::branchTwoCharCode($toBranchId > 0 ? $toBranchId : $fromBranchId);

        return 'SO-PBL' . $two . '-' . $datePart . '-' . $fromBranchId . '-';
    }
}
