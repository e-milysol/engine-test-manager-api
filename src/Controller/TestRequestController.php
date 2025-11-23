<?php

namespace App\Controller;

use App\Entity\TestRequest;
use App\Repository\TestRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/test-requests', name: 'api_test_requests_')]
class TestRequestController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(TestRequestRepository $repo): JsonResponse
    {
        $items = $repo->findAll();

        $data = array_map(function (TestRequest $tr) {
            return [
                'id'        => $tr->getId(),
                'title'     => $tr->getTitle(),
                'status'    => $tr->getStatus(),
                'priority'  => $tr->getPriority(),
                'createdAt' => $tr->getCreatedAt()?->format('c'),
            ];
        }, $items);

        return $this->json($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true) ?? [];

        $title = trim($payload['title'] ?? '');
        $description = trim($payload['description'] ?? '');
        $priority = isset($payload['priority']) ? (int) $payload['priority'] : 3;

        if ($title === '') {
            return $this->json(
                ['error' => 'Field "title" is required'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        if ($priority < 1 || $priority > 5) {
            $priority = 3;
        }

        $now = new \DateTimeImmutable();

        $tr = new TestRequest();
        $tr->setTitle($title);
        $tr->setDescription($description);
        $tr->setStatus('NEW');
        $tr->setPriority($priority);
        $tr->setCreatedAt($now);
        $tr->setUpdatedAt(new \DateTime());

        $em->persist($tr);
        $em->flush();

        return $this->json(
            [
                'id'        => $tr->getId(),
                'title'     => $tr->getTitle(),
                'status'    => $tr->getStatus(),
                'priority'  => $tr->getPriority(),
                'createdAt' => $tr->getCreatedAt()?->format('c'),
            ],
            JsonResponse::HTTP_CREATED
        );
    }
}
