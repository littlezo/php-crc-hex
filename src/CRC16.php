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

class CRC16
{
    private $calcType;

    private $calcTypeHash = [
        'IBM',
        'MAXIM',
        'USB',
        'MODBUS',
        'CCITT',
        'CCITT-FALSE',
        'X25',
        'XMODEM',
        'DNP',
    ];

    /**
     * @param string $calc
     */
    public function __construct($calc = 'MODBUS')
    {
        $this->calcType = in_array(strtoupper($calc), $this->calcTypeHash) ? strtoupper($calc) : 'MODBUS';
    }

    /**
     * @param $str
     * @return null|string
     */
    public function calc($str)
    {
        $result = null;
        switch ($this->calcType) {
            case 'MODBUS':
                $result = $this->crc16Modbus($str);
                break;
            case 'X25':
                $result = $this->crc16x25($str);
                break;
        }
        return $result;
    }

    /**
     * crc16 for Modbus.
     * @param $str
     * @return string
     */
    private function crc16Modbus($str)
    {
        $data = pack('H*', $str);
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); ++$i) {
            $crc ^= ord($data[$i]);
            for ($j = 8; $j != 0; --$j) {
                if (($crc & 0x0001) != 0) {
                    $crc >>= 1;
                    $crc ^= 0xA001;
                } else {
                    $crc >>= 1;
                }
            }
        }
        return sprintf('%04X', $crc);
    }

    /**
     * crc16 for x25.
     * @param $str
     * @return string
     */
    private function crc16x25($str)
    {
        // $data = pack('H*', $str);
        $init = 0xFFFF;
        $ploy = 0x1021;
        $char = 0;
        $crc = $this->hash($str, 0x1021, 0xffff, 0xffff, true, false);
        return sprintf('%04X', $crc);
        // for ($i = 0; $i < strlen($data); ++$i) {
        //     $crc ^= ord($data[$i]);
        //     for ($j = 8; $j != 0; --$j) {
        //         if (($crc & 0x0001) != 0) {
        //             $crc >>= 1;
        //             $crc ^= 0xA001;
        //         } else {
        //             $crc >>= 1;
        //         }
        //     }
        // }
        return sprintf('%04X', $crc);
    }

    /**
     * @param string $str 待校验字符串
     * @param int $polynomial 二项式
     * @param int $initValue 初始值
     * @param int $xOrValue 输出结果前异或的值
     * @param bool $inputReverse 输入字符串是否每个字节按比特位反转
     * @param bool $outputReverse 输出是否整体按比特位反转
     * @param mixed $char
     * @return int
     */

    /**
     * 将一个字符按比特位进行反转 eg: 65 (01000001) --> 130(10000010).
     * @param $char
     * @return string $char
     */
    private function reverseChar($char)
    {
        $byte = ord($char);
        $tmp = 0;
        for ($i = 0; $i < 8; ++$i) {
            if ($byte & (1 << $i)) {
                $tmp |= (1 << (7 - $i));
            }
        }
        return chr($tmp);
    }

    /**
     * 将一个字节流按比特位反转 eg: 'AB'(01000001 01000010)  --> '\x42\x82'(01000010 10000010).
     * @param $str
     * @return mixed
     */
    private function reverseString($str)
    {
        $m = 0;
        $n = strlen($str) - 1;
        while ($m <= $n) {
            if ($m == $n) {
                $str[$m] = $this->reverseChar($str[$m]);
                break;
            }
            $ord1 = $this->reverseChar($str[$m]);
            $ord2 = $this->reverseChar($str[$n]);
            $str[$m] = $ord2;
            $str[$n] = $ord1;
            ++$m;
            --$n;
        }
        return $str;
    }

    private function hash($str, $polynomial, $initValue, $xOrValue, $inputReverse = false, $outputReverse = false)
    {
        $crc = $initValue;

        for ($i = 0; $i < strlen($str); ++$i) {
            if ($inputReverse) {
                // 输入数据每个字节按比特位逆转
                $c = ord($this->reverseChar($str[$i]));
            } else {
                $c = ord($str[$i]);
            }
            dd($str[$i]);
            dd($c);
            $crc ^= ($c << 8);
            for ($j = 0; $j < 8; ++$j) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) & 0xffff) ^ $polynomial;
                } else {
                    $crc = ($crc << 1) & 0xffff;
                }
            }
        }
        if ($outputReverse) {
            // 把低地址存低位，即采用小端法将整数转换为字符串
            $ret = pack('cc', $crc & 0xff, ($crc >> 8) & 0xff);
            // 输出结果按比特位逆转整个字符串
            $ret = $this->reverseString($ret);
            // 再把结果按小端法重新转换成整数
            $arr = unpack('vshort', $ret);
            $crc = $arr['short'];
        }
        return $crc ^ $xOrValue;
    }
}
