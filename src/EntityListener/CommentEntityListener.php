<?php

namespace App\EntityListener;

use App\Entity\Comment;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::prePersist, entity: Comment::class)]
class CommentEntityListener
{    
    public function prePersist(Comment $comment, LifecycleEventArgs $event)
    {
        $comment->setCreatedAt(new \DateTimeImmutable());
    }
}