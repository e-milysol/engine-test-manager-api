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
        $items = $repo->findBy([], ['createdAt' => 'DESC']);

        $data = array_map(function (TestRequest $tr) {
            return $this->serializeTestRequest($tr);
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
            $this->serializeTestRequest($tr),
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'detail', methods: ['GET'])]
    public function detail(TestRequest $testRequest = null): JsonResponse
    {
        if (!$testRequest) {
            return $this->json(
                ['error' => 'TestRequest not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $this->json($this->serializeTestRequest($testRequest));
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
        TestRequestRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $testRequest = $repo->find($id);

        if (!$testRequest) {
            return $this->json(
                ['error' => 'TestRequest not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $payload = json_decode($request->getContent(), true) ?? [];

        if (isset($payload['title'])) {
            $title = trim((string) $payload['title']);
            if ($title === '') {
                return $this->json(
                    ['error' => 'Field "title" cannot be empty'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
            $testRequest->setTitle($title);
        }

        if (isset($payload['description'])) {
            $testRequest->setDescription(trim((string) $payload['description']));
        }

        if (isset($payload['priority'])) {
            $priority = (int) $payload['priority'];
            if ($priority < 1 || $priority > 5) {
                return $this->json(
                    ['error' => 'Field "priority" must be between 1 and 5'],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }
            $testRequest->setPriority($priority);
        }

        if (isset($payload['status'])) {
            $status = strtoupper(trim((string) $payload['status']));
            $allowedStatuses = ['NEW', 'IN_PROGRESS', 'DONE', 'CANCELLED'];

            if (!in_array($status, $allowedStatuses, true)) {
                return $this->json(
                    [
                        'error'   => 'Invalid status value',
                        'allowed' => $allowedStatuses,
                    ],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $testRequest->setStatus($status);
        }

        $testRequest->setUpdatedAt(new \DateTime());

        $em->flush();

        return $this->json($this->serializeTestRequest($testRequest));
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        TestRequestRepository $repo,
        EntityManagerInterface $em
    ): JsonResponse {
        $testRequest = $repo->find($id);

        if (!$testRequest) {
            // Idempotent delete: devolver 204 aunque no exista
            return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
        }

        $em->remove($testRequest);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    private function serializeTestRequest(TestRequest $tr): array
    {
        return [
            'id'        => $tr->getId(),
            'title'     => $tr->getTitle(),
            'description' => $tr->getDescription(),
            'status'    => $tr->getStatus(),
            'priority'  => $tr->getPriority(),
            'createdAt' => $tr->getCreatedAt()?->format('c'),
            'updatedAt' => $tr->getUpdatedAt()?->format('c'),
        ];
    }
}
