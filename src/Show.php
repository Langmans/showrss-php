<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Show
 * @ORM\Entity()
 * @ORM\Table(name="shows")
 */
class Show
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $year;
}