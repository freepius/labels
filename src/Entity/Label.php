<?php

namespace App\Entity;

class Label
{
    use Trait\CompoundIdTrait;
    use Trait\DataTrait;

    public function __construct(string $id, array $data)
    {
        $this->setId($id)->setData($data);
    }

    /**
     * Determines if the label has a sub-title
     * (either an explicit one or a slice of the compound ID).
     */
    public function hasSubTitle(): bool
    {
        // To have a sub-title, the compound ID must have at least 2 parts
        // (first one is the parent ID, other ones form the sub-title).
        return (bool) ($this->subTitle ?? $this->getIdSub(1));
    }

    /**
     * Get the sub-title if any.
     */
    public function getSubTitle(): string
    {
        return $this->hasSubTitle()
            ? $this->subTitle ?? $this->getIdSlice(1)
            : ''
        ;
    }

    /**
     * Get the parent identifier if any.
     * The parent identifier is defined either explicitly, or by the first part of the compound ID.
     */
    public function getParentId(): ?string
    {
        return $this->parent ?? ($this->getIdSub(1) ? $this->getIdSub(0) : null);
    }

    /**
     * Get the template name to use to render the label.
     */
    public function getTemplateName(): string
    {
        return sprintf(
            'label/%s.html.twig',
            $this->template ?? $this->getParentId() ?? $this->id
        );
    }

    /**
     * Get the CSS classes to use to render the main label element.
     */
    public function getCssClasses(): string
    {
        $default = array_unique([
            $this->getParentId(),
            $this->getIdSlice(0, 1),
            $this->getIdSlice(1),
            $this->id,
        ]);

        return sprintf(
            'label %s',
            implode(' ', $this->cssClasses ?? $default)
        );
    }
}
