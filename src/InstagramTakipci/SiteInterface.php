<?php

namespace InstagramTakipci;

/**
 * 
 */
interface SiteInterface {

    /**
     * Return the name of the IG site.
     *
     * The name must be all lowercase and without any spaces.
     *
     * @return string The name of the IG site
     */
    public function getName();

    /**
     * Return the address of the IG site.
     *
     * The address must be absolute and should start with http://
     *
     * @return string The address of the IG site
     */
    public function getAddress();

    /**
     * Return the login address of the IG site.
     *
     * The address must be absolute and should start with http://
     *
     * @return string The login address of the IG site
     */
    public function getLoginAddress();

    /**
     * Return the logout address of the IG site.
     *
     * The address must be absolute and should start with http://
     *
     * @return string The logout address of the IG site
     */
    public function getLogoutAddress();

    /**
     * Return the like address of the IG site.
     *
     * The address must be absolute and should start with http://
     *
     * @return string The like address of the IG site
     */
    public function getLikeAddress();

    /**
     * Return the dislike address of the IG site.
     *
     * The address must be absolute and should start with http://
     *
     * @return string The dislike address of the IG site
     */
    public function getDislikeAddress();

    /**
     * Return the follow address of the IG site.
     *
     * The address must be absolute and should start with http://
     *
     * @return string The follow address of the IG site
     */
    public function getFollowAddress();

    /**
     * Return the unfollow address of the IG site.
     *
     * The address must be absolute and should start with http://
     *
     * @return string The unfollow address of the IG site
     */
    public function getUnfollowAddress();

    /**
     * Return the array of parameters for log in.
     *
     * If there is no parameter required this should return empty array.
     *
     * @return array The parameters for log in action.
     */
    public function getLoginParameters();

    /**
     * Return the array of parameters for logout.
     *
     * If there is no parameter required this should return empty array.
     *
     * @return array The parameters for logout action.
     */
    public function getLogoutParameters();

    /**
     * Return the array of parameters for like.
     *
     * If there is no parameter required this should return empty array.
     *
     * @param int $id ID of the media
     * @return array The parameters for like action.
     */
    public function getLikeParameters($id);

    /**
     * Return the array of parameters for dislike.
     *
     * If there is no parameter required this should return empty array.
     *
     * @param int $id ID of the media
     * @return array The parameters for dislike action.
     */
    public function getDislikeParameters($id);

    /**
     * Return the array of parameters for follow.
     *
     * If there is no parameter required this should return empty array.
     *
     * @param int $id ID of the user
     * @return array The parameters for follow action.
     */
    public function getFollowParameters($id);

    /**
     * Return the array of parameters for unfollow.
     *
     * If there is no parameter required this should return empty array.
     *
     * @param int $id ID of the user
     * @return array The parameters for unfollow action.
     */
    public function getUnfollowParameters($id);
}
