<?php

namespace InstagramTakipci\Site;

use InstagramTakipci\SiteInterface;

class Webstagram implements SiteInterface {

    /**
     * {@inheritdoc}
     */
    public function getName() {
        return 'webstagram';
    }

    /**
     * {@inheritdoc}
     */
    public function getAddress() {
        return 'http://web.stagram.com/';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getLikeAddress() {
        return 'http://web.stagram.com/do_like/';
    }

    /**
     * {@inheritdoc}
     */
    public function getLikeParameters($id) {
        return array(
            "urlParameters" => array(),
            "postParameters" => array(
                "pk" => $id,
                "t" => floor(mt_rand() / mt_getrandmax() * 10000 + 1)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDislikeAddress() {
        return 'http://web.stagram.com/do_dislike/';
    }

    /**
     * {@inheritdoc}
     */
    public function getDislikeParameters($id) {
        return array(
            "urlParameters" => array(),
            "postParameters" => array(
                "pk" => $id,
                "t" => floor(mt_rand() / mt_getrandmax() * 10000 + 1)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFollowAddress() {
        return 'http://web.stagram.com/do_follow/';
    }

    /**
     * {@inheritdoc}
     */
    public function getFollowParameters($id) {
        return array(
            "urlParameters" => array(
                "" => time()
            ),
            "postParameters" => array(
                "pk" => $id,
                "t" => floor(mt_rand() / mt_getrandmax() * 10000 + 1)
            )
        );
    }
    
    /**
     * {@inheritdoc}
     */
    public function getUnfollowAddress() {
        return 'http://web.stagram.com/do_unfollow/';
    }

    /**
     * {@inheritdoc}
     */
    public function getUnfollowParameters($id) {
        return array(
            "urlParameters" => array(
                "" => time()
            ),
            "postParameters" => array(
                "pk" => $id,
                "t" => floor(mt_rand() / mt_getrandmax() * 10000 + 1)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginAddress() {
        return 'https://api.instagram.com/oauth/authorize/?client_id=9d836570317f4c18bca0db6d2ac38e29&redirect_uri=http%3A%2F%2Fweb.stagram.com%2F&response_type=code&scope=likes+comments+relationships';
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginParameters() {
        return array(
            "successUrl" => "http://web.stagram.com/feed/"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoutAddress() {
        return array("http://web.stagram.com/?t=lo", "http://instagram.com/accounts/logout/");
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoutParameters() {
        return array();
    }
}
