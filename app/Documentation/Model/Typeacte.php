<?php

namespace App\Documentation\Model;

/**
 * Class Typeacte
 *
 * @OA\Schema(
 *     schema="Typeacte",
 *     title="Typeacte class",
 *     description="Typeacte class",
 * )
 */
class Typeacte
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