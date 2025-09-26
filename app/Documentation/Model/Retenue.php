<?php

namespace App\Documentation\Model;

/**
 * Class Retenue
 *
 * @OA\Schema(
 *     schema="Retenue",
 *     title="Retenue class",
 *     description="Retenue class",
 * )
 */
class Retenue
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

     /**
     * @OA\Property()
     *
     * @var string
     */
    private $observation;
}