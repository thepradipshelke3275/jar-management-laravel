<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = "Pending";
    case COMPLETED = "Completed";
}
