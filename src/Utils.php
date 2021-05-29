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
namespace littler\CrcHex;

class Utils
{
    private $crc;

    public function __construct()
    {
        $this->crc = new CRC16();
    }

    public function check($hexStr)
    {
        if ($hexStr == '' || $hexStr == "\n" || strlen($hexStr) < 16) {
            throw new \Exception('字符为空或长度不足16');
        }
        if (strlen($hexStr) % 2 != 0) {
            throw new \Exception('16进制字符串长度不能为单数');
        }
        if (! isHexString($hexStr)) {
            throw new \Exception('非16进制字符串');
        }
        $hexStr = str_replace(' ', '', $hexStr);
        $dataLength = substr($hexStr, 16, 2);
        $signature = substr($hexStr, strlen($hexStr) - 4, 4);
        $dataLengthDec = hexdec($dataLength);
        if ((16 + ($dataLengthDec * 2)) != strlen($hexStr)) {
            throw new \Exception('数据长度错误');
        }
        $fixHexStr = substr($hexStr, 0, (strlen($hexStr) - 2 * 2)); // 40
        $crcResult = $this->crc->calc($fixHexStr);
        $crcResultCheck = $crcResult[2] . $crcResult[3] . $crcResult[0] . $crcResult[1];
        if (strtolower($signature) != strtolower($crcResultCheck)) {
            throw new \Exception('CRC16校验不通过' . $hexStr . '=' . $fixHexStr . '===' . $crcResult . '=====' . $crcResultCheck . 'and' . $signature);
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

    public function reBuild($deviceSNStr, $hexStr)
    {
        $deviceSNStr = str_replace(' ', '', $deviceSNStr);
        $hexStr = str_replace(' ', '', $hexStr);
        $result = null;
        if (strlen($deviceSNStr) != 16) {
            throw new \Exception('设备号长度非16位');
        }
        $package = null;
        try {
            $package = $this->parse($hexStr);   // 格式校验，错误抛出异常
            $deviceSN = strToHex($deviceSNStr); // 替换设备号
            $version = $package->getVersion();
            $connectType = $package->getConnectType();
            $command = $package->getCommand();
            $dataLength = $package->getDataLength();
            $data = $package->getData();
            $seq = $package->getSeq();
            $eof = $package->getEof();
            // 计算crc16
            $hexStrNew = $deviceSN . $version . $connectType . $command . $dataLength . $data . $seq;
            $crcResult = $this->crc->calc($hexStrNew);
            $crcResultCheck = $crcResult[2] . $crcResult[3] . $crcResult[0] . $crcResult[1];
            $signature = $crcResultCheck; // 新signature
            $result = $this->build($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    public function reBuildSeq($seqStr, $hexStr)
    {
        $seqStr = str_replace(' ', '', $seqStr);
        $hexStr = str_replace(' ', '', $hexStr);
        $result = null;
        if (strlen($seqStr) != 8) {
            throw new \Exception('seq长度非8位');
        }
        $package = null;
        try {
            $package = $this->parse($hexStr);   // 格式校验，错误抛出异常
            $deviceSN = $package->getDeviceSN();
            $version = $package->getVersion();
            $connectType = $package->getConnectType();
            $command = $package->getCommand();
            $dataLength = $package->getDataLength();
            $data = $package->getData();
            $seq = $seqStr; // 替换设备号
            $eof = $package->getEof();
            // 计算crc16
            $hexStrNew = $deviceSN . $version . $connectType . $command . $dataLength . $data . $seq;
            $crcResult = $this->crc->calc($hexStrNew);
            $crcResultCheck = $crcResult[2] . $crcResult[3] . $crcResult[0] . $crcResult[1];
            $signature = $crcResultCheck; // 新signature
            $result = $this->build($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    public function reBuildData($dataStr, $hexStr)
    {
        $dataStr = str_replace(' ', '', $dataStr);
        $hexStr = str_replace(' ', '', $hexStr);
        $result = null;
        if (strlen($dataStr) % 2 != 0) {
            throw new \Exception('data非偶数');
        }
        $package = null;
        try {
            $package = $this->parse($hexStr);   // 格式校验，错误抛出异常
            $deviceSN = $package->getDeviceSN();
            $version = $package->getVersion();
            $connectType = $package->getConnectType();
            $command = $package->getCommand();
            $dataLength = dechex((strlen($dataStr) / 2)); // 替换设备号
            $dataLength = strlen($dataLength) == 1 ? '0' . $dataLength : $dataLength;
            $data = $dataStr; // 替换设备号
            $seq = $package->getSeq();
            $eof = $package->getEof();
            // 计算crc16
            $hexStrNew = $deviceSN . $version . $connectType . $command . $dataLength . $data . $seq;
            $crcResult = $this->crc->calc($hexStrNew);
            $crcResultCheck = $crcResult[2] . $crcResult[3] . $crcResult[0] . $crcResult[1];
            $signature = $crcResultCheck; // 新signature
            $result = $this->build($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
        return $result;
    }

    private function getPackageScreenType($hexStr, $pos)
    {
        $class = '';
        if (strlen($hexStr) >= 40) {
            $dataLength = substr($hexStr, 38, 2);
            $dataLengthDec = hexdec($dataLength);
            if ($pos >= 0 && $pos < 17) {
                $class = 'sec_1';
            } elseif ($pos == 17) {
                $class = 'sec_2'; //1
            } elseif ($pos == 18) {
                $class = 'sec_3'; //2
            } elseif ($pos == 19) {
                $class = 'sec_4'; //3
            } elseif ($pos == 20) {
                $class = 'sec_5'; // 4 dataLength
            } elseif ($pos > 20 && $pos < (21 + $dataLengthDec)) {
                $class = 'sec_6';
            } elseif ($pos > (20 + $dataLengthDec) && $pos < (25 + $dataLengthDec)) {
                $class = 'sec_7';
            } elseif ($pos > (24 + $dataLengthDec) && $pos < (27 + $dataLengthDec)) {
                $class = 'sec_8';
            } elseif ($pos > (26 + $dataLengthDec) && $pos < (28 + $dataLengthDec)) {
                $class = 'sec_9';
            }
        }
        return $class;
    }

    private function build($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof)
    {
        return new Package($deviceSN, $version, $connectType, $command, $dataLength, $data, $seq, $signature, $eof);
    }
}
