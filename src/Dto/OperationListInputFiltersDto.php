<?php

/**
 * Operation list input filters DTO.
 */

namespace App\Dto;

/**
 * Class OperationListInputFiltersDto.
 */
class OperationListInputFiltersDto
{
    /**
     * Constructor.
     *
     * @param int|null $categoryId Category identifier
     * @param int|null $tagId      Tag identifier
     */
    public function __construct(public readonly ?int $categoryId = null, public readonly ?int $tagId = null)
    {
    }
}
