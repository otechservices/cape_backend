<?php

namespace App\Documentation\Model;

/**
 * Class Typeprime
 *
 * @OA\Schema(
 *     schema="Typeprime",
 *     title="Typeprime class",
 *     description="Typeprime class",
 * )
 */
class Typeprime
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