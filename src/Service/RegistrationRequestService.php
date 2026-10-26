<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\Pdo\PdoCategoryRepository;
use App\Repository\Pdo\PdoRegistrationRequestRepository;
use App\Repository\Pdo\PdoUserRepository;
use App\Support\Password;
use PDO;
use RuntimeException;
use Throwable;

/**
 * Public election requests and the administrative approval transaction.
 */
final class RegistrationRequestService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly PdoCategoryRepository $categories,
        private readonly PdoUserRepository $users,
        private readonly PdoRegistrationRequestRepository $requests,
    ) {}

    public function submit(
        string $fullName,
        string $email,
        string $category,
        int $maxVoter,
        string $adultVoter,
        string $expireTimestamp,
    ): int {
        return $this->requests->create(
            $fullName,
            $email,
            $category,
            $maxVoter,
            $adultVoter,
            $expireTimestamp,
        );
    }

    /**
     * Create the category and organiser account for a pending request.
     *
     * @return array{email:string,password:string,category_id:int}
     *
     * @throws RuntimeException when the request, expiry, or database write fails.
     */
    public function approve(int $requestId): array
    {
        $request = $this->requests->findPending($requestId);
        if ($request === null) {
            throw new RuntimeException('Request not found or already processed.');
        }

        $expiresAt = strtotime((string) ($request['expire_timestamp'] ?? ''));
        if ($expiresAt === false || $expiresAt <= time()) {
            throw new RuntimeException('The requested expiry time is no longer valid.');
        }

        $categoryName = trim((string) ($request['election_category'] ?? ''));
        $fullName     = trim((string) ($request['full_name'] ?? ''));
        $email        = trim((string) ($request['email'] ?? ''));
        $maxVoter     = max(1, (int) ($request['max_voter'] ?? 0));
        $adultVoter   = ((string) ($request['adult_voter'] ?? 'No')) === 'Yes' ? 'Yes' : 'No';
        $plainPassword = bin2hex(random_bytes(4));

        $this->pdo->beginTransaction();

        try {
            $categoryId = $this->categories->create(
                $categoryName,
                $maxVoter,
                $adultVoter,
                date('Y-m-d H:i:s', $expiresAt),
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s', $expiresAt),
            );

            $this->users->createOrganiser(
                $fullName,
                $email,
                Password::hash($plainPassword),
                $categoryId,
            );

            $this->requests->markApproved($requestId);
            $this->pdo->commit();

            return [
                'email'       => $email,
                'password'    => $plainPassword,
                'category_id' => $categoryId,
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }

    public function reject(int $requestId): bool
    {
        return $this->requests->reject($requestId);
    }
}
