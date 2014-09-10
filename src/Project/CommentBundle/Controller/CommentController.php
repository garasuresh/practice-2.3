<?php

namespace Project\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\ORM\EntityManager;

use Project\CommentBundle\Entity\Comment;
use Bp\CommonBundle\Entity\User;

class CommentController extends Controller
{
    public function addCommentAction(Request $request){
        $comment = new Comment();
        $comments = null;
        $form = $this->createFormBuilder($comment)
            ->setAction($this->generateUrl('project_comment_add'))
            ->setMethod('GET')
            ->add('description', 'text')
            ->add('save', 'submit', array('label' => 'Post'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isValid()){
            /** @var  $user User */
            $user = $this->get('security.context')->getToken()->getUser();

            /** @var  $em EntityManager */
            $em = $this->getDoctrine()->getManager();
            $comment->setUser($user);
            $em->persist($comment);
            $em->flush();

            $comments = $em->getRepository('ProjectCommentBundle:Comment')->findAll();
        }

        return $this->render('ProjectCommentBundle:Comment:new.html.twig', array(
            'form' => $form->createView(),
            'comments' => $comments
        ));
    }
}
