<?php
namespace Mcfedr\TwitterPushBundle\Service;

use Doctrine\Common\Cache\Cache;
use Mcfedr\AwsPushBundle\Message\Message;
use Mcfedr\AwsPushBundle\Service\Messages;
use Mcfedr\TwitterPushBundle\Exception\InvalidParameterCombinationException;
use Mcfedr\TwitterPushBundle\Exception\MaxPushesPerHourException;

class TweetPusher
{
    const CACHE_KEY = 'mcfedr_twitter_push.last_hour';

    /**
     * @var Messages
     */
    private $messages;

    /**
     * @var string
     */
    private $topicArn;

    /**
     * @var int
     */
    private $gcmTtl;

    /**
     * @var string
     */
    private $linkPlaceholder;

    /**
     * @var int
     */
    private $maxPushesPerHour;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param Messages $messages
     * @param string $topicArn
     * @param int $gcmTtl
     * @param string $linkPlaceholder
     * @param int $maxPushesPerHour
     * @param Cache $cache
     * @throws InvalidParameterCombinationException
     */
    public function __construct(Messages $messages, $topicArn = null, $gcmTtl = null, $linkPlaceholder = null, $maxPushesPerHour = 0, $cache = null)
    {
        if ($maxPushesPerHour > 0 & !$cache) {
            throw new InvalidParameterCombinationException('When max pushes is set you must provide a cache');
        }

        $this->messages = $messages;
        $this->topicArn = $topicArn;
        $this->gcmTtl = $gcmTtl;
        $this->linkPlaceholder = $linkPlaceholder;
        $this->maxPushesPerHour = $maxPushesPerHour;
        $this->cache = $cache;
    }

    /**
     * Send a tweet to everyone
     *
     * @param array $tweet
     * @throws MaxPushesPerHourException
     * @throws \Mcfedr\AwsPushBundle\Exception\PlatformNotConfiguredException
     */
    public function pushTweet($tweet)
    {
        if ($this->maxPushesPerHour > 0 && $this->cache) {
            $hourAgo = new \DateTime('- 1 hour');
            /** @var \DateTime[] $tweetTimes */
            $tweetTimes = $this->cache->fetch(static::CACHE_KEY) ?: [];
            foreach ($tweetTimes as $k => $time) {
                if ($time < $hourAgo) {
                    unset($tweetTimes[$k]);
                }
            }
            if (($count = count($tweetTimes)) >= $this->maxPushesPerHour) {
                throw new MaxPushesPerHourException("Cannot send push, $count have already been sent. The limit is {$this->maxPushesPerHour}");
            }
            $tweetTimes[] = new \DateTime();
            $this->cache->save(static::CACHE_KEY, $tweetTimes, 3600);
        }

        $m = $this->getMessageForTweet($tweet);

        if ($this->topicArn) {
            $this->messages->send($m, $this->topicArn);
        } else {
            $this->messages->broadcast($m);
        }
    }

    private function getMessageForTweet($tweet)
    {
        $m = new Message($tweet['text']);
        $custom = [
            'i' => $tweet['id_str']
        ];
        //Check for links in the tweet
        foreach (['urls', 'media'] as $entity) {
            if (isset($tweet['entities']) && isset($tweet['entities'][$entity]) && isset($tweet['entities'][$entity][0])) {
                $entity = $tweet['entities'][$entity][0];
                $custom['u'] = $entity['url'];
                //Remove the link from the text to save space
                $newText = trim(mb_substr($tweet['text'], 0, $entity['indices'][0], 'utf8') . ($this->linkPlaceholder ?: '') . mb_substr($tweet['text'], $entity['indices'][1], null, 'utf8'));
                if ($newText != '') {
                    $m->setText($newText);
                }
                break;
            }
        }

        $m->setCustom($custom);

        $m->setTtl($this->gcmTtl);

        return $m;
    }
}
