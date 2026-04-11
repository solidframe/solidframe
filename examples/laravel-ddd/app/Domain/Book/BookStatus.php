<?php

declare(strict_types=1);

namespace App\Domain\Book;

enum BookStatus: string
{
    case Available = 'available';
    case Borrowed = 'borrowed';
}
