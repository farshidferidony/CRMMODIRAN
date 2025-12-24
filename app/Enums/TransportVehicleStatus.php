<?php

namespace App\Enums;

enum TransportVehicleStatus: string
{
    case Searching   = 'searching';
    case Found       = 'found';
    case Loading     = 'loading';
    case Loaded      = 'loaded';
    case EnRoute     = 'en_route';
    case Arrived     = 'arrived';
    case Unloading   = 'unloading';
    case Unloaded    = 'unloaded';

    public function label(): string
    {
        return match ($this) {
            self::Searching => 'در حال جستجو',
            self::Found     => 'پیدا شد',
            self::Loading   => 'در حال بارگیری',
            self::Loaded    => 'بارگیری شده',
            self::EnRoute   => 'در مسیر',
            self::Arrived   => 'به مقصد رسیده',
            self::Unloading => 'در حال تخلیه',
            self::Unloaded  => 'تخلیه کامل شد',
        };
    }

    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
