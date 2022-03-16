<?php

namespace App\Support\Enums;

use phpDocumentor\Reflection\Types\Integer;

enum Period: Integer
{
    case Day = 1;
    case Night = 2;
}
