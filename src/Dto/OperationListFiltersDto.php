<?php

/**
 * Operation list filters DTO.
 */

namespace App\Dto;

use App\Entity\Category;
use App\Entity\Tag;

/**
 * Class OperationListFiltersDto.
 */
class OperationListFiltersDto
{
    /**
     * Constructor.
     *
     * @param Category|null $category Category entity
     * @param Tag|null      $tag      Tag entity
     */
    public function __construct(public readonly ?Category $category, public readonly ?Tag $tag)
    {
    }
}
