<?php

namespace App\Controller;

use App\Entity\TestRequest;
use App\Entity\TestRun;
use App\Repository\TestRunRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TestRunController extends AbstractController
{
    /**
     * List all runs for a given TestRequest
     */
    #[Route('/api/test-requests/{id}/runs', name: 'api_test_runs_list_for_request', methods: ['GET'])]
    public function listForRequest(
        TestRequest $testRequest = null,
        TestRunRepository $runRepo
    ): JsonResponse {
        if (!$testRequest) {
            return $this->json(
                ['error' => 'TestRequest not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $runs = $runRepo->findBy(
            ['testRequest' => $testRequest],
            ['startedAt' => 'DESC']
        );

        $data = array_map([$this, 'serializeRun'], $runs);

        return $this->json($data);
    }

    /**
     * Create a new run for a given TestRequest
     */
    #[Route('/api/test-requests/{id}/runs', name: 'api_test_runs_create_for_request', methods: ['POST'])]
    public function createForRequest(
        TestRequest $testRequest = null,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        if (!$testRequest) {
            return $this->json(
                ['error' => 'TestRequest not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $payload = json_decode($request->getContent(), true) ?? [];

        $now = new \DateTimeImmutable();

        $run = new TestRun();
        $run->setTestRequest($testRequest);
        $run->setStartedAt($now);
		$run->setCreatedAt($now);
        $run->setFinishedAt(null);
        $run->setResult('PENDING');
        $run->setNotes(trim((string) ($payload['notes'] ?? '')));

        $em->persist($run);
        $em->flush();

        return $this->json(
            $this->serializeRun($run),
            JsonResponse::HTTP_CREATED
        );
    }

    /**
     * Get details of a single TestRun
     */
    #[Route('/api/test-runs/{id}', name: 'api_test_runs_detail', methods: ['GET'])]
    public function detail(TestRun $run = null): JsonResponse
    {
        if (!$run) {
            return $this->json(
                ['error' => 'TestRun not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        return $this->json($this->serializeRun($run));
    }

    /**
     * Update result / notes / finishedAt of a TestRun
     */
    #[Route('/api/test-runs/{id}', name: 'api_test_runs_update', methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
        TestRunRepository $runRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $run = $runRepo->find($id);

        if (!$run) {
            return $this->json(
                ['error' => 'TestRun not found'],
                JsonResponse::HTTP_NOT_FOUND
            );
        }

        $payload = json_decode($request->getContent(), true) ?? [];

        if (isset($payload['result'])) {
            $result = strtoupper(trim((string) $payload['result']));
            $allowed = ['PENDING', 'PASSED', 'FAILED', 'CANCELLED'];

            if (!in_array($result, $allowed, true)) {
                return $this->json(
                    [
                        'error'   => 'Invalid result value',
                        'allowed' => $allowed,
                    ],
                    JsonResponse::HTTP_BAD_REQUEST
                );
            }

            $run->setResult($result);
        }

        if (isset($payload['notes'])) {
            $run->setNotes(trim((string) $payload['notes']));
        }

        if (array_key_exists('finishedAt', $payload)) {
            if ($payload['finishedAt'] === null) {
                $run->setFinishedAt(null);
            } else {
                try {
                    $finishedAt = new \DateTime($payload['finishedAt']);
                    $run->setFinishedAt($finishedAt);
                } catch (\Exception $e) {
                    return $this->json(
                        ['error' => 'Invalid finishedAt datetime format'],
                        JsonResponse::HTTP_BAD_REQUEST
                    );
                }
            }
        }

        $em->flush();

        return $this->json($this->serializeRun($run));
    }

    private function serializeRun(TestRun $run): array
    {
        return [
            'id'          => $run->getId(),
            'testRequest' => $run->getTestRequest()?->getId(),
            'result'      => $run->getResult(),
            'notes'       => $run->getNotes(),
            'startedAt'   => $run->getStartedAt()?->format('c'),
            'finishedAt'  => $run->getFinishedAt()?->format('c'),
        ];
    }
}
