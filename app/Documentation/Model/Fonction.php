<?php

namespace App\Documentation\Model;

/**
 * Class Fonction
 *
 * @OA\Schema(
 *     schema="Fonction",
 *     title="Fonction class",
 *     description="Fonction class",
 * )
 */
class Fonction
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