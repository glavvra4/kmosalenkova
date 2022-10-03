<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace App\Twig\Extension;

use Pimcore\Model\Document;
use Pimcore\Navigation\Container;
use Pimcore\Twig\Extension\Templating\Navigation;
use Pimcore\Twig\Extension\Templating\Placeholder;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NavigationExtension extends AbstractExtension
{
    const NAVIGATION_EXTENSION_POINT_PROPERTY = 'navigation_extension_point';

    protected Navigation $navigationHelper;
    protected Placeholder $placeholderHelper;

    /**
     * @param Navigation $navigationHelper
     * @param Placeholder $placeholderHelper
     */
    public function __construct(Navigation $navigationHelper, Placeholder $placeholderHelper)
    {
        $this->navigationHelper = $navigationHelper;
        $this->placeholderHelper = $placeholderHelper;
    }

    /**
     * @return array|TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('app_navigation_data_links', [$this, 'getDataLinks']),
            new TwigFunction('app_navigation_enrich_breadcrumbs', [$this, 'enrichBreadcrumbs'])
        ];
    }

    /**
     * @param Document $document
     * @param Document $startNode
     *
     * @return \Pimcore\Navigation\Container
     */
    public function getDataLinks(Document $document, Document $startNode)
    {
        return $this->navigationHelper->build([
            'active' => $document,
            'root' => $startNode
        ]);
    }

    /**
     * @param Container $navigation
     *
     * @return Container
     *
     * @throws \Exception
     */
    public function enrichBreadcrumbs(Container $navigation): Container
    {
        $additionalBreadCrumbs = $this->placeholderHelper->__invoke('addBreadcrumb');

        if ($additionalBreadCrumbs->getArrayCopy()) {
            $parentPage = false;

            foreach ($additionalBreadCrumbs->getArrayCopy() as $breadcrumb) {
                $page = $navigation->findBy('id', $breadcrumb['id']);
                if (null === $page) {
                    $parentPage = $parentPage ?: $navigation->findBy('id', $breadcrumb['parentId']);
                    $newPage = new \Pimcore\Navigation\Page\Document([
                        'id' => $breadcrumb['id'],
                        'uri' => isset($breadcrumb['url']) && $breadcrumb['url'] != '' ? $breadcrumb['url'] : '',
                        'label' => $breadcrumb['label'],
                        'active' => true
                    ]);
                    if ($parentPage) {
                        $parentPage->setActive(false);
                        $parentPage->addPage($newPage);
                        $parentPage = $newPage;
                    } else {
                        $navigation->addPage($newPage);
                    }
                }
            }
        }

        return $navigation;
    }
}
