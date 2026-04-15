<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class RequestValidator
{
    public function __construct(private ValidatorInterface $validator) {}

    /**
     * @return array<string, mixed>
     */
    public function validate(Request $request, Assert\Collection $constraints): array
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($request->getContent(), true) ?? [];

        $violations = $this->validator->validate($data, $constraints);

        if ($violations->count() > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $field = trim($violation->getPropertyPath(), '[]');
                $errors[$field][] = $violation->getMessage();
            }

            throw new UnprocessableEntityHttpException(
                json_encode(['errors' => $errors], JSON_THROW_ON_ERROR),
            );
        }

        return $data;
    }
}
