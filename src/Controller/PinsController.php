<?php

namespace App\Controller;

use App\Entity\Pin;
use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Component\Mime\Address;
use App\Form\PinType;
use App\Repository\PinRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bridge\Twig\Mime\WrappedTemplatedEmail;

class PinsController extends AbstractController
{
/**
 * @Route("/", name= "app_home", methods="GET")
 */
public function index(PinRepository $pinRepository): Response
{
    $pins = $pinRepository->findBy([], ['createdAt' => 'DESC']);

    return $this->render('pins/index.html.twig', compact('pins'));
}
    
    /**
     * @Route("/pins/create", name="app_pins_create", methods="GET|POST")
     * @IsGranted("PIN_CREATE")
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
     * @Route("/pins/{id<[0-9]+>}", name= "app_pins_show", methods="GET|POST")
     */
    public function show(Pin $pin, Request $request,EntityManagerInterface $em,MailerInterface $mailer ): Response
    {
        $contact = new Contact();
     
        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $email = (new TemplatedEmail())
            ->from($contact->getEmail())
            ->to(new Address(
                $this->getParameter('app.mail_from_address'),
                $this->getParameter('app.mail_from_name'),
            ))        
            ->subject('demande!')
            ->htmlTemplate('notification/not.html.twig')
            ->context([
                'firstname' => $contact->getFirstName(),
                'lastname' => $contact->getLastName(),
                'mail' => $contact->getEmail(),
                'phone' => $contact->getPhone(),
                'message' => $contact->getMessage()
            ])
            ;

        $mailer->send($email);

            $this->addFlash('success', 'votre message a été bien envoyé!');
            return $this->render('pins/show.html.twig', [
                'contact'=>$contact,
                'pin' => $pin,
                'form' => $form->createView()]
            );            
        }

        return $this->render('pins/show.html.twig', [
            'pin' => $pin,
            'form' => $form->createView()]
        );
    }

    
    
    /**
     * @Route("/pins/{id<[0-9]+>}/edit", name="app_pins_edit", methods={"GET", "PUT"})
     * @IsGranted("PIN_MANAGE", subject="pin")
     */
    public function edit(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(PinType::class, $pin, [
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Pin successfully updated!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('pins/edit.html.twig', [
            'pin' => $pin,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/pins/{id<[0-9]+>}", name="app_pins_delete", methods="DELETE")
     */
    public function delete(Request $request, Pin $pin, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('PIN_MANAGE', $pin);

        if ($this->isCsrfTokenValid('pin_deletion_' . $pin->getId(), $request->request->get('csrf_token'))) {
            $em->remove($pin);
            $em->flush();

            $this->addFlash('info', 'Pin successfully deleted!');
        }

        return $this->redirectToRoute('app_home');
    }
}
