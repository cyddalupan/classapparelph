{{-- SHARED: Garment Order Slip template (Create Sale + Sale Details/Kanban) --}}
<!-- Garment Order Slip Template (hidden, populated via JS) -->
<div id="garment_printSlip" style="display:none;">
    <style>
        @media print {
            body.gps-printing * { visibility: hidden !important; }
            body.gps-printing #garment_printSlip, body.gps-printing #garment_printSlip * { visibility: visible !important; }
            body.gps-printing #garment_printSlip { position: absolute; left: 0; top: 0; width: 100%; max-width: 277mm; display: block !important; }
            body.gps-printing #sublimation_printSlip { display: none !important; }
            body.gps-printing .no-print { display: none !important; }
        }
        .print-slip { font-family: 'Courier New', monospace; font-size: 10pt; color: #000; width: 100%; }
        .print-slip h1 { font-size: 16pt; margin: 0; text-align: center; }
        .print-slip h2 { font-size: 13pt; margin: 0; text-align: center; }
        .print-slip table { width: 100%; border-collapse: collapse; }
        .print-slip td, .print-slip th { border: 1px solid #000; padding: 3px 5px; text-align: left; font-size: 9pt; vertical-align: top; }
        .print-slip .no-border td, .print-slip .no-border { border: none; }
        .print-slip .field-label { font-weight: bold; background: #f0f0f0; width: 1%; white-space: nowrap; }
        .print-slip .mockup-box { border: 2px dashed #999; display: flex; align-items: center; justify-content: center; text-align: center; color: #999; font-size: 9pt; overflow: hidden; width:100%; aspect-ratio: 4 / 3; max-height:300px; }
        .print-slip .mockup-box img { max-width: 100%; max-height: 100%; object-fit: contain; }
        .print-slip .roster-table th { background: #e0e0e0; font-weight: bold; text-align: center; }
        .print-slip .roster-table td { text-align: center; }
        .print-slip .roster-table th, .print-slip .roster-table td { border: 1px solid #bbb; }
        .print-slip .roster-table tr.roster-filler td { height: 12px; font-size: 6pt; line-height: 6pt; padding: 0; }
        .print-slip .section-title { font-weight: bold; font-size: 11pt; margin-top: 4px; margin-bottom: 2px; }
        .print-slip .divider { border-top: 2px solid #000; margin: 1px 0; }
    </style>
    <div id="garmentSlipContent" class="print-slip">
        <h1>CUSTOMER FORM SPECIFICATIONS</h1>
        <div class="divider"></div>
        <table id="gps_upperInfo"><tr>
            <td style="width:33%;vertical-align:top;" class="no-border">
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">PROJECT:</td><td id="gps_projectName"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">SHOP:</td><td id="gps_shop"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">DATE NEEDED:</td><td id="gps_dateNeeded"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">QTY:</td><td id="gps_qty"></td></tr></table>
                <table style="width:100%;"><tr><td class="field-label" style="width:100px;">AMOUNT:</td><td id="gps_amount"></td></tr></table>
            </td>
            <td style="width:67%;vertical-align:top;">
                <div class="section-title">PRODUCTS</div>
                <table id="gps_productsTable">
                    <thead><tr><th>Product</th><th style="width:60px;">Qty</th><th style="width:90px;">Price</th><th style="width:100px;">Subtotal</th></tr></thead>
                    <tbody id="gps_productsBody"></tbody>
                </table>
                <div class="section-title" style="margin-top:6px;">PRINTING OPTIONS</div>
                <div id="gps_printOptions" style="font-size:9pt;"></div>
            </td>
        </tr></table>
        <div class="divider"></div>
        <table><tr>
            <td style="width:30%;vertical-align:top" class="no-border">
                <div class="section-title">MOCK UP</div>
                <div class="mockup-box" style="margin:0 auto;">
                    <span id="gps_mockupPlaceholder">MOCK UP HERE</span>
                    <img id="gps_mockupImg" src="" style="display:none;">
                </div>
            </td>
            <td style="width:70%;vertical-align:top" class="no-border">
                <div class="section-title">NAME LIST</div>
                <table class="roster-table" id="gps_rosterTable">
                    <thead></thead>
                    <tbody id="gps_rosterBody"></tbody>
                </table>
            </td>
        </tr></table>
        <div style="margin-top:4px;font-size:9pt;text-align:right;border-top:1px solid #000;padding-top:2px;" id="gps_note"></div>
    </div>
</div>