<?php

namespace App\ValueResolver;

use App\Entity\Label;
use App\Repository\LabelRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class LabelValueResolver implements ValueResolverInterface
{
    public function __construct(protected LabelRepository $repository)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Label::class !== $argument->getType()) {
            return [];
        }

        $id = $request->attributes->get('label_id') ?? $request->attributes->get('id');

        if (null === $id) {
            return [];
        }

        yield $this->repository->find($id);
    }
}
