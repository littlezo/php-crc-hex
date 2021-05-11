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
namespace littleZov\crc;

class Utils
{
    private $crc;

    public function __construct()
    {
        $this->crc = new CRC16();
    }

    public function check($hexStr)
    {
        if ($hexStr == '' || $hexStr == "\n" || strlen($hexStr) < 40) {
            throw new \Exception('字符为空或长度不足20');
        }
        if (strlen($hexStr) % 2 != 0) {
            throw new \Exception('16进制字符串长度不能为单数');
        }
        if (! isHexString($hexStr)) {
            throw new \Exception('非16进制字符串');
        }
        $hexStr = str_replace(' ', '', $hexStr);
        $dataLength = substr($hexStr, 38, 2); // 40
        $signature = substr($hexStr, strlen($hexStr) - 6, 4);
        $dataLengthDec = hexdec($dataLength);
        if ((54 + ($dataLengthDec * 2)) != strlen($hexStr)) {
            throw new \Exception('数据长度错误');
        }
        $fixHexStr = substr($hexStr, 0, (strlen($hexStr) - 2 * 3)); // 40
        $crcResult = $this->crc->calc($fixHexStr);
        $crcResultCheck = $crcResult[2] . $crcResult[3] . $crcResult[0] . $crcResult[1];
        if (strtolower($signature) != strtolower($crcResultCheck)) {
            throw new \Exception('CRC16校验不通过');
        }
        return true;
    }

    public function parse($hexStr)
    {
        $hexStr = str_replace(' ', '', $hexStr);
        $result = null;
        try {
            // 数据检测
            $isPackage = $this->check($hexStr);
            // 解析数据
            $deviceSN = substr($hexStr, 0, 32);
            $version = substr($hexStr, 32, 2);
            $connectType = substr($hexStr, 34, 2);
            $command = substr($hexStr, 36, 2);
            $dataLength = substr($hexStr, 38, 2);
            $dataLengthDec = hexdec($dataLength);
            $data = substr($hexStr, 40, $dataLengthDec * 2);
            $seq = substr($hexStr, 40 + $dataLengthDec * 2, 8);
            $signature = substr($hexStr, 48 + $dataLengthDec * 2, 4);
            $eof = substr($hexStr, 52 + $dataLengthDec * 2, 2);
            $result = $this->build($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    public function hexScreen($hexStr, $is_hex = true)
    {
        // return $this->isHexString($hexStr);
        $str = '';
        $hexStrArr = [];
        for ($i = 0, $j = 1; $i < strlen($hexStr); $i = $i + 2, $j++) {
            $hexStrArr[] = $hexStr[$i] . $hexStr[$i + 1];
        }
        return $is_hex == true ? implode(' ', $hexStrArr) : implode('', $hexStrArr);
    }

    public function hexToStr($hex)
    {
        $str = '';
        for ($i = 0; $i < strlen($hex) - 1; $i += 2) {
            $str .= chr(hexdec($hex[$i] . $hex[$i + 1]));
        }
        return $str;
    }

    public function strToHex($str)
    {
        $hex = '';
        for ($i = 0; $i < strlen($str); ++$i) {
            $hex .= dechex(ord($str[$i]));
        }
        return $hex;
    }

    public function isHexString($str)
    {
        return ctype_xdigit($str);
    }
}
