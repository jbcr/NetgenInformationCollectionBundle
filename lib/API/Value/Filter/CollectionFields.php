<?php

declare(strict_types=1);

namespace Netgen\InformationCollection\API\Value\Filter;

final class CollectionFields
{
    /**
     * @var int
     */
    protected $contentId;

    /**
     * @var int
     */
    protected $collectionId;

    /**
     * @var array
     */
    protected $fields;

    public function __construct(int $contentId, int $collectionId, array $fields)
    {
        $this->contentId = $contentId;
        $this->collectionId = $collectionId;
        $this->fields = $fields;
    }

    /**
     * @return int
     */
    public function getContentId(): int
    {
        return $this->contentId;
    }

    /**
     * @return int
     */
    public function getCollectionId(): int
    {
        return $this->collectionId;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
