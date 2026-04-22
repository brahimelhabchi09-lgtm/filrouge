<?php

namespace App\Domain\Report\ValueObject;

enum Priority: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;
}
