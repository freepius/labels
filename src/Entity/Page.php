<?php

namespace App\Entity;

class Page
{
    use Trait\IdTrait;
    use Trait\DataTrait;

    public function __construct(string $id, array $data)
    {
        $this->setId($id)->setData($data);
    }

    /**
     * Get the template name to use to render the page.
     */
    public function getTemplateName(): string
    {
        return sprintf(
            'page/%s.html.twig',
            $this->template ?? $this->id
        );
    }

    /**
     * Get the CSS classes to use to render the main page element.
     */
    public function getCssClasses(): string
    {
        $default = fn () => [$this->id];

        return sprintf(
            'page %s',
            implode(' ', $this->getDataValue('cssClasses', $default()))
        );
    }
}
