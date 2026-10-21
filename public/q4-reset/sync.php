<?php
// Q4 Reset deck — live presenter sync
// Host (presenter) POSTs the current slide; viewers poll GET and follow.
// The host key is only sent in the presenter's URL (?host=KEY).
header('Content-Type: application/json');
header('Cache-Control: no-store');

$HOST_KEY = '0ce2e5109a78';
$STATE = __DIR__ . '/.sync-state.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = (string)($_POST['key'] ?? '');
    if (!hash_equals($HOST_KEY, $key)) { http_response_code(403); echo json_encode(['error'=>'forbidden']); exit; }
    $slide = (int)($_POST['slide'] ?? 1);
    if ($slide < 1) $slide = 1;
    if ($slide > 500) $slide = 500;
    @file_put_contents($STATE, json_encode(['slide'=>$slide,'t'=>microtime(true)]), LOCK_EX);
    echo json_encode(['ok'=>true,'slide'=>$slide]);
    exit;
}

$s = ['slide'=>1,'t'=>0];
if (is_file($STATE)) { $j = json_decode(@file_get_contents($STATE), true); if (is_array($j)) $s = ['slide'=>(int)($j['slide']??1),'t'=>(float)($j['t']??0)]; }
echo json_encode($s);
