<?php

namespace Mcfedr\TwitterPushBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class McfedrTwitterPushExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('mcfedr_twitter_push.twitter.consumer_key', $config['twitter']['consumer_key']);
        $container->setParameter('mcfedr_twitter_push.twitter.consumer_secret', $config['twitter']['consumer_secret']);
        $container->setParameter('mcfedr_twitter_push.twitter.token', $config['twitter']['token']);
        $container->setParameter('mcfedr_twitter_push.twitter.token_secret', $config['twitter']['token_secret']);
        $container->setParameter('mcfedr_twitter_push.userid', $config['userid']);
        $container->setParameter('mcfedr_twitter_push.gcm_ttl', $config['gcm_ttl']);
        $container->setParameter('mcfedr_twitter_push.link_placeholder', $config['link_placeholder']);
        $container->setParameter('mcfedr_twitter_push.max_pushes_per_hour', $config['max_pushes_per_hour']);

        if (isset($config['cache'])) {
            $cacheName = $config['cache'];
        } else {
            $cacheName = 'mcfedr_twitter_push.cache';
            $container->setDefinition(
                $cacheName,
                new Definition('Doctrine\Common\Cache\ArrayCache')
            );
        }

        $container->setDefinition(
            'mcfedr_twitter_push.pusher',
            new Definition('Mcfedr\TwitterPushBundle\Service\TweetPusher', [
                new Reference('mcfedr_aws_push.messages'),
                $container->getParameter('mcfedr_aws_push.topic_arn'),
                $container->getParameter('mcfedr_twitter_push.gcm_ttl'),
                $container->getParameter('mcfedr_twitter_push.link_placeholder'),
                $container->getParameter('mcfedr_twitter_push.max_pushes_per_hour'),
                new Reference($cacheName),
                isset($config['cache_timeout']) ? $config['cache_timeout'] : 3600
            ])
        );
    }
}
