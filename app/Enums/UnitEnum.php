<?php

namespace App\Enums;

enum UnitEnum: string
{
    case UNIT = 'UNIT';
    case DOZEN = 'DOZEN';
    case HUNDRED = 'HUNDRED';
    case BOX = 'BOX';
    case PACK = 'PACK';
    case G = 'G';
    case KG = 'KG';
    case ML = 'ML';
    case L = 'L';
    case MONEY = 'REAL';

    public function label(): string
    {
        return match ($this) {
            self::UNIT => 'unidades',
            self::DOZEN => 'dúzias',
            self::HUNDRED => 'centos',
            self::BOX => 'caixas',
            self::PACK => 'pacotes',
            self::G => 'gramas',
            self::KG => 'kilogramas',
            self::ML => 'mililitros',
            self::L => 'litros',
            self::MONEY => 'reais',
        };
    }

    public function abbreviation(): string
    {
        return match ($this) {
            self::UNIT => 'un',
            self::DOZEN => 'dz',
            self::HUNDRED => 'ct',
            self::BOX => 'cx',
            self::PACK => 'pct',
            self::G => 'g',
            self::KG => 'kg',
            self::ML => 'ml',
            self::L => 'l',
            self::MONEY => 'R$',
        };
    }

}
