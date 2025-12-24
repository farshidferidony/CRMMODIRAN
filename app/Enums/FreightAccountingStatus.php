<?php

namespace App\Enums;

enum FreightAccountingStatus: string
{
    case Pending  = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending  => 'در انتظار تأیید حسابداری',
            self::Approved => 'تأیید شده توسط حسابداری',
            self::Rejected => 'رد شده توسط حسابداری',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
