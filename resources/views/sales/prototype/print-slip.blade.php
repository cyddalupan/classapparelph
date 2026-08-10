@php
    // Extract sublimation items only
    $sublimationItems = [];
    $hasRoster = false;
    $allRosters = [];
    foreach ($services as $si => $item) {
        if (isset($item['sublimationForm'])) {
            $sf = $item['sublimationForm'];
            $sf['_itemIndex'] = $si;
            $sf['_itemName'] = $item['name'] ?? ('Product #' . (count($sublimationItems) + 1));
            $sublimationItems[] = $sf;
            if (!empty($sf['roster'])) {
                $hasRoster = true;
                $allRosters = array_merge($allRosters, $sf['roster']);
            }
        }
    }

    // Build slip data for a given sublimation item (reproduces original format)
    function buildSlipData($sf, $sale, $allRosters) {
        // QTY: count from roster if it exists (named entries), otherwise from sizes
        // This avoids double-counting when addProduct creates both from same data
        $totalQty = 0;
        if (!empty($sf['roster'])) {
            foreach ($sf['roster'] as $r) {
                $totalQty += intval($r['qty'] ?? $r['number'] ?? 1);
            }
        } else {
            foreach ($sf['sizes'] ?? [] as $s) {
                $totalQty += intval($s['quantity'] ?? $s['qty'] ?? 0);
            }
        }

        // Support BOTH key formats: 'specifications' (old) and 'specs' (legacy JS)
        $specs = $sf['specifications'] ?? $sf['specs'] ?? [];
        // If specs is a string ("N/A"), treat as empty
        if (is_string($specs)) $specs = [];
        
        $specPartsMap = [
            'neckRibbingColor' => 'Neck Ribbing', 'neckTape' => 'Neck Tape', 'cuffs' => 'Cuffs',
            'slit' => 'Slit', 'pocket' => 'Pocket', 'collar' => 'Collar', 'neckShape' => 'Neck Shape',
            'cutType' => 'Cut Type', 'inner' => 'Inner', 'buttonColor' => 'Button',
            'zipperColor' => 'Zipper', 'innerStr' => 'Inner String', 'jersey' => 'Jersey',
            'defaultDesign' => 'Design', 'armsleeve' => 'Arm Sleeve', 'shoulder' => 'Shoulder',
            'sizeLabel' => 'Size Label'
        ];

        // Support BOTH formats: garment[name] (nested) AND garmentType (legacy JS)
        $garmentName = $sf['garment']['name'] ?? $sf['garmentType'] ?? '';
        // Support BOTH formats: fabric[name] (nested) AND fabric string (legacy JS)
        $fabricName = $sf['fabric']['name'] ?? (is_string($sf['fabric'] ?? null) ? $sf['fabric'] : '');
        $partsAdded = $sf['parts'] ?? [];

        // Build part rows from specs (using specPartsMap label mapping)
        $partRows = [];
        if ($garmentName) $partRows[] = ['part' => 'Garment', 'detail' => $garmentName];
        foreach ($specPartsMap as $key => $label) {
            // Check lowercase key first (preferred), then case-insensitive fallback
            $val = $specs[$key] ?? '';
            if (!$val) {
                foreach ($specs as $sk => $sv) {
                    if (strtolower($sk) === $key && $sv) {
                        $val = $sv;
                        break;
                    }
                }
            }
            if ($val) $partRows[] = ['part' => $label, 'detail' => $val];
        }
        if (!empty($partsAdded)) {
            $partDetails = implode(', ', array_map(function($p) { return $p['name'] ?? ''; }, $partsAdded));
            if ($partDetails) $partRows[] = ['part' => 'Parts Added', 'detail' => $partDetails];
        }

        $splitMid = (int)ceil(count($partRows) / 2);
        $leftParts = array_slice($partRows, 0, $splitMid);
        $rightParts = array_slice($partRows, $splitMid);

        // Mockup: support mockupUrl (normalized), mockup (old), sale->mockup_images
        $mockupUrl = '';
        if (!empty($sf['mockupUrl'])) {
            $mockupUrl = $sf['mockupUrl'];
        } elseif (!empty($sf['mockup'])) {
            $mockupUrl = $sf['mockup'];
        } else {
            $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
            if (!empty($mockups[0]['url'])) $mockupUrl = $mockups[0]['url'];
            elseif (!empty($mockups[0])) $mockupUrl = $mockups[0];
        }

        // Roster: if sf['roster'] is empty but sizes has named entries, build roster from sizes
        $roster = $sf['roster'] ?? [];
        if (empty($roster)) {
            $sizes = $sf['sizes'] ?? [];
            foreach ($sizes as $s) {
                if (!empty($s['name'])) {
                    $roster[] = [
                        'name' => $s['name'] ?? '',
                        'size' => $s['size'] ?? '',
                        'number' => $s['qty'] ?? $s['quantity'] ?? 1,
                    ];
                }
            }
        }

        // Detect Excel column headers from first roster entry with columns array
        $excelHeaders = [];
        if (!empty($roster) && !empty($roster[0]['columns']) && is_array($roster[0]['columns'])) {
            foreach ($roster[0]['columns'] as $col) {
                if (is_array($col) && count($col) >= 2) {
                    $excelHeaders[] = $col[0];
                }
            }
        }

        return [
            'totalQty' => $totalQty,
            'specs' => $specs,
            'garmentName' => $garmentName,
            'fabricName' => $fabricName,
            'leftParts' => $leftParts,
            'rightParts' => $rightParts,
            'mockupUrl' => $mockupUrl,
            'notes' => $sf['notes'] ?? '',
            'sizes' => $sf['sizes'] ?? [],
            'roster' => $roster,
            'excelHeaders' => $excelHeaders,
            '_itemName' => $sf['_itemName'] ?? '',
        ];
    }

    function formatDDMMYYYY($dateStr) {
        if (!$dateStr) return '';
        try { return \Carbon\Carbon::parse($dateStr)->format('d/m/Y'); } catch (\Exception $e) { return $dateStr; }
    }

    // Build data for all items
    $slipDataList = [];
    foreach ($sublimationItems as $idx => $sf) {
        $slipDataList[] = buildSlipData($sf, $sale, $allRosters);
    }

    $customerName = $sale->customer_name ?? '';
    $salesNumber = $sale->sales_number ?? '';
    $salesAgent = $sale->sales_agent_name ?? '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Order Slip — {{ $salesNumber }}</title>
