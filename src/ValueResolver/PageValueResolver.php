<?php

namespace App\ValueResolver;

use App\Entity\Page;
use App\Repository\PageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class PageValueResolver implements ValueResolverInterface
{
    public function __construct(protected PageRepository $repository)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (Page::class !== $argument->getType()) {
            return [];
        }

        $id = $request->attributes->get('page_id') ?? $request->attributes->get('id');

        if (null === $id) {
            return [];
        }

        yield $this->repository->find($id);
    }
}
