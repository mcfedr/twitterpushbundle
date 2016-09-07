<?php


class TestKernel extends Symfony\Component\HttpKernel\Kernel
{
    public function registerBundles()
    {
        return [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Mcfedr\AwsPushBundle\McfedrAwsPushBundle(),
            new Mcfedr\TwitterPushBundle\McfedrTwitterPushBundle()
        ];
    }

    public function registerContainerConfiguration(\Symfony\Component\Config\Loader\LoaderInterface $loader)
    {
        $loader->load(__DIR__ . '/config_test.yml');
    }
}
