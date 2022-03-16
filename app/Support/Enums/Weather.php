<?php

namespace App\Support\Enums;

/**
 * Descrição de clima e os seus códigos
 *
 * W_[código] = "[descrição do clima]"
 */
enum Weather: string
{
    case W_0    = '';
    case W_1000 = 'Limpo';
    case W_1001 = 'Nublado';
    case W_1100 = 'Predominantemente Limpo';
    case W_1101 = 'Parcialmente Nublado';
    case W_1102 = 'Predominantemente nublado';
    case W_2000 = 'Névoa';
    case W_2100 = 'Nevoeiro Leve';
    case W_3000 = 'Vento Leve';
    case W_3001 = 'Vento';
    case W_3002 = 'Vento Forte';
    case W_4000 = 'Chuvisco';
    case W_4001 = 'Chuva';
    case W_4200 = 'Chuva Leve';
    case W_4201 = 'Chuva Pesada';
    case W_5000 = 'Neve';
    case W_5001 = 'Rajadas';
    case W_5100 = 'Pouca Neve';
    case W_5101 = 'Neve Pesada';
    case W_6000 = 'Garoa Congelante';
    case W_6001 = 'Chuva Congelante';
    case W_6200 = 'Chuva Leve e Congelante';
    case W_6201 = 'Chuva Pesada e Congelante';
    case W_7000 = 'Pelotas de Gelo';
    case W_7101 = 'Pelotas de Gelo Pesado';
    case W_7102 = 'Pelotas de Gelo Leves';
    case W_8000 = 'Trovoadas';

    use EnumCodeable;
}
