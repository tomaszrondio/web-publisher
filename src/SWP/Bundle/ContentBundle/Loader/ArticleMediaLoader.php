<?php

declare(strict_types=1);

/*
 * This file is part of the Superdesk Web Publisher Content Bundle.
 *
 * Copyright 2015 Sourcefabric z.u. and contributors.
 *
 * For the full copyright and license information, please see the
 * AUTHORS and LICENSE files distributed with this source code.
 *
 * @copyright 2015 Sourcefabric z.ú
 * @license http://www.superdesk.org/license
 */

namespace SWP\Bundle\ContentBundle\Loader;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use SWP\Bundle\ContentBundle\Provider\ArticleMediaProviderInterface;
use SWP\Component\Common\Criteria\Criteria;
use SWP\Component\TemplatesSystem\Gimme\Context\Context;
use SWP\Component\TemplatesSystem\Gimme\Factory\MetaFactory;
use SWP\Component\TemplatesSystem\Gimme\Loader\LoaderInterface;
use SWP\Component\TemplatesSystem\Gimme\Meta\Meta;
use SWP\Component\TemplatesSystem\Gimme\Meta\MetaCollection;

/**
 * Class ArticleMediaLoader.
 */
class ArticleMediaLoader extends PaginatedLoader implements LoaderInterface
{
    /**
     * @var MetaFactory
     */
    protected $metaFactory;

    /**
     * @var Context
     */
    protected $context;

    /**
     * @var ArticleMediaProviderInterface
     */
    protected $articleMediaProvider;

    private $mediaCache = [];

    /**
     * ArticleMediaLoader constructor.
     *
     * @param ArticleMediaProviderInterface $articleMediaProvider
     * @param MetaFactory                   $metaFactory
     * @param Context                       $context
     */
    public function __construct(ArticleMediaProviderInterface $articleMediaProvider, MetaFactory $metaFactory, Context $context)
    {
        $this->articleMediaProvider = $articleMediaProvider;
        $this->metaFactory = $metaFactory;
        $this->context = $context;
    }

    /**
     *  {@inheritdoc}
     */
    public function load($type, $parameters = [], $withoutParameters = [], $responseType = LoaderInterface::COLLECTION)
    {
        $mediaKey = md5($type.json_encode([$parameters, $withoutParameters]).$responseType);
        if (isset($this->mediaCache[$mediaKey])) {
            return $this->mediaCache[$mediaKey];
        }

        if (LoaderInterface::COLLECTION === $responseType) {
            $criteria = new Criteria();
            $criteria->set('maxResults', null);

            if (array_key_exists('article', $parameters) && $parameters['article'] instanceof Meta) {
                $criteria->set('article', $parameters['article']->getValues());
            } elseif (isset($this->context->article) && null !== $this->context->article) {
                $criteria->set('article', $this->context->article->getValues());
            } else {
                return false;
            }

            $criteria = $this->applyPaginationToCriteria($criteria, $parameters);
            $media = $criteria->get('article')->getMedia();

            if (($media instanceof PersistentCollection && $media->isInitialized()) || $media instanceof ArrayCollection) {
                $collectionCriteria = new \Doctrine\Common\Collections\Criteria(
                    null,
                    $criteria->get('order'),
                    $criteria->get('firstResult'),
                    $criteria->get('maxResults')
                );
                $mediaCount = $criteria->get('article')->getMedia()->count();
                $media = $criteria->get('article')->getMedia()->matching($collectionCriteria);
            } else {
                $mediaCount = $this->articleMediaProvider->getCountByCriteria($criteria);
                $media = $this->articleMediaProvider->getManyByCriteria($criteria);
            }

            if ($media->count() > 0) {
                $metaCollection = new MetaCollection();
                $metaCollection->setTotalItemsCount($mediaCount);
                foreach ($media as $item) {
                    $metaCollection->add($this->metaFactory->create($item));
                }
                $this->mediaCache[$mediaKey] = $metaCollection;

                return $metaCollection;
            }
        }

        return false;
    }

    /**
     * Checks if Loader supports provided type.
     *
     * @param string $type
     *
     * @return bool
     */
    public function isSupported(string $type): bool
    {
        return in_array($type, ['articleMedia']) && !$this->context->isPreviewMode();
    }
}
