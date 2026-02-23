<?php

namespace App\Enums;

enum EzOrderStatusEnum : string
{
    case PROCESSING = 'PROCESSING';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

}
