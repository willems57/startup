<?php

namespace App\Controller;


use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class ContactController extends AbstractController
{

    #[Route('/api/contacts', methods: ['POST'])]
    public function addContact(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $contact = new Contact();
        $contact->setName($data['name']);
        $contact->setMail($data['mail']);
        $contact->setMessage($data['message']);
        $contact->setDate(new \DateTime($data['date']));

        $em->persist($contact);
        $em->flush();

        return $this->json(['status' => 'Contact created'], 201);
    }


    #[Route('/api/contacts', methods: ['GET'])]
    public function getAllContacts(ContactRepository $contactRepository): JsonResponse
    {
        $contacts = $contactRepository->findAll();

        $data = array_map(fn(Contact $contact) => [
            'id' => $contact->getId(),
            'name' => $contact->getName(),
            'mail' => $contact->getMail(),
            'message' => $contact->getMessage(),
            'date' => $contact->getDate()->format('Y-m-d H:i:s'),
        ], $contacts);

        return $this->json($data);
    }

    
    #[Route('/api/contacts/{id}', methods: ['DELETE'])]
    public function deleteContact(int $id, ContactRepository $contactRepository, EntityManagerInterface $em): JsonResponse
    {
        $contact = $contactRepository->find($id);

        if (!$contact) {
            return $this->json(['error' => 'Contact not found'], 404);
        }

        $em->remove($contact);
        $em->flush();

        return $this->json(['status' => 'Contact deleted'], 200);
    }
}