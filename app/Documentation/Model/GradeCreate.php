<?php

namespace App\Documentation\Model;

/**
 * Class GradeCreate
 *
 * @OA\Schema(
 *     schema="GradeCreate",
 *     title="GradeCreate class",
 *     description="GradeCreate class",
 * )
 */
class GradeCreate
{
    /**
     * @OA\Property()
     *
     * @var string
     */
    private $libelle;
}
