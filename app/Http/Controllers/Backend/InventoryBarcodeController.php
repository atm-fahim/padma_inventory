<?php

namespace App\Http\Controllers\Backend;

use App\Interfaces\Repo\Backend\BranchRepo;
use App\Interfaces\Repo\Backend\InventoryBarcodeRepo;
use App\Support\BarcodeLabelSizeCatalog;
use App\Traits\UserAccessTraits;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Redirect;
use Picqer\Barcode\BarcodeGeneratorPNG;

class InventoryBarcodeController extends BaseController
{
    use UserAccessTraits;

    public function __construct(
        private InventoryBarcodeRepo $inventoryBarcodeRepo,
        private BranchRepo $branchRepo,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $orgId = (int) (auth()->user()->org_id ?? 1);
        $userAccessData = $this->userAccessFunction();

        $branches = $this->branchRepo->withoutDeletingData();
        $items = $this->inventoryBarcodeRepo->getItemsWithInventory($orgId);

        return view('backend.item.inventory_barcode.index', [
            'user_access' => $userAccessData,
            'locationGroups' => $this->buildLocationGroups($branches),
            'items' => $items,
            'sheetSizes' => BarcodeLabelSizeCatalog::sheetPresets(),
            'filters' => [
                'location_filter' => (string) $request->input('location_filter', ''),
                'item_id' => $request->input('item_id'),
            ],
            'labels' => [],
        ]);
    }

    public function generate(Request $request)
    {
        $orgId = (int) (auth()->user()->org_id ?? 1);
        $userAccessData = $this->userAccessFunction();

        $selected = $request->input('selected', []);
        if (!is_array($selected)) {
            $selected = [];
        }
        $qtyInput = $request->input('qty', []);
        $defaultQty = max(1, min(500, (int) $request->input('default_qty', 1)));
        $sheetKey = (string) $request->input('label_sheet_size', BarcodeLabelSizeCatalog::DEFAULT_SHEET_KEY);
        $densityKey = (string) $request->input('barcode_density', BarcodeLabelSizeCatalog::DEFAULT_DENSITY_KEY);
        $sheet = BarcodeLabelSizeCatalog::resolveSheet($sheetKey);
        $density = BarcodeLabelSizeCatalog::resolveDensity($densityKey);
        $showPrice = $request->boolean('show_price');
        $showBrand = $request->boolean('show_brand');

        $locationFilter = trim((string) $request->input('location_filter', ''));
        $filterItemId = $request->filled('item_id') ? (int) $request->input('item_id') : null;

        if ($locationFilter === '' && !$filterItemId) {
            return Redirect::back()
                ->withInput()
                ->with('eMessage', 'Division/Branch বা Item select করুন, তারপর Generate চাপুন।');
        }

        [$filterBranchId, $filterDivisionId] = $this->parseLocationFilter($locationFilter);

        $allSources = $this->inventoryBarcodeRepo->searchBarcodeSources(
            $orgId,
            $filterBranchId,
            $filterDivisionId,
            $filterItemId
        );

        if ($selected === [] && $allSources->isNotEmpty()) {
            $selected = $allSources->pluck('set_uniq_no')->unique()->values()->all();
        }

        if ($selected === []) {
            return Redirect::back()
                ->withInput()
                ->with('eMessage', 'এই Branch/Division-এ SET_UNIQ_NO সহ inventory পাওয়া যায়নি।');
        }

        $bySet = $allSources->keyBy('set_uniq_no');

        $generator = new BarcodeGeneratorPNG();
        $labels = [];

        foreach ($selected as $setUniqNo) {
            $setUniqNo = trim((string) $setUniqNo);
            if ($setUniqNo === '' || !$bySet->has($setUniqNo)) {
                continue;
            }

            $row = $bySet->get($setUniqNo);
            $qty = max(1, min(500, (int) ($qtyInput[$setUniqNo] ?? $defaultQty)));
            $itemName = (string) ($row->item_title ?? 'N/A');
            $brandName = (string) ($row->brand_name ?? '');
            $priceLabel = '';
            if ($showPrice) {
                $priceVal = $row->price ?? null;
                if ($priceVal !== null && $priceVal !== '' && (float) $priceVal > 0) {
                    $priceLabel = number_format((float) $priceVal, 2);
                }
            }

            try {
                $png = $generator->getBarcode(
                    $setUniqNo,
                    $generator::TYPE_CODE_128,
                    (int) $density['width_factor'],
                    (int) $density['height_px']
                );
            } catch (\Throwable) {
                continue;
            }

            $img = '<img alt="' . e($setUniqNo) . '" src="data:image/png;base64,' . base64_encode($png) . '" />';

            for ($i = 0; $i < $qty; $i++) {
                $labels[] = [
                    'set_uniq_no' => $setUniqNo,
                    'item_name' => $itemName,
                    'brand_name' => $showBrand ? $brandName : '',
                    'price' => $priceLabel,
                    'barcode_html' => $img,
                ];
            }
        }

        if ($labels === []) {
            return Redirect::back()->withInput()->with('eMessage', 'No valid barcode labels could be generated.');
        }

        $branches = $this->branchRepo->withoutDeletingData();
        $items = $this->inventoryBarcodeRepo->getItemsWithInventory($orgId);

        return view('backend.item.inventory_barcode.index', [
            'user_access' => $userAccessData,
            'locationGroups' => $this->buildLocationGroups($branches),
            'items' => $items,
            'sheetSizes' => BarcodeLabelSizeCatalog::sheetPresets(),
            'filters' => [
                'location_filter' => $locationFilter,
                'item_id' => $filterItemId,
            ],
            'labels' => $labels,
            'label_count' => count($labels),
            'sheetKey' => $sheetKey,
            'sheetLabel' => $sheet['label'],
            'sheetWidthMm' => $sheet['width_mm'],
            'sheetHeightMm' => $sheet['height_mm'],
            'labelCssVars' => BarcodeLabelSizeCatalog::cssVariables($sheetKey),
            'showPrice' => $showPrice,
            'showBrand' => $showBrand,
        ]);
    }

