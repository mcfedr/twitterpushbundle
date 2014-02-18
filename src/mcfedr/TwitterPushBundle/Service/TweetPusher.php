<?php
namespace mcfedr\TwitterPushBundle\Service;

use mcfedr\AWSPushBundle\Message\Message;
use mcfedr\AWSPushBundle\Service\Messages;
use mcfedr\AWSPushBundle\Service\Topics;

class TweetPusher
{

    /**
     * @var Messages
     */
    private $messages;

    /**
     * @var Topics
     */
    private $topics;

    /**
     * @var string
     */
    private $topicName;

    /**
     * @param Messages $messages
     * @param Topics $topics
     * @param string $topicName
     */
    public function __construct(Messages $messages, Topics $topics, $topicName)
    {
        $this->messages = $messages;
        $this->topics = $topics;
        $this->topicName = $topicName;
    }

    /**
     * Send a tweet to everyone
     *
     * @param array $tweet
     */
    public function pushTweet($tweet)
    {
        $m = new Message($tweet['text']);
        if (isset($tweet['entities']) && isset($tweet['entities']['urls']) && isset($tweet['entities']['urls'][0])) {
            $m->setCustom([
                'url' => $tweet['entities']['urls'][0]['url']
            ]);
        } else if (isset($tweet['entities']) && isset($tweet['entities']['media']) && isset($tweet['media']['urls'][0])) {
            $m->setCustom([
                'url' => $tweet['entities']['media'][0]['url']
            ]);
        }

        if ($this->topicName) {
            $this->topics->broadcast($m, $this->topicName);
        }
        else {
            $this->messages->broadcast($m);
        }
    }
}
