<?php

echo "=== 🔄 AUTO-REFRESH RE-ENABLER ===\n";
echo "Jika live update BERHASIL, jalankan script ini untuk enable auto-refresh sebagai backup\n\n";

$filePath = 'resources/js/hooks/use-stock-alerts.ts';

if (!file_exists($filePath)) {
    echo "❌ File tidak ditemukan: {$filePath}\n";
    exit(1);
}

$content = file_get_contents($filePath);

// Uncomment auto-refresh section
$pattern = '/\/\*\s*\n\s*\/\/\s*Auto-refresh fallback[\s\S]*?\*\//';
$replacement = function($matches) {
    $code = $matches[0];
    // Remove /* and */
    $code = preg_replace('/\/\*\s*\n/', '', $code);
    $code = preg_replace('/\s*\*\//', '', $code);
    return $code;
};

$newContent = preg_replace_callback($pattern, $replacement, $content);

if ($newContent === $content) {
    echo "⚠️ Auto-refresh sudah aktif atau tidak ditemukan kode yang di-comment\n";
    exit(0);
}

file_put_contents($filePath, $newContent);

echo "✅ Auto-refresh berhasil di-enable!\n";
echo "🔄 Sistem sekarang hybrid: Live update (primary) + Auto-refresh (backup)\n";
echo "💡 Jika live update gagal, auto-refresh akan tetap bekerja setiap 5 detik\n";
