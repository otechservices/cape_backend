<?php

namespace App\Documentation\Model;

/**
 * Class Statut
 *
 * @OA\Schema(
 *     schema="Statut",
 *     title="Statut class",
 *     description="Statut class",
 * )
 */
class Statut
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