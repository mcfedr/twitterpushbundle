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
        if ($this->topicName) {
            $this->topics->broadcast($m, $this->topicName);
        }
        else {
            $this->messages->broadcast($m);
        }
    }
}
