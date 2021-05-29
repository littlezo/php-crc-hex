<?php

declare(strict_types=1);
/**
 * #logic 做事不讲究逻辑，再努力也只是重复犯错
 * ## 何为相思：不删不聊不打扰，可否具体点：曾爱过。何为遗憾：你来我往皆过客，可否具体点：再无你。.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
function hexScreen($hexStr, $is_hex = true)
{
    $str = '';
    $hexStrArr = [];
    for ($i = 0, $j = 1; $i < strlen($hexStr); $i = $i + 2, $j++) {
        $hexStrArr[] = $hexStr[$i] . $hexStr[$i + 1];
    }
    return $is_hex == true ? implode(' ', $hexStrArr) : implode('', $hexStrArr);
}

function hexToStr($hex)
{
    $str = '';
    for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
        $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
    }
    return $str;
}

function strToHex($str)
{
    $hex = '';
    for ($i = 0; $i < strlen($str); ++$i) {
        $hex .= dechex(ord($str[$i]));
    }
    return $hex;
}

function isHexString($str)
{
    return ctype_xdigit($str);
}
