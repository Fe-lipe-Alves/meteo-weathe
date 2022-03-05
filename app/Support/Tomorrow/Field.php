<?php

namespace App\Support\Tomorrow;

use App\Support\Enums\FieldComparison;

class Field
{
    public function __construct(
        public string          $name,
        public FieldComparison $comparation,
    )
    {  }
}
