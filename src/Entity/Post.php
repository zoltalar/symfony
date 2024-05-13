<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[UniqueEntity('slug')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(length: 255, nullable: true)]    
    private ?string $slug = null;
    
    #[ORM\Column(type: 'text')]
    private ?string $content = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
    
    public function computeSlug(SluggerInterface $slugger): static
    {
        if ( ! $this->slug) {
            $this->slug = (string) $slugger->slug($this->getTitle())->lower();
        }
        
        return $this;
    }
    
    public function getContent(): ?string
    {
        return $this->content;
    }
    
    public function setContent(string $content): static
    {
        $this->content = $content;
        
        return $this;
    }
    
    public function getPhoto(): ?string
    {
        return $this->photo;
    }
    
    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;
        
        return $this;
    }
    
    public function deletePhoto(string $dir): static
    {
        $photo = $this->photo;
        
        if ( ! empty($photo))
        {
            $filesystem = new Filesystem();
            $filesystem->remove($dir . DIRECTORY_SEPARATOR . $photo);
        }
        
        return $this;
    }
}
