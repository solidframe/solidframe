<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property string|null $description
 * @property string $status
 */
final class ProjectModel extends Model
{
    use HasUuids;

    protected $table = 'projects';

    protected $fillable = [
        'id',
        'name',
        'description',
        'status',
    ];
}
