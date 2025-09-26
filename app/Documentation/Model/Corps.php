<?php

namespace App\Documentation\Model;

/**
 * Class Corps
 *
 * @OA\Schema(
 *     schema="Corps",
 *     title="Corps class",
 *     description="Corps class",
 * )
 */
class Corps
{
    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var string
     */
    private $code;

    /**
     * @OA\Property()
     *
     * @var string
     */
    private $libelle;
}