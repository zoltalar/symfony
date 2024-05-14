<?php

namespace App\EntityListener;

use App\Entity\Post;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsEntityListener(event: Events::prePersist, entity: Post::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Post::class)]
#[AsEntityListener(event: Events::preRemove, entity: Post::class)]
class PostEntityListener
{
    private ?SluggerInterface $slugger = null;
    private ?string $photoDir = null;
    
    public function __construct(
        SluggerInterface $slugger,
        #[Autowire('%photo_dir%')] string $photoDir
    ) 
    {
        $this->slugger = $slugger;
        $this->photoDir = $photoDir;
    }
    
    public function prePersist(Post $post, LifecycleEventArgs $event)
    {
        $post->computeSlug($this->slugger);
    }
    
    public function preUpdate(Post $post, LifecycleEventArgs $event)
    {
        $post->computeSlug($this->slugger);
    }
    
    public function preRemove(Post $post, LifecycleEventArgs $event)
    {
        $post->deletePhoto($this->photoDir);
        $objectManager = $event->getObjectManager();
        
        foreach ($post->getComments() as $comment) {
            $objectManager->remove($comment);
            $objectManager->flush();
        }
    }
}