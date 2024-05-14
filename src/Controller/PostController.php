<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Form\PostType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PostController extends AbstractController
{    
    private ?EntityManagerInterface $entityManager = null;
    
    public function __construct(EntityManagerInterface $entityManager) 
    {
        $this->entityManager = $entityManager;
    }
    
    #[Route('/post', name: 'post-index')]
    public function index(): Response
    {
        $posts = $this
            ->entityManager
            ->getRepository(Post::class)
            ->findAll();
        
        return $this->render('post/index.html.twig', ['posts' => $posts]);
    }
    
    #[Route('/post/add', name: 'post-add')]
    public function add(Request $request, #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        $post = new Post();
        
        $form = $this->createForm(PostType::class, $post, [
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            if ($photo = $form['photo']->getData()) {
                $file = implode('.', [
                    bin2hex(random_bytes(8)),
                    $photo->guessExtension()
                ]);
                
                $photo->move($photoDir, $file);
                $post->setPhoto($file);
            }
            
            $this->entityManager->persist($post);
            $this->entityManager->flush();
            
            return $this->redirectToRoute('post-index');
        }
        
        return $this->render('post/add.html.twig', ['form' => $form]);
    }
    
    #[Route('/post/{slug}', name: 'post-show')]
    public function show(Request $request, string $slug): Response
    {
        $post = $this
            ->entityManager
            ->getRepository(Post::class)
            ->findOneBy(['slug' => $slug]);
        
        if ( ! $post) {
            return $this->redirectToRoute('post-index');
        }
        
        $comment = new Comment();
        
        $form = $this->createForm(CommentType::class, $comment, [
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid())
        {
            $comment->setPost($post);
            
            $this->entityManager->persist($comment);
            $this->entityManager->flush();
            
            return $this->redirectToRoute('post-show', [
                'slug' => $post->getSlug()
            ]);
        }
        
        return $this->render('post/show.html.twig', [
            'post' => $post,
            'comments' => $post->getComments(),
            'form' => $form
        ]);
    }
    
    #[Route('/post/delete/{id}', name: 'post-delete')]
    public function delete(Post $post): RedirectResponse
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        
        return $this->redirectToRoute('post-index');
    }
}
