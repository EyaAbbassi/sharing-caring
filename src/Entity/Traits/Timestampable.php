<?php 
namespace App\Entity\Traits;

trait Timestampable
{
    /**
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", options={"default":"CURRENT_TIMESTAMP"})
     */
    private $updatedAt;

    public function getCreatedAt() : ?\DateTimeInterface 
    {
        return $this->createdAt;
    }
    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdateddAt(): ?\DateTimeInterface 
    {
        return $this->updatedAt;
    }
    public function setUpdateddAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    PUBLIC FUNCTION updateTimestamps()
    {   
        if( $this->getCreatedAt()=== null)
        {
            $this->setCreatedAt(new \DateTimeImmutable);
        }
        $this->setUpdateddAt(new \DateTimeImmutable);
    }
    
}