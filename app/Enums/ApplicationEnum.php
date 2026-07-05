<?php

namespace App\Enums;

enum ApplicationEnum: string
{
    // 	pending, in_progress, approved, rejected, withdrawn 
    case Pending     = 'pending';
    case InProgress = 'in_progress';
    case Approved    = 'approved';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
}
