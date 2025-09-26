<?php

namespace App\Documentation\Model;

/**
 * Class JoursFeries
 *
 * @OA\Schema(
 *     schema="JoursFeries",
 *     title="JoursFeries class",
 *     description="JoursFeries class",
 * )
 */
class JoursFeries
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
     * @var date
     */
    private $date;
}