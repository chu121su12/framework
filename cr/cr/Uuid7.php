<?php

namespace CR\Extra;

use Brick\Math\BigInteger as BrickBigInteger;
use Brick\Math\RoundingMode;
use DateTimeInterface;
use Moontoast\Math\BigNumber;
use phpseclib3\Math\BigInteger as Psl3BigInteger;
use Ramsey\Uuid\Uuid;

class Uuid7
{
    const MILLISECONDS = 1000;

    const UUID_TYPE_UNIX_TIME = 7;

    public static function make(DateTimeInterface $dateTime = null)
    {
        $random = random_bytes(10);

        if ($dateTime) {
            $unixTime = self::calculateTime($dateTime->format('U'), $dateTime->format('u'));
        }
        else {
            $time = gettimeofday();

            $unixTime = self::calculateTime($time['sec'], $time['usec']);
        }

        $bytes = hex2bin($unixTime) . $random;

        $uuid = self::uuidFromBytesAndVersion($bytes, self::UUID_TYPE_UNIX_TIME);

        return Uuid::fromString($uuid);
    }

    private static function applyVariant($clockSeq)
    {
        $clockSeq = $clockSeq & 0x3fff;
        $clockSeq |= 0x8000;

        return $clockSeq;
    }

    private static function applyVersion($timeHi, $version)
    {
        $timeHi = $timeHi & 0x0fff;
        $timeHi |= $version << 12;

        return $timeHi;
    }

    private static function calculateTime($seconds, $microseconds)
    {
        if (\strlen(\decbin(~0)) >= 64) {
            $hex = self::calculateHexTime64Bit($seconds, $microseconds);
        }
        elseif (class_exists(Psl3BigInteger::class)) {
            $hex = self::calculateHexTimePhpseclib3($seconds, $microseconds);
        }
        elseif (class_exists(BrickBigInteger::class)) {
            $hex = self::calculateHexTimeBrick($seconds, $microseconds);
        }
        else {
            $hex = self::calculateHexTimeMoontoast($seconds, $microseconds);
        }

        return str_pad($hex, 12, '0', STR_PAD_LEFT);
    }

    private static function calculateHexTime64Bit($seconds, $microseconds)
    {
        // Convert the seconds into milliseconds.
        $sec = $seconds * $ms;

        // Convert the microseconds into milliseconds; the scale is zero because
        // we need to discard the fractional part.
        $usec = (int) ($microseconds / $ms);

        $unixTime = $sec + $usec;

        return (string) dechex($unixTime);
    }

    private static function calculateHexTimePhpseclib3($seconds, $microseconds)
    {
        $ms = new Psl3BigInteger(self::MILLISECONDS);
        $sec = (new Psl3BigInteger($seconds))->multiply($ms);
        $usec = (new Psl3BigInteger($microseconds))->divide($ms)[0];
        $unixTime = $sec->add($usec);
        return $unixTime->toHex();
    }

    private static function calculateHexTimeMoontoast($seconds, $microseconds)
    {
        $ms = (string) self::MILLISECONDS;

        $sec = (new BigNumber($seconds))->setScale(0);
        $sec->multiply($ms);

        $usec = (new BigNumber($microseconds))->setScale(0);
        $usec->divide($ms);

        $unixTime = new BigNumber('0');
        $unixTime->add($sec)->add($usec);

        return $unixTime->convertToBase(16);
    }

    private static function calculateHexTimeBrick($seconds, $microseconds)
    {
        $ms = BrickBigInteger::of(self::MILLISECONDS);
        $sec = BrickBigInteger::of($seconds)->multipliedBy($ms);
        $usec = BrickBigInteger::of($microseconds)->dividedBy($ms, RoundingMode::DOWN);
        $unixTime = $sec->plus($usec);
        return $unixTime->toBase(16);
    }

    private static function uuidFromBytesAndVersion($bytes, $version)
    {
        /** @var array $unpackedTime */
        $unpackedTime = unpack('n*', substr($bytes, 6, 2));
        $timeHi = (int) $unpackedTime[1];
        $timeHiAndVersion = pack('n*', self::applyVersion($timeHi, $version));

        /** @var array $unpackedClockSeq */
        $unpackedClockSeq = unpack('n*', substr($bytes, 8, 2));
        $clockSeqHi = (int) $unpackedClockSeq[1];
        $clockSeqHiAndReserved = pack('n*', self::applyVariant($clockSeqHi));

        $bytes = substr_replace($bytes, $timeHiAndVersion, 6, 2);
        $bytes = substr_replace($bytes, $clockSeqHiAndReserved, 8, 2);

        return self::uuidStringFromBytes($bytes);
    }

    private static function uuidStringFromBytes($bytes)
    {
        $base16Uuid = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s',
            substr($base16Uuid, 0, 8),
            substr($base16Uuid, 8, 4),
            substr($base16Uuid, 12, 4),
            substr($base16Uuid, 16, 4),
            substr($base16Uuid, 20, 12)
        );
    }

}
