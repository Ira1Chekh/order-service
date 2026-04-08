<?php

namespace App\Enum;

enum OrderStatus: string
{
    case Processing = 'Processing';
    case Success = 'Success';
}
