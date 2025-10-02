<?php

namespace App\Documentation\Model;

/**
 * Class Periode
 *
 * @OA\Schema(
 *     schema="Periode",
 *     title="Periode class",
 *     description="Periode class",
 * )
 */
class Periode
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
    private $periode;

      /**
     * @OA\Property()
     *
     * @var string
     */
    private $type_periode;

   
}