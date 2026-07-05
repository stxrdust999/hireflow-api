<?php

namespace App\Enums;

enum JobOpeningEnum: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Closed    = 'closed';
}
