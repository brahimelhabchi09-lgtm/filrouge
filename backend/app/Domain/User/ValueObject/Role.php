<?php

namespace App\Domain\User\ValueObject;


enum Role: string
{
    case ADMIN = 'ADMIN';
    case STUDENT = 'STUDENT';
    case TEACHER = 'TEACHER';
    case BDE = 'BDE';
}