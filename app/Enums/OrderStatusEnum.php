<?php

namespace App\Enums;

enum OrderStatusEnum : string
{
    case PROCESSING = 'PROCESSING';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

}
