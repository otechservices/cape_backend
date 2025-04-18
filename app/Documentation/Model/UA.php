<?php

namespace App\Documentation\Model;

/**
 * Class UA
 *
 * @OA\Schema(
 *     schema="UA",
 *     title="UA class",
 *     description="UA class",
 * )
 */
class UA
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