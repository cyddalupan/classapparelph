@php
    // Extract sublimation items only
    $sublimationItems = [];
    $hasRoster = false;
    $allRosters = [];
    foreach ($services as $item) {
        if (isset($item['sublimationForm'])) {
            $sf = $item['sublimationForm'];
            $sublimationItems[] = $sf;
            
            // Collect roster data for display
            if (!empty($sf['roster'])) {
                $hasRoster = true;
                $allRosters = array_merge($allRosters, $sf['roster']);
            }
        }
    }
    
    // Take the first sublimation item for the main print slip
    $main = $sublimationItems[0] ?? null;
    
    // Calculate total quantity
    $totalQty = 0;
    if ($main) {
        foreach ($main['sizes'] ?? [] as $s) {
            $totalQty += intval($s['quantity'] ?? 0);
        }
        foreach ($main['roster'] ?? [] as $r) {
            $totalQty += intval($r['number'] ?? 1);
        }
    }
    
    // Spec fields
    $specs = $main['specifications'] ?? [];
    
    // Spec parts map
    $specPartsMap = [
        'neckRibbingColor' => 'Neck Ribbing', 'neckTape' => 'Neck Tape', 'cuffs' => 'Cuffs',
        'slit' => 'Slit', 'pocket' => 'Pocket', 'collar' => 'Collar', 'neckShape' => 'Neck Shape',
        'cutType' => 'Cut Type', 'inner' => 'Inner', 'buttonColor' => 'Button',
        'zipperColor' => 'Zipper', 'innerStr' => 'Inner String', 'jersey' => 'Jersey',
        'defaultDesign' => 'Design', 'armsleeve' => 'Arm Sleeve', 'shoulder' => 'Shoulder',
        'sizeLabel' => 'Size Label'
    ];
    
    // Build parts rows
    $partRows = [];
    $garmentName = $main['garment']['name'] ?? '';
    $fabricName = $main['fabric']['name'] ?? '';
    $partsAdded = $main['parts'] ?? [];
    
    // Garment row
    if ($garmentName) {
        $partRows[] = ['part' => 'Garment', 'detail' => $garmentName];
    }
    
    // Spec rows
    foreach ($specPartsMap as $key => $label) {
        $val = $specs[$key] ?? '';
        if ($val) {
            $partRows[] = ['part' => $label, 'detail' => $val];
        }
    }
    
    // Parts row
    if (!empty($partsAdded)) {
        $partDetails = implode(', ', array_map(function($p) { return $p['name'] ?? ''; }, $partsAdded));
        if ($partDetails) {
            $partRows[] = ['part' => 'Parts Added', 'detail' => $partDetails];
        }
    }
    
    // Split parts into two columns
    $splitMid = (int)ceil(count($partRows) / 2);
    $leftParts = array_slice($partRows, 0, $splitMid);
    $rightParts = array_slice($partRows, $splitMid);
    
    // Notes
    $notes = $services[0]['notes'] ?? '';
    
    // Date format helper
    function formatDDMMYYYY($dateStr) {
        if (!$dateStr) return '';
        try {
            return \Carbon\Carbon::parse($dateStr)->format('d/m/Y');
        } catch (\Exception $e) {
            return $dateStr;
        }
    }
    
    // Customer info
    $customerName = $sale->customer_name ?? '';
    $salesNumber = $sale->sales_number ?? '';
    
    // Sales agent
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
        body * { visibility: hidden; }
        #printSlipContent, #printSlipContent * { visibility: visible; }
        #printSlipContent { position: absolute; left: 0; top: 0; width: 100%; max-width: 277mm; display: block !important; }
        .no-print { display: none !important; }
    }
    * { box-sizing: border-box; }
    body { margin: 0; padding: 20px; font-family: 'Courier New', monospace; }
    .print-slip { font-family: 'Courier New', monospace; font-size: 10pt; color: #000; width: 100%; }
    .print-slip h1 { font-size: 16pt; margin: 0; text-align: center; }
    .print-slip table { width: 100%; border-collapse: collapse; }
    .print-slip td, .print-slip th { border: 1px solid #000; padding: 3px 5px; text-align: left; font-size: 9pt; vertical-align: top; }
    .print-slip .no-border td, .print-slip .no-border { border: none; }
    .print-slip .field-label { font-weight: bold; background: #f0f0f0; width: 1%; white-space: nowrap; }
    .print-slip .mockup-box { border: 2px dashed #999; display: flex; align-items: center; justify-content: center; text-align: center; color: #999; font-size: 9pt; overflow: hidden; width:100%; aspect-ratio: 4 / 3; max-height:300px; }
    .print-slip .mockup-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .print-slip .roster-table th { background: #e0e0e0; font-weight: bold; text-align: center; }
    .print-slip .roster-table td { text-align: center; }
    .print-slip .section-title { font-weight: bold; font-size: 11pt; margin-top: 4px; margin-bottom: 2px; }
    .print-slip .divider { border-top: 2px solid #000; margin: 1px 0; }
    #ps_upperInfo > tbody > tr > td { padding: 1px 3px !important; }
    #ps_upperInfo table td, #ps_upperInfo table th { padding: 1px 3px !important; font-size: 9pt; }
    #ps_upperInfo .section-title { margin-top: 1px; margin-bottom: 1px; font-size: 11pt; }
    #ps_upperInfo .field-label { padding: 1px 3px !important; }
</style>
</head>
<body>
<div id="printSlipContent" class="print-slip">
    <!-- Title -->
    <h1>CUSTOMER FORM SPECIFICATIONS</h1>
    @if($salesNumber)
        <div style="text-align:center;font-size:9pt;margin-top:2px;">{{ $salesNumber }}</div>
    @endif

    <div class="divider"></div>

    <!-- Top: 2 columns — Info (33%) | Parts (67%) -->
    <table id="ps_upperInfo"><tr>
        <td style="width:33%;vertical-align:top;" class="no-border">
            <table style="width:100%;"><tr><td class="field-label" style="width:100px;">PROJECT:</td><td id="ps_projectName">{{ $main['projectName'] ?? '' }}</td></tr></table>
            <table style="width:100%;"><tr><td class="field-label" style="width:100px;">DESCRIPTION:</td><td id="ps_description">{{ $main['description'] ?? '' }}</td></tr></table>
            <table style="width:100%;"><tr><td class="field-label" style="width:100px;">FABRIC:</td><td id="ps_fabric">{{ $fabricName }}</td></tr></table>
            <table style="width:100%;"><tr><td class="field-label" style="width:100px;">DESIGNER:</td><td id="ps_designer">{{ $main['designer'] ?? '' }}</td></tr></table>
            <table style="width:100%;"><tr><td class="field-label" style="width:100px;">QTY:</td><td id="ps_qty">{{ $totalQty }} PCS</td></tr></table>
            <table style="width:100%;"><tr><td class="field-label" style="width:100px;">DATE NEEDED:</td><td id="ps_dateNeeded">{{ formatDDMMYYYY($main['dateNeeded'] ?? '') }}</td></tr></table>
            <table style="width:100%;"><tr><td class="field-label" style="width:100px;">AGENT:</td><td id="ps_agent">{{ $salesAgent }}</td></tr></table>
            <table style="width:100%;"><tr><td class="field-label" style="width:100px;">CUSTOMER:</td><td id="ps_customer">{{ $customerName }}</td></tr></table>
        </td>
        <td style="width:67%;vertical-align:top;">
            <div style="width:100%;">
                <table style="width:49%;float:left;">
                    <tr><th style="width:100px;">Part</th><th>Color/Details</th></tr>
                    @foreach($leftParts as $row)
                        <tr><td>{{ $row['part'] }}</td><td>{{ $row['detail'] }}</td></tr>
                    @endforeach
                </table>
                <table style="width:49%;float:right;">
                    <tr><th style="width:100px;">Part</th><th>Color/Details</th></tr>
                    @foreach($rightParts as $row)
                        <tr><td>{{ $row['part'] }}</td><td>{{ $row['detail'] }}</td></tr>
                    @endforeach
                </table>
                <div style="clear:both;"></div>
            </div>
        </td>
    </tr></table>

    <div class="divider"></div>

    <!-- Bottom: Mock-up (30%) | Name List (70%) -->
    <table><tr>
        <td style="width:30%;vertical-align:top" class="no-border">
            <div class="section-title">MOCK UP</div>
            <div class="mockup-box">
                @php
                    // Try mockup_images column first
                    $mockups = is_string($sale->mockup_images) ? json_decode($sale->mockup_images, true) : ($sale->mockup_images ?? []);
                    $mockupUrl = $mockups[0]['url'] ?? ($mockups[0] ?? '');
                    // Fallback: check services JSON for embedded mockup
                    if (!$mockupUrl) {
                        $svcs = is_string($sale->services) ? json_decode($sale->services, true) : ($sale->services ?? []);
                        foreach ((array)$svcs as $svc) {
                            if (!empty($svc['sublimationForm']['mockup'])) {
                                $mockupUrl = $svc['sublimationForm']['mockup'];
                                break;
                            }
                        }
                    }
                @endphp
                @if($mockupUrl)
                    <a href="{{ $mockupUrl }}" target="_blank" style="display:block;width:100%;height:100%;"><img src="{{ $mockupUrl }}" alt="mockup" style="cursor:pointer;max-width:100%;max-height:100%;object-fit:contain;" onerror="this.style.display='none';this.parentElement.innerHTML='<span>MOCK UP HERE</span>'"></a>
                @else
                    <span>MOCK UP HERE</span>
                @endif
            </div>
        </td>
        <td style="width:70%;vertical-align:top" class="no-border">
            <div class="section-title">NAME LIST</div>
            @if($hasRoster && !empty($allRosters))
                @php
                    // Check if ANY roster entry has columns data from Excel import
                    $hasExcelCols = false;
                    $allColHeaders = [];
                    $isArrFormat = false; // array of [header,value] pairs vs object
                    foreach ($allRosters as $r) {
                        if (!empty($r['columns'])) {
                            $hasExcelCols = true;
                            // Detect format: array of pairs [header, value] vs object {header: value}
                            if (!$isArrFormat && isset($r['columns'][0]) && is_array($r['columns'][0])) {
                                $isArrFormat = true;
                            }
                            if ($isArrFormat) {
                                // Array of pairs: [['BACK NAMES','dfsdf'], ['SIZE','XL'], ...]
                                foreach ($r['columns'] as $pair) {
                                    if (!in_array($pair[0], $allColHeaders)) $allColHeaders[] = $pair[0];
                                }
                            } else {
                                // Object format (backward compat): {'BACK NAMES':'dfsdf', 'SIZE':'XL', ...}
                                foreach (array_keys($r['columns']) as $h) {
                                    if (!in_array($h, $allColHeaders)) $allColHeaders[] = $h;
                                }
                            }
                        }
                    }
                    // Helper: get column value from whichever format
                    $getColVal = function($item, $hdr) use ($isArrFormat) {
                        if (empty($item['columns'])) return '';
                        if ($isArrFormat) {
                            foreach ($item['columns'] as $pair) {
                                if ($pair[0] === $hdr) return $pair[1];
                            }
                            return '';
                        } else {
                            return $item['columns'][$hdr] ?? '';
                        }
                    };
                @endphp
                @if($hasExcelCols)
                    {{-- Excel-imported: use ALL original column headers from Excel --}}
                    <table class="roster-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                @foreach($allColHeaders as $hdr)
                                    <th>{{ $hdr }}</th>
                                @endforeach
                                <th>GA</th><th>QA1</th><th>QA2</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allRosters as $idx => $rosterItem)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    @foreach($allColHeaders as $hdr)
                                        <td>{{ $getColVal($rosterItem, $hdr) }}</td>
                                    @endforeach
                                    <td></td><td></td><td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    {{-- No Excel columns: use standard hardcoded headers (backward compat) --}}
                    <table class="roster-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NAME</th>
                                <th>SIZE</th>
                                <th>QTY</th>
                                <th>GA</th><th>QA1</th><th>QA2</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allRosters as $idx => $rosterItem)
                                <tr>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>{{ $rosterItem['name'] ?? '' }}</td>
                                    <td>{{ $rosterItem['size'] ?? '' }}</td>
                                    <td>{{ $rosterItem['number'] ?? '1' }}</td>
                                    <td></td><td></td><td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            @else
                {{-- Show quantities by size --}}
                <table class="roster-table">
                    <thead>
                        <tr><th>Size</th><th>Quantity</th><th>GA</th><th>QA1</th><th>QA2</th></tr>
                    </thead>
                    <tbody>
                        @php $sizes = $main['sizes'] ?? []; @endphp
                        @foreach($sizes as $s)
                            <tr>
                                <td>{{ $s['size'] ?? '' }}</td>
                                <td>{{ $s['quantity'] ?? 0 }}</td>
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

    @if($notes)
        <div style="margin-top:6px;font-size:11pt;text-align:left;border-top:1px solid #000;padding:6px 8px;background:#fffbe6;border-left:3px solid #f0ad4e;line-height:1.5;">📝 {{ $notes }}</div>
    @endif
</div>

<script>
    // Auto-print on load
    window.onload = function() { window.print(); };
</script>
</body>
</html>
