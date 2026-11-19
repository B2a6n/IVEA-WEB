<?php

declare(strict_types=1);

namespace App\Enums;

enum SyncStatus: string
{
    case Created = 'created';
    case Duplicate = 'duplicate';
    case Rejected = 'rejected';
}
