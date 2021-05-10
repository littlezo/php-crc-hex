<?php

declare(strict_types=1);
/**
 * This file is part of Code Ai.
 *
 * @version 1.0.0
 * @author @小小只^v^ <littlezov@qq.com>  littlezov@qq.com
 * @contact  littlezov@qq.com
 * @link     https://github.com/littlezo
 * @document https://github.com/littlezo/wiki
 * @license  https://github.com/littlezo/MozillaPublicLicense/blob/main/LICENSE
 *
 */
function success($data)
{
    json(1, $data);
}

function error($data)
{
    json(0, $data);
}

function json($status, $data)
{
    $result = [
        'status' => $status,
        'data' => $data,
    ];
    echo json_encode($result);
    exit;
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
