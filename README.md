# PHP CRC HEX
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Flittlezo%2Fphp-crc-hex.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Flittlezo%2Fphp-crc-hex?ref=badge_shield)


This is an implementation of the [CRC RevEng Catalogue](http://reveng.sourceforge.net/crc-catalogue/) in PHP 7.x

## How to use

```PHP
<?php
use littler\CrcHex\CRC16;

require_once ./vendor/autoload.php;

$crc = new CRC16();
$crcResult = $crc->calc("10010000FF00");
echo $crcResult;
// BB7E
```

## Currently implemented

#### 8bit CRC

Not yet

#### 16bit CRC

IBM, MAXIM, USB, MODBUS, CCITT, CCITT-FALSE, X25, XMODEM, DNP

#### 24bit CRC

Not yet

#### 32bit CRC

Not yet


## License
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Flittlezo%2Fphp-crc-hex.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Flittlezo%2Fphp-crc-hex?ref=badge_large)