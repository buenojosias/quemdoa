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

    public function illustration(): string
    {
        return match ($this) {
            self::FOODS => 'foods.png',
            self::DRINKS => 'drinks.png',
            self::HORTIFRUTI => 'hortifruti.png',
            self::MEATS => 'meats.png',
            self::DISPOSABLES => 'disposables.png',
            self::HYGIENE => 'hygiene.png',
            self::CLEANING => 'cleaning.png',
            self::DECORATION => 'decoration.png',
            self::MONEY => 'money.png',
            self::OTHERS => 'others.png',
        };
    }
}
