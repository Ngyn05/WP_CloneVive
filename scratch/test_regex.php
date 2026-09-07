<?php
$file = 'c:/Users/hnguy/Local Sites/vivevn/app/public/wp-content/themes/theme_vive/shop-us/products/vive-eagle/index.htm';
$html = file_get_contents($file);
echo "Original len: " . strlen($html) . "\n";

$bad = preg_replace( '#<script\b[^>]*>(?:(?!</script>).)*\b(?:_RestockRocketConfig|restockrocket|RestockRocket|BndlrScriptAppended)\b(?:(?!</script>).)*</script>#is', '', $html );
echo "Bad regex result is null? " . ($bad === null ? 'YES' : 'NO') . " (error: " . preg_last_error_msg() . ")\n";

$good = preg_replace_callback( '#<script\b[^>]*>.*?</script>#is', static function( $m ) {
    if ( strpos( $m[0], '_RestockRocketConfig' ) !== false || strpos( $m[0], 'RestockRocket' ) !== false || strpos( $m[0], 'BndlrScriptAppended' ) !== false ) {
        return '';
    }
    return $m[0];
}, $html );
echo "Good callback result is null? " . ($good === null ? 'YES' : 'NO') . " (len: " . strlen((string)$good) . ")\n";
