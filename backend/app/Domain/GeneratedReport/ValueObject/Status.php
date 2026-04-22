<?php

namespace App\Domain\Report\ValueObject;

enum Status: string
{
    case PENDING = "PENDING";
    case RESOLVED = "RESOLVED";
    case REJECTED = "REJECTED";
}
