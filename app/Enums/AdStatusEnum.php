<?php

namespace App\Enums;

enum AdStatusEnum: string
{
    case Pending = "pending";
    case Active = "active";    
    case Rejected = "rejected";
}
