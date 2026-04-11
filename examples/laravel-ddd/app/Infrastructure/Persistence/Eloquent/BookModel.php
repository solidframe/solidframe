<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $title
 * @property string $author
 * @property string $isbn
 * @property string $status
 * @property string|null $borrower
 */
final class BookModel extends Model
{
    use HasUuids;

    protected $table = 'books';

    protected $fillable = [
        'id',
        'title',
        'author',
        'isbn',
        'status',
        'borrower',
    ];
}
