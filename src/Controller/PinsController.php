<?php

namespace App\Controller;

use App\Repository\PinRepository;
use App\Entity\Pin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormRegistryInterface;
use App\Form\PinType;


class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home", methods="GET" )
     */
    public function index(PinRepository $pinRepository)
    {
        $pins= $pinRepository->findBy([],['createdAt' => 'DESC']);
        return $this->render('pins/index.html.twig', compact('pins'));
    }

/**
     * @Route("/pins/create", name="app_pins_create", methods="GET|POST")
     */

    public function create(Request $request, EntityManagerInterface $em, UserRepository $userRepo): Response
    {
        $pin = new Pin;

        $form = $this->createForm(PinType::class, $pin);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pin->setUser($this->getUser());
            $em->persist($pin);
            $em->flush();

            $this->addFlash('success', 'Pin successfully created!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/create.html.twig', [
            'form' => $form->createView()
        ]);
    }



/**
 * @Route("/pins/{id<[0-9]+>}", name="app_pins_show",methods="GET" )
 */
 public function show(Pin $pin): Response
 {
     return $this->render('pins/show.html.twig', compact ('pin'));
 }

 /**
  *  @Route("/pins/{id<[0-9]+>}/edit", name="app_pins_edit",methods="GET|PUT")
  */
  public function edit(Request $request ,EntityManagerInterface $em,Pin $pin): response
  { 
    
    $form= $this->createForm(PinType ::class,$pin, ['method'=>'PUT']);

         $form->handleRequest($request);

          if ($form->isSubmitted() && $form ->isValid())
          {    
                $data=$form->getData();
                $em->flush();
                $this-> addFlash ('success','Article successfully updated');

                return $this->redirectToRoute("app_home");
          }
      return $this->render('pins/edit.html.twig',[
        'pin' =>$pin, 
        'form'=>$form->createView()]);
  }

  /**
  *  @Route("/pins/{id<[0-9]+>}", name="app_pins_delete",methods="DELETE")
  */ 

  public function delete(EntityManagerInterface $em,Pin $pin): response
  { 
    
    $em->remove($pin);
    $em->flush();
    $this-> addFlash ('info','Article successfully deleted');

    return $this->redirectToRoute("app_home");
  }

}