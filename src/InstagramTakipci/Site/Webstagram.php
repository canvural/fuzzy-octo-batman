<?php

namespace InstagramTakipci\Site;

use InstagramTakipci\Bot;
use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class Webstagram implements SiteInterface
{

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'webstagram';
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress()
    {
        return 'http://web.stagram.com/';
    }

    /**
     * {@inheritdoc}
     */
    public function getLikeAddress()
    {
        return 'http://web.stagram.com/api/like/';
    }

    /**
     * {@inheritdoc}
     */
    public function getLikeParameters($id)
    {
        return array(
            "urlParameters" => array(
                "" => $id
            ),
            "postParameters" => array()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDislikeAddress()
    {
        return 'http://web.stagram.com/api/remove_like/';
    }

    /**
     * {@inheritdoc}
     */
    public function getDislikeParameters($id)
    {
        return array(
            "urlParameters" => array("" => $id),
            "postParameters" => array()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFollowAddress()
    {
        return 'http://web.stagram.com/api/relationships/';
    }

    /**
     * {@inheritdoc}
     */
    public function getFollowParameters($id)
    {
        return array(
            "urlParameters" => array("" => $id),
            "postParameters" => array(
                "action" => "follow",
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUnfollowAddress()
    {
        return 'http://web.stagram.com/api/relationships/';
    }

    /**
     * {@inheritdoc}
     */
    public function getUnfollowParameters($id)
    {
        return array(
            "urlParameters" => array("" => $id),
            "postParameters" => array(
                "action" => "unfollow",
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginAddress()
    {
        return 'https://api.instagram.com/oauth/authorize/?client_id=9d836570317f4c18bca0db6d2ac38e29&redirect_uri=http://web.stagram.com/&response_type=code&scope=comments+relationships+likes';
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginParameters()
    {
        return array(
            "successUrl" => "http://web.stagram.com/feed"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoutAddress()
    {
        return array("http://web.stagram.com/?t=lo", "http://instagram.com/accounts/logout/");
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoutParameters()
    {
        return array(
            "urlParameters" => array(),
            "postParameters" => array()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentAddress()
    {
        return "http://web.stagram.com/api/comments/";
    }

    /**
     * {@inheritdoc}
     */
    public function getCommentParameters($mediaId, $comment)
    {
        return array(
            "urlParameters" => array("" => $mediaId),
            "postParameters" => array(
                "comment"   => $comment,
                "media_id" => $mediaId
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaAndUserIdsFromHashtags(array $hashtags, Bot $client)
    {
        $tagUrl = "http://web.stagram.com/tag/";

        $returnArray = array();

        foreach ($hashtags as $hashtag) {
            $url = $tagUrl . $hashtag . '/';

            $main = $client->request('GET', $url)->filter('.photobox');

            $returnArray[$hashtag] = $main->each(
                function (Crawler $node, $i) {
                    $usernameNode = $node->filter("a.username");
                    $username = $usernameNode->text();

                    $mainimgNode = $node->filter("a.mainimg");
                    $mainimgHref = substr($mainimgNode->attr("href"), 3);
                    list($mediaId, $userId) = explode('_', $mainimgHref);

                    return array(
                        "username" => $username,
                        "media_id"  => $mediaId,
                        "user_id"   => $userId
                    );
            });
        }

        return $returnArray;
    }
}
