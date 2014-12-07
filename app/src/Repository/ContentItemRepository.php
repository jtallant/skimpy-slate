<?php namespace Skimpy\Repository;

use Symfony\Component\Finder\Finder;
use Skimpy\Contracts\ObjectRepository;
use Skimpy\Entity\ContentItem;
use Skimpy\Service\ContentFromFileCreator;
use Skimpy\Support\SortedCollection;

/**
 * Class ContentItemRepository
 *
 * @package Skimpy\Repository
 */
class ContentItemRepository implements ObjectRepository
{
    /**
     * @var ContentFromFileCreator
     */
    protected $contentFromFileCreator;

    /**
     * @var string
     */
    protected $contentPath;

    /**
     * Constructor
     *
     * @param Finder                 $finder
     * @param ContentFromFileCreator $contentFromFileCreator
     * @param                        $contentPath
     */
    public function __construct(
        ContentFromFileCreator $contentFromFileCreator,
        $contentPath
    ) {
        $this->contentFromFileCreator = $contentFromFileCreator;
        $this->contentPath = $contentPath;
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        $objects = [];
        foreach ($this->getContentFiles() as $f) {
            $content = $this->contentFromFileCreator->createContentObject($f);
            $objects[] = $content;
        }
        return $objects;
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $objects = [];
        foreach ($this->getContentFiles() as $f) {
            $content = $this->contentFromFileCreator->createContentObject($f);
            if ($this->contentContainsAllCriteria($content, $criteria)) {
                $objects[] = $content;
            }
        }
        return (new SortedCollection($objects, $orderBy, $limit, $offset))->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $objects = $this->findBy($criteria);
        if (empty($objects)) {
            return null;
        }
        return $objects[0];
    }

    /**
     * Returns true if the ContentItem contains all the criteria
     *
     * @param ContentItem $content
     * @param array       $criteria
     *
     * @return bool
     */
    protected function contentContainsAllCriteria(ContentItem $content, array $criteria)
    {
        foreach ($criteria as $prop => $value) {
            if (false === isset($content->$prop)) {
                return false;
            }

            if (false === $this->contentContainsCriteria($content->$prop, $value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns true if content value contains criteria value
     *
     * @param mixed $contentValue
     * @param mixed $criteriaValue
     *
     * @return bool
     */
    protected function contentContainsCriteria($contentValue, $criteriaValue)
    {
        $contentValue = (array) $contentValue;
        $criteriaValue = (array) $criteriaValue;
        return count(array_intersect($contentValue, $criteriaValue)) > 0;
    }

    /**
     * @return Finder
     */
    protected function getContentFiles()
    {
        return (new Finder)->in($this->contentPath)->files();
    }
}