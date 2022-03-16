<?php

namespace App\Support\Enums;

/**
 * Descrição de clima e os seus códigos
 *
 * D_[código] = "[descrição do clima]"
 */
enum DayOfWeek: string
{
    case D_0 = 'Domingo';
    case D_1 = 'Segunda-feira';
    case D_2 = 'Terça-feira';
    case D_3 = 'Quarta-feira';
    case D_4 = 'Quinta-feira';
    case D_5 = 'Sexta-feira';
    case D_6 = 'Sábado';

    use EnumCodeable;
}
