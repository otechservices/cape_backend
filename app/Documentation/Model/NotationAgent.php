<?php

namespace App\Documentation\Model;

/**
 * Class NotationAgent
 *
 * @OA\Schema(
 *     schema="NotationAgent",
 *     title="NotationAgent class",
 *     description="NotationAgent class",
 * )
 */
class NotationAgent
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
     * @var int
     */
    private $periode_id;

    /**
     * @OA\Property(
     *     format="int64",
     * )
     *
     * @var string
     */
    private $note;
 
}