<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Episode
 * @ORM\Entity()
 * @ORM\Table(name="episodes",uniqueConstraints={@ORM\UniqueConstraint(name="show_season_episode",columns={"show_id","season_number","episode_number"})})
 */
class Episode extends BaseEntity
{

    /**
     * @var int
     * @ORM\GeneratedValue()
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var int
     * @ORM\Column(type="integer",nullable=false)
     */
    protected $show_id;

    /**
     * @var int
     * @ORM\Column(type="integer",nullable=false)
     */
    protected $season_number;

    /**
     * @var int
     * @ORM\Column(type="integer",nullable=false)
     */
    protected $episode_number;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var Show
     * @ORM\ManyToOne(targetEntity="Show", inversedBy="episodes")
     */
    protected $show;

    /**
     * @return int
     */
    public function getEpisodeNumber()
    {
        return $this->episode_number;
    }

    /**
     * @param int $episode_number
     */
    public function setEpisodeNumber($episode_number)
    {
        $this->episode_number = $episode_number;
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
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getSeasonNumber()
    {
        return $this->season_number;
    }

    /**
     * @param int $season_number
     */
    public function setSeasonNumber($season_number)
    {
        $this->season_number = $season_number;
    }

    /**
     * @return int
     */
    public function getShowId()
    {
        return $this->show_id;
    }

    /**
     * @return Show
     */
    public function getShow()
    {
        return $this->show;
    }

    /**
     * @param Show $show
     */
    public function setShow(Show $show)
    {
        $old_show = $this->show;
        if ($old_show) {
            $old_show->removeEpisode($this);
        }
        if (!$show->hasEpisode($this)) {
            $show->addEpisode($this);
        }
        $this->show = $show;
        $this->show_id = $show->getId();
    }
}