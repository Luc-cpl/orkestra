<?php

namespace Tests\Unit\Services\Http\Entities;

enum TestStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
