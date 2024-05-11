<?php

namespace App\EntityListener;

use App\Entity\Post;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Post::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Post::class)]
class PostEntityListener
{
    private ?SluggerInterface $slugger = null;
    
    public function __construct(SluggerInterface $slugger) 
    {
        $this->slugger = $slugger;
    }
    
    public function prePersist(Post $post, LifecycleEventArgs $event)
    {
        $post->computeSlug($this->slugger);
    }
    
    public function preUpdate(Post $post, LifecycleEventArgs $event)
    {
        $post->computeSlug($this->slugger);
    }
}