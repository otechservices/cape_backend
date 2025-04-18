<?php

namespace App\Documentation\Model;

/**
 * Class Hsup
 *
 * @OA\Schema(
 *     schema="Hsup",
 *     title="Hsup class",
 *     description="Hsup class",
 * )
 */
class Hsup
{
    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var int
     */
    private $agent_id;

    
    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property()
     *
     * @var date
     */
    private $nbHeure;
}