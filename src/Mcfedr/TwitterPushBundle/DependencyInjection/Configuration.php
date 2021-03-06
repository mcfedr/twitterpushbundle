<?php

namespace Mcfedr\TwitterPushBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('mcfedr_twitter_push')
            ->children()
                ->arrayNode('twitter')
                    ->children()
                        ->scalarNode('consumer_key')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('consumer_secret')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('token')->end()
                        ->scalarNode('token_secret')->end()
                    ->end()
                ->end()
                ->scalarNode('userid')->isRequired()->cannotBeEmpty()->end()
                ->integerNode('gcm_ttl')->min(0)->defaultValue(86400)->end()
                ->scalarNode('link_placeholder')->defaultValue('[link]')->end()
                ->integerNode('max_pushes_per_hour')->min(0)->defaultValue(0)->end()
                ->scalarNode('cache')->end()
                ->integerNode('cache_timeout')->min(0)->end()
            ->end()
        ->end();


        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
