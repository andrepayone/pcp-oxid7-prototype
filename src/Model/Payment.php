<?php

declare(strict_types=1);

namespace Payone\PcpPrototype\Model;

class Payment extends Payment_parent
{
    protected static array $pcpPaymentTypes = [
        'pcpcreditcard',
        'pcpsecuredebit',
        'pcppaypal',
        'pcpsecureinstallment',
        'pcppayinstore',
    ];

    public static function isPcpPaymentType(string $sPaymentId): bool
    {
        return in_array($sPaymentId, self::$pcpPaymentTypes, true);
    }

    public static function isPcpInstallment(string $sPaymentId): bool
    {
        return $sPaymentId === 'pcpsecureinstallment';
    }

    public static function getPcpPaymentTypes(): array
    {
        return self::$pcpPaymentTypes;
    }
}