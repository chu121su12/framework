<?php

namespace CR\Extra;

use DateTimeInterface;
use phpseclib3\Math\BigInteger;
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
        $ms = new BigInteger(self::MILLISECONDS);

        // Convert the seconds into milliseconds.
        $sec = (new BigInteger($seconds))->multiply($ms);

        // Convert the microseconds into milliseconds; the scale is zero because
        // we need to discard the fractional part.
        $usec = (new BigInteger($microseconds))->divide($ms)[0];

        $unixTime = $sec->add($usec);

        return str_pad($unixTime->toHex(), 12, '0', STR_PAD_LEFT);
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

        return static::uuidStringFromBytes($bytes);
    }

    private static function uuidStringFromBytes($bytes)
    {
        $base16Uuid = bin2hex($bytes);

        return sprintf(
            '%s-%s-%s-%s-%s'
            substr($base16Uuid, 0, 8),
            substr($base16Uuid, 8, 4),
            substr($base16Uuid, 12, 4),
            substr($base16Uuid, 16, 4),
            substr($base16Uuid, 20, 12)
        );
    }

}
