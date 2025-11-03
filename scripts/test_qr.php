<?php
require __DIR__ . '/../vendor/autoload.php';
$g = new PragmaRX\Google2FA\Google2FA();
$secret = $g->generateSecretKey();
$gqr = new PragmaRX\Google2FAQRCode\Google2FA();
try {
    $qr = $gqr->getQRCodeInline('TestApp', 'user@example.com', $secret);
    file_put_contents(__DIR__ . '/qr_output.txt', $qr);
    echo 'OK: wrote qr_output.txt (' . strlen($qr) . ' bytes)\n';
    echo 'HEAD: ' . substr($qr, 0, 200) . "\n";
} catch (Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . "\n";
}
