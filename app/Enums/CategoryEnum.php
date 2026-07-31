<?php

namespace App\Enums;

enum CategoryEnum: string
{
    case FOODS = 'Comidas';
    case DRINKS = 'Bebidas';
    case HORTIFRUTI = 'Hortifruti';
    case MEATS = 'Carnes e frios';
    case SPICES = 'Temperos e condimentos';
    case CANDIES = 'Doces e sobremesas';
    case DISPOSABLES = 'Descartáveis';
    case HYGIENE = 'Higiene';
    case CLEANING = 'Limpeza';
    case DECORATION = 'Decoração';
    case MONEY = 'Dinheiro';
    case STATIONERY = 'Papelaria e escritório';
    case CLOTHES = 'Roupas e calçados';
    case ELECTRONICS = 'Eletrônicos';
    case TOYS = 'Brinquedos';
    case OTHERS = 'Outros';

    public function illustration(): string
    {
        return match ($this) {
            self::FOODS => 'foods.png',
            self::DRINKS => 'drinks.png',
            self::HORTIFRUTI => 'hortifruti.png',
            self::MEATS => 'meats.png',
            self::SPICES => 'spices.png',
            self::CANDIES => 'candies.png',
            self::DISPOSABLES => 'disposables.png',
            self::HYGIENE => 'hygiene.png',
            self::CLEANING => 'cleaning.png',
            self::DECORATION => 'decoration.png',
            self::MONEY => 'money.png',
            self::STATIONERY => 'stationery.png',
            self::CLOTHES => 'clothes.png',
            self::ELECTRONICS => 'electronics.png',
            self::TOYS => 'toys.png',
            self::OTHERS => 'others.png',
        };
    }
}
