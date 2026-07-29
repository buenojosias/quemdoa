<?php

namespace App\Enums;

enum CategoryEnum: string
{
    case FOODS = 'Comidas';
    case DRINKS = 'Bebidas';
    case HORTIFRUTI = 'Hortifruti';
    case MEATS = 'Carnes e frios';
    case DISPOSABLES = 'Descartáveis';
    case HYGIENE = 'Higiene';
    case CLEANING = 'Limpeza';
    case DECORATION = 'Decoração';
    case MONEY = 'Dinheiro';
    case OTHERS = 'Outros';

    public static function illustration(): array
    {
        return [
            self::FOODS->value => 'foods.png',
            self::DRINKS->value => 'drinks.png',
            self::HORTIFRUTI->value => 'hortifruti.png',
            self::MEATS->value => 'meats.png',
            self::DISPOSABLES->value => 'disposable.png',
            self::HYGIENE->value => 'hygiene.png',
            self::CLEANING->value => 'cleaning.png',
            self::DECORATION->value => 'decoration.png',
            self::MONEY->value => 'money.png',
            self::OTHERS->value => 'others.png',
        ];
    }
}
