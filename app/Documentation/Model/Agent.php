<?php

namespace App\Documentation\Model;

/**
 * Class Agent
 *
 * @OA\Schema(
 *     schema="Agent",
 *     title="Agent class",
 *     description="Agent class",
 * )
 */
class Agent
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