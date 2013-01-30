<?php

namespace TE\SearchifyBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class TESearchifyExtension extends Extension
{
    /**
     * {@inheritDoc}
     *
     * @param array            $configs   configuration
     * @param ContainerBuilder $container container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->addClassesToCompile(array(
            'Searchify\\Api',
            'Searchify\\Index',
            'Searchify\\Response',
            'Searchify\\SearchifyException',
            'Searchify\\Exception\\HttpException',
            'Searchify\\Exception\\IndexAlreadyExists',
            'Searchify\\Exception\\IndexDoesNotExist',
            'Searchify\\Exception\\InvalidDefinition',
            'Searchify\\Exception\\InvalidQuery',
            'Searchify\\Exception\\InvalidResponseFromServer',
            'Searchify\\Exception\\InvalidUrl',
            'Searchify\\Exception\\TooManyIndexes',
            'Searchify\\Exception\\Unauthorized'
        ));
    }
}
