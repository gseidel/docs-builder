<?php

declare(strict_types=1);

/*
 * This file is part of the Docs Builder package.
 * (c) Ryan Weaver <ryan@symfonycasts.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyDocsBuilder\Renderers;

use Doctrine\RST\Environment;
use Doctrine\RST\Nodes\TocNode;
use Doctrine\RST\Renderers\NodeRenderer;
use Doctrine\RST\Templates\TemplateRenderer;

class TocNodeRenderer implements NodeRenderer
{
    /** @var Environment */
    private $environment;

    /** @var TocNode */
    private $tocNode;

    /** @var TemplateRenderer */
    private $templateRenderer;

    public function __construct(Environment $environment, TocNode $tocNode, TemplateRenderer $templateRenderer)
    {
        $this->environment      = $environment;
        $this->tocNode          = $tocNode;
        $this->templateRenderer = $templateRenderer;
    }

    public function render(): string
    {
        $options = $this->tocNode->getOptions();

        if (isset($options['hidden'])) {
            return '';
        }

        $tocItems = [];

        foreach ($this->tocNode->getFiles() as $file) {
            $reference = $this->environment->resolve('doc', $file);

            if ($reference === null) {
                continue;
            }

            $url = $this->environment->relativeUrl($reference->getUrl());

            $this->buildLevel($url, $reference->getTitles(), 1, $tocItems, $file);
        }

        return $this->templateRenderer->render('toc.html.twig', [
            'tocNode' => $this->tocNode,
            'tocItems' => $tocItems,
            'tocOptions' => $this->buildTocOptions($this->tocNode, $tocItems),
        ]);
    }

    private function buildTocOptions(TocNode $tocNode, array $tocItems): array
    {
        $maxDepth = $tocNode->getDepth();
        $numVisibleItems = 0;
        foreach ($tocItems as $tocItem) {
            if ($tocItem['level'] <= $maxDepth) {
                $numVisibleItems++;
            }
        }

        return [
            'maxDepth' => $maxDepth,
            'numVisibleItems' => $numVisibleItems,
            'size' => $this->getTocSize($numVisibleItems),
        ];
    }

    // If you change this method, make the same change in JsonGenerator too
    private function getTocSize(int $numVisibleItems): string
    {
        if ($numVisibleItems < 10) {
            return 'md';
        }

        if ($numVisibleItems < 20) {
            return 'lg';
        }

        return 'xl';
    }

    /**
     * @param mixed[]|array $titles
     * @param mixed[]       $tocItems
     */
    private function buildLevel(
        ?string $url,
        array $titles,
        int $level,
        array &$tocItems,
        string $file
    ): void {
        foreach ($titles as $k => $entry) {
            [$title, $children] = $entry;

            [$title, $target] = $this->generateTarget(
                $url,
                $title,
                // don't add anchor for first h1 in a different file (link directly to the file)
                ! ($level === 1 && $k === 0 && $file !== '/' . $this->environment->getCurrentFileName())
            );

            $tocItem = [
                'targetId' => $this->generateTargetId($target),
                'targetUrl' => $this->environment->generateUrl($target),
                'title' => $title,
                'level' => $level,
                'children' => [],
            ];

            // render children until we hit the configured maxdepth
            if (count($children) > 0 && $level < $this->tocNode->getDepth()) {
                $this->buildLevel($url, $children, $level + 1, $tocItem['children'], $file);
            }

            $tocItems[] = $tocItem;
        }
    }

    private function generateTargetId(string $target): string
    {
        return Environment::slugify($target);
    }

    /**
     * @param string[]|string $title
     *
     * @return mixed[]
     */
    private function generateTarget(?string $url, $title, bool $withAnchor): array
    {
        $target = $url;
        if ($withAnchor) {
            $anchor  = $this->generateAnchorFromTitle($title);
            $target .= '#' . $anchor;
        }

        if (is_array($title)) {
            [$title, $target] = $title;

            $reference = $this->environment->resolve('doc', $target);

            if ($reference === null) {
                return [$title, $target];
            }

            $target = $this->environment->relativeUrl($reference->getUrl());
        }

        return [$title, $target];
    }

    /**
     * @param string[]|string $title
     */
    private function generateAnchorFromTitle($title): string
    {
        $slug = is_array($title)
            ? $title[1]
            : $title;

        return Environment::slugify($slug);
    }
}
