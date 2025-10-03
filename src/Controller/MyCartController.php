<?php

namespace App\Controller;

use App\Entity\UserOrder;
use App\Form\UserOrderType;
use App\Repository\UserOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/my/cart')]
final class MyCartController extends AbstractController
{
    #[Route(name: 'app_my_cart_index', methods: ['GET'])]
    public function index(UserOrderRepository $userOrderRepository): Response
    {
        return $this->render('my_cart/index.html.twig', [
            'user_orders' => $userOrderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_my_cart_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $userOrder = new UserOrder();
        $form = $this->createForm(UserOrderType::class, $userOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userOrder);
            $entityManager->flush();

            return $this->redirectToRoute('app_my_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('my_cart/new.html.twig', [
            'user_order' => $userOrder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_my_cart_show', methods: ['GET'])]
    public function show(UserOrder $userOrder): Response
    {
        return $this->render('my_cart/show.html.twig', [
            'user_order' => $userOrder,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_my_cart_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserOrder $userOrder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserOrderType::class, $userOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_my_cart_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('my_cart/edit.html.twig', [
            'user_order' => $userOrder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_my_cart_delete', methods: ['POST'])]
    public function delete(Request $request, UserOrder $userOrder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$userOrder->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($userOrder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_my_cart_index', [], Response::HTTP_SEE_OTHER);
    }
}
