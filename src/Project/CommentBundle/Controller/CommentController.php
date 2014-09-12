<?php

namespace Project\CommentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

use Doctrine\ORM\EntityManager;

use Bp\CommonBundle\Entity\User;

use Project\CommentBundle\Entity\Comment;
use Project\CommentBundle\Form\CommentType;

class CommentController extends Controller
{
    /**
     * Create comment
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createCommentAction(Request $request){
        /** @var  $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $comment = new Comment();
        $form  = $this->createCreateForm($comment);
        $form->handleRequest($request);

        if($form->isValid()){
            /** @var  $user User */
            $user = $this->get('security.context')->getToken()->getUser();

            $comment->setUser($user);
            $em->persist($comment);
            $em->flush();

            // Creating acl
            $aclProvider = $this->get('security.acl.provider');
            $objIdentity = ObjectIdentity::fromDomainObject($comment);
            $acl = $aclProvider->createAcl($objIdentity);

            // retrieving the security identity of the currently logged-in user
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            // grant owner access
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);

            return $this->redirect($this->generateUrl('project_comment_new'));
        }

        $comments = $this->getComments();
        return $this->render('ProjectCommentBundle:Comment:new.html.twig', array(
            'form' => $form->createView(),
            'comments' => $comments
        ));
    }
    /**
     * Render form for post comment
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newCommentAction(){
        $entity = new Comment();
        $form = $this->createCreateForm($entity);

        $comments = $this->getComments();
        return $this->render('ProjectCommentBundle:Comment:new.html.twig',array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'comments' => $comments
        ));
    }


    /**
     * Creating form
     *
     * @param Comment $entity
     * @return \Symfony\Component\Form\Form
     */
    public function createCreateForm(Comment $entity){
        $form = $this->createForm(new CommentType(), $entity, array(
            'action' => $this->generateUrl('project_comment_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Add'));

        return $form;
    }

    /**
     * @return array |  all comments
     */
    public function getComments(){
        /** @var  $em EntityManager */
        $em = $this->getDoctrine()->getManager();
        $comments = $em->getRepository('ProjectCommentBundle:Comment')->findAll();

        return $comments;
    }
}