<style>
    @media print {
        @page { size: A4 landscape; margin: 12mm 15mm; }
        .no-print { display: none !important; }
        .print-slip-item { position: static; width: 100%; max-width: 277mm; }
        .print-slip-item { break-after: page; }
        .print-slip-item:last-child { break-after: auto; }
    }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 20px; font-family: 'Courier New', monospace; }
    .print-slip { font-family: 'Courier New', monospace; font-size: 10pt; color: #000; width: 100%; }
    .print-slip table { border-collapse: collapse; width: 100%; table-layout: fixed; }
    .print-slip table.inner-table { table-layout: auto; }
    .print-slip .no-border td, .print-slip .no-border th { border: none; }
    .print-slip .section-title { background: #000; color: #fff; padding: 3px 6px; font-weight: bold; font-size: 10pt; text-align: center; letter-spacing: 1px; }
    .print-slip .info-table td, .print-slip .info-table th { border: 1px solid #000; padding: 2px 4px; }
    .print-slip .info-table .label { font-weight: bold; width: 30%; background: #f0f0f0; }
    .print-slip .roster-table th { background: #e0e0e0; font-weight: bold; text-align: center; }
    .print-slip .roster-table td { text-align: center; }
    .print-slip .parts-table td { padding: 1px 4px; }
    .print-slip .mockup-box { border: 1px dotted #999; min-height: 100px; display: flex; align-items: center; justify-content: center; }
    .print-slip .mockup-box img { max-width: 100%; max-height: 180px; object-fit: contain; }
    .product-selector { text-align: center; margin-bottom: 12px; }
    .product-selector button { padding: 4px 12px; margin: 0 2px; cursor: pointer; border: 1px solid #999; background: #fff; }
    .product-selector button.active { background: #000; color: #fff; border-color: #000; }
</style>
</head>
<body>
@if(count($slipDataList) > 1)
    <div class="product-selector no-print">
        @foreach($slipDataList as $sdIdx => $sd)
            @php
                $btnLabel = $sd['garmentName'] ?: $sd['_itemName'];
            @endphp
            <button data-idx="{{ $sdIdx }}" class="{{ $sdIdx === 0 ? 'active' : '' }}" onclick="showProduct({{ $sdIdx }})">
                {{ $btnLabel }}
            </button>
        @endforeach
        <span style="margin-left:8px;font-size:9pt;color:#999;">(Items auto-print each on a new page)</span>
    </div>
@endif
@forelse($slipDataList as $sdIdx => $sd)
    @php
        $hasExcelHeaders = !empty($sd['excelHeaders']);
        $excelHeaders = $sd['excelHeaders'] ?? [];
    @endphp
    <div class="print-slip-item" style="display: {{ $sdIdx === 0 ? 'block' : 'none' }}">
        <div class="print-slip">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;border-bottom:2px solid #000;padding-bottom:3px;">
                <div style="font-size:12pt;font-weight:bold;">{{ $sd['garmentName'] ?: $sd['_itemName'] }}</div>
                <div style="font-size:9pt;">Sales #: <strong>{{ $salesNumber }}</strong> | Date: <strong>{{ formatDDMMYYYY($sale->created_at) }}</strong></div>
            </div>

            <table style="width:100%;"><tr><!-- 2‑col: info left, parts right -->
                <td style="width:33%;vertical-align:top;padding-right:6px;" class="no-border">
                    <div class="section-title">INFORMATION</div>
                    <table class="info-table" style="margin-top:2px;">
                        <tr><td class="label">Customer</td><td>{{ $customerName }}</td></tr>
                        <tr><td class="label">Sales Agent</td><td>{{ $salesAgent }}</td></tr>
                        @if($sd['garmentName'])
                        <tr><td class="label">Garment</td><td>{{ $sd['garmentName'] }}</td></tr>
                        @endif
                        @if($sd['fabricName'])
                        <tr><td class="label">Fabric</td><td>{{ $sd['fabricName'] }}</td></tr>
                        @endif
                        <tr><td class="label">Total Qty</td><td>{{ $sd['totalQty'] }}</td></tr>
                    </table>
                </td>
                <td style="width:67%;vertical-align:top;padding-left:6px;" class="no-border">
                    @if(!empty($sd['leftParts']) || !empty($sd['rightParts']))
                    <div class="section-title">CUSTOMER FORM SPECIFICATIONS</div>
                    <table class="inner-table" style="width:100%;"><tr>
                        <td style="width:50%;vertical-align:top;">
                            <table class="parts-table" style="width:100%;">
                                @foreach($sd['leftParts'] as $p)
                                <tr><td style="font-weight:bold;">{{ $p['part'] }}</td><td>{{ $p['detail'] }}</td></tr>
                                @endforeach
                            </table>
                        </td>
                        <td style="width:50%;vertical-align:top;">
                            <table class="parts-table" style="width:100%;">
                                @foreach($sd['rightParts'] as $p)
                                <tr><td style="font-weight:bold;">{{ $p['part'] }}</td><td>{{ $p['detail'] }}</td></tr>
                                @endforeach
                            </table>
                        </td>
                    </tr></table>
                    @endif
                </td>
            </tr></table>

            <table style="width:100%;"><tr><!-- 2‑col: mockup left, roster right -->
                <td style="width:30%;vertical-align:top" class="no-border">
                    <div class="section-title">MOCK UP</div>
                    <div class="mockup-box">
                        @if($sd['mockupUrl'])
                            <img src="{{ $sd['mockupUrl'] }}" alt="mockup" onerror="this.style.display='none';this.parentElement.innerHTML='<span>MOCK UP HERE</span>'">
                        @else
                            <span>MOCK UP HERE</span>
                        @endif
                    </div>
                </td>
                <td style="width:70%;vertical-align:top" class="no-border">
                    <div class="section-title">NAME LIST</div>
                    @if(!empty($sd['roster']))
                        <table class="roster-table">
                            @if($hasExcelHeaders)
                                <thead><tr><th>#</th>
                                    @foreach($excelHeaders as $hdr)
                                        <th>{{ $hdr }}</th>
                                    @endforeach
                                    <th>GA</th><th>QA1</th><th>QA2</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($sd['roster'] as $rIdx => $rosterItem)
                                    <tr>
                                        <td>{{ $rIdx + 1 }}</td>
                                        @php
                                            $cols = $rosterItem['columns'] ?? [];
                                        @endphp
                                        @if(!empty($cols) && is_array($cols))
                                            @foreach($cols as $col)
                                                <td>{{ is_array($col) && count($col) >= 2 ? $col[1] : '' }}</td>
                                            @endforeach
                                        @else
                                            <td>{{ $rosterItem['name'] ?? '' }}</td>
                                            <td>{{ $rosterItem['number'] ?? '' }}</td>
                                            <td>{{ $rosterItem['size'] ?? '' }}</td>
                                            <td>{{ $rosterItem['qty'] ?? '1' }}</td>
                                        @endif
                                        <td></td><td></td><td></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            @else
                                <thead><tr><th>#</th><th>NAME</th><th>NUMBER</th><th>SIZE</th><th>QTY</th><th>GA</th><th>QA1</th><th>QA2</th></tr></thead>
                                <tbody>
                                    @foreach($sd['roster'] as $rIdx => $rosterItem)
                                    <tr>
                                        <td>{{ $rIdx + 1 }}</td>
                                        <td>{{ $rosterItem['name'] ?? '' }}</td>
                                        <td>{{ $rosterItem['number'] ?? '' }}</td>
                                        <td>{{ $rosterItem['size'] ?? '' }}</td>
                                        <td>{{ $rosterItem['qty'] ?? '1' }}</td>
                                        <td></td><td></td><td></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            @endif
                        </table>
                    @else
                        <table class="roster-table">
                            <thead><tr><th>Size</th><th>Quantity</th><th>GA</th><th>QA1</th><th>QA2</th></tr></thead>
                            <tbody>
                                @php $sizes = $sd['sizes'] ?? []; @endphp
                                @foreach($sizes as $s)
                                <tr>
                                    <td>{{ $s['size'] ?? '' }}</td>
                                    <td>{{ $s['quantity'] ?? $s['qty'] ?? 0 }}</td>
                                    <td></td><td></td><td></td>
                                </tr>
                                @endforeach
                                @if(empty($sizes))
                                <tr><td colspan="5" style="text-align:center;">—</td></tr>
                                @endif
                            </tbody>
                        </table>
                    @endif
                </td>
            </tr></table>

            @if($sd['notes'])
                <div style="margin-top:4px;font-size:9pt;text-align:right;border-top:1px solid #000;padding-top:2px;">Note: {{ $sd['notes'] }}</div>
            @endif
        </div>
    </div>
    @empty
    <div class="print-slip">
        <p>No sublimation products found.</p>
    </div>
    @endforelse

<script>
    var currentIdx = 0;
    function showProduct(idx) {
        document.querySelectorAll('.print-slip-item').forEach(function(el, i) {
            el.style.display = i === idx ? 'block' : 'none';
        });
        document.querySelectorAll('.product-selector button').forEach(function(btn) {
            var btnIdx = parseInt(btn.dataset.idx);
            btn.classList.toggle('active', btnIdx === idx);
        });
        currentIdx = idx;
    }
</script>
</body>
</html>
