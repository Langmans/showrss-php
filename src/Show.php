<?php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Show
 * @ORM\Entity()
 * @ORM\Table(name="shows",uniqueConstraints={@ORM\UniqueConstraint(name="show",columns={"name"})})
 */
class Show extends BaseEntity
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var Episode[]
     * @ORM\OneToMany(targetEntity="Episode", mappedBy="show", cascade={"persist"})
     */
    protected $episodes;

    public function __construct()
    {
        $this->episodes = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function addEpisode(Episode $episode)
    {
        if(!$this->hasEpisode($episode)) {
            $this->episodes[] = $episode;
        }
        return $this;
    }

    public function removeEpisode(Episode $episode)
    {
        return $this->episodes->removeElement($episode);
    }

    public function hasEpisode(Episode $episode)
    {
        return $this->episodes->contains($episode);
    }

    public function getEpisodes()
    {
        return $this->episodes;
    }
}