<?php

namespace App\Controller;

use App\Entity\Post;
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
        
        $form = $this->createForm(PostType::class, $post);
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
    public function show(Post $post): Response
    {
        return $this->render('post/show.html.twig', ['post' => $post]);
    }
    
    #[Route('/post/delete/{id}', name: 'post-delete')]
    public function delete(Post $post): RedirectResponse
    {
        $this->entityManager->remove($post);
        $this->entityManager->flush();
        
        return $this->redirectToRoute('post-index');
    }
}
