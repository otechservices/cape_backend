<?php

namespace App\Documentation\Model;

/**
 * Class PrimeStatut
 *
 * @OA\Schema(
 *     schema="PrimeStatut",
 *     title="PrimeStatut class",
 *     description="PrimeStatut class",
 * )
 */
class PrimeStatut
{
    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $statut_id;

    /**
     * @OA\Property()
     *
     * @var int
     */
    private $prime_id;
}