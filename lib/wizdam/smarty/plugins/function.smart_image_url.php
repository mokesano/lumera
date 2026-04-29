<?php
declare(strict_types=1);

/**
 * Output: .../assets/images/[MODIFIER]/[TYPE]/[ID]?as=[FMT]
 * Contoh: .../assets/images/w735/issue/59
 */

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage PluginsFunction
 */
function smarty_function_smart_image_url($params, &$smarty) {
    $type   = isset($params['type']) ? $params['type'] : 'issue';
    $id     = isset($params['id']) ? (int) $params['id'] : 0;
    $width  = isset($params['width']) ? (int) $params['width'] : 0;
    $height = isset($params['height']) ? (int) $params['height'] : 0;
    $format = isset($params['format']) ? $params['format'] : '';

    $req = Request::getRequest();
    $baseUrl = $req->getBaseUrl();
    
    // Tentukan Prefix Modifier (w200, w735h400, atau original)
    $modifier = 'original';
    if ($width > 0) {
        $modifier = "w{$width}";
        if ($height > 0) $modifier .= "h{$height}";
    }

    // BANGUN URL SPRINGER STYLE
    $url = "$baseUrl/assets/images/$modifier/$type/$id";

    // Format WebP (tetap di query string karena ini konversi tipe file)
    if ($format == 'webp') $url .= "?as=webp";

    return $url;
}
?>