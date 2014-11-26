<?php
namespace Mcfedr\TwitterPushBundle\Service;

use Mcfedr\AwsPushBundle\Message\Message;
use Mcfedr\AwsPushBundle\Service\Messages;

class TweetPusher
{

    /**
     * @var Messages
     */
    protected $messages;

    /**
     * @var string
     */
    protected $topicArn;

    /**
     * @var int
     */
    protected $gcmTtl;

    /**
     * @var string
     */
    protected $linkPlaceholder;

    /**
     * @param Messages $messages
     * @param string $topicArn
     * @param int $gcmTtl
     * @param string $linkPlaceholder
     */
    public function __construct(Messages $messages = null, $topicArn = null, $gcmTtl = null, $linkPlaceholder = null)
    {
        $this->messages = $messages;
        $this->topicArn = $topicArn;
        $this->gcmTtl = $gcmTtl;
        $this->linkPlaceholder = $linkPlaceholder;
    }

    /**
     * Send a tweet to everyone
     *
     * @param array $tweet
     */
    public function pushTweet($tweet)
    {
        $m = $this->getMessageForTweet($tweet);

        if ($this->topicArn) {
            $this->messages->send($m, $this->topicArn);
        } else {
            $this->messages->broadcast($m);
        }
    }

    protected function getMessageForTweet($tweet)
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
                $newText = trim(mb_substr($tweet['text'], 0, $entity['indices'][0], 'utf8') . ($this->linkPlaceholder ? : '') . mb_substr($tweet['text'], $entity['indices'][1], 'utf8'));
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