    /**
     * @return array{0: ?int, 1: ?int} branchId, divisionId
     */
    private function parseLocationFilter(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [null, null];
        }

        if (str_starts_with($raw, 'branch:')) {
            $id = (int) substr($raw, 7);

            return [$id > 0 ? $id : null, null];
        }

        if (str_starts_with($raw, 'division:')) {
            $id = (int) substr($raw, 9);

            return [null, $id > 0 ? $id : null];
        }

        return [null, null];
    }

    /**
     * @param \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Collection $branches
     * @return array<string, array<int, array{value: string, label: string}>>
     */
    private function buildLocationGroups($branches): array
    {
        $groups = [];

        foreach ($branches as $branch) {
            $divName = trim((string) ($branch->division_name ?? ''));
            if ($divName === '') {
                $divName = 'Other / Unassigned';
            }
            $groups[$divName] = $groups[$divName] ?? ['division_id' => $branch->division_id ?? null, 'entries' => []];
        }

        foreach ($groups as $divName => &$meta) {
            $entries = [];
            if (!empty($meta['division_id'])) {
                $entries[] = [
                    'value' => 'division:' . (int) $meta['division_id'],
                    'label' => 'All branches in ' . $divName,
                ];
            }
            $meta['entries'] = $entries;
        }
        unset($meta);

        foreach ($branches as $branch) {
            $divName = trim((string) ($branch->division_name ?? ''));
            if ($divName === '') {
                $divName = 'Other / Unassigned';
            }
            $bid = $branch->hr_branch_id ?? $branch->id;
            $groups[$divName]['entries'][] = [
                'value' => 'branch:' . (int) $bid,
                'label' => (string) ($branch->branch_name ?? ('Branch #' . $bid)),
            ];
        }

        foreach ($groups as &$meta) {
            usort($meta['entries'], function ($a, $b) {
                if (str_starts_with($a['value'], 'division:')) {
                    return -1;
                }
                if (str_starts_with($b['value'], 'division:')) {
                    return 1;
                }

                return strcasecmp($a['label'], $b['label']);
            });
        }
        unset($meta);

        uksort($groups, 'strcasecmp');

        $out = [];
        foreach ($groups as $divName => $meta) {
            $out[$divName] = $meta['entries'];
        }

        return $out;
    }
}
