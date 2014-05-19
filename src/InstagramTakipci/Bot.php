<?php

namespace InstagramTakipci;

use Goutte\Client;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use GuzzleHttp\Message\Response as GuzzleResponse;
use Symfony\Component\Finder\Finder;
use InstagramTakipci\IO\IOInterface;
use InstagramTakipci\Site\SiteInterface;

class Bot extends Client
{
    /**
     * @var IOInterface
     */
    protected $io;

    /**
     *
     * @var Config
     */
    protected $config;

    /**
     *
     * @var SiteInterface
     */
    protected $currentSite = NULL;

    /**
     * @var \Guzzle\Http\Message\Response
     */
    protected $guzzleResponse;

    protected $sites = array();
    protected $clientIds = array(
        "104087a4e94c467b83b19a7f315c6f43",
        "ff71a6212d124eb6a648a240c681e27b",
        "4123f73c6aad4f3b903369ccb353ecde",
        "9420651ad6064ce196bf4ff008ec2032",
        "ab48a254b33b467f8634331184c6550a",
        "9fd51ae9c0014b2293be80bfae3692de",
        "8e7165923b2845adb1b8f36f3ba4be8b",
        "713da7bc385e4d7f9e01a73ad2845ae5",
        "010b62cc4e344146bd73437f9b634587",
        "c02d1c473e53485d946a1c44d3daf8d2"
    );

    public function __construct(IOInterface $io, Config $config)
    {
        $this->io = $io;
        $this->config = $config;

        parent::__construct();

        // Create the Guzzle and Goutte clients
        $this->setClient(new GuzzleClient(array(
            'config' => array(
                'curl' => array(
                    CURLOPT_COOKIESESSION => true,
                    CURLOPT_COOKIEJAR => getcwd() . '/cookie/cookies.txt',
                    CURLOPT_COOKIEFILE => getcwd() . '/cookie/cookies.txt',
                    CURLOPT_TIMEOUT => 0
                ),
                'headers' => array(
                    'User-Agent' => random_uagent()
                )
            )
        )));

        $this->setHeader("User-Agent", random_uagent());

        $this->setupLoggers();
        $this->registerBuiltInSites();
    }

    protected function createResponse(GuzzleResponse $response)
    {
        $this->guzzleResponse = $response;

        return parent::createResponse($response);
    }

    /**
     * Find all the implementations of the SiteInterface and add them.
     */
    public function registerBuiltInSites()
    {
        foreach (Finder::create()->files()->in(__DIR__ . '/Site')->notName("/SiteInterface/i") as $file) {
            $class = 'InstagramTakipci\\Site\\' . basename($file, '.php');
            $this->addSite(new $class());
        }
    }

    public function setupLoggers()
    {
        // create a log channel
        $log = new Logger('guzzle_requests');
        $log->pushHandler(new StreamHandler('guzzle.log'));

        $client = $this->getClient();
        $subscriber = new LogSubscriber($log, Formatter::DEBUG);
        $client->getEmitter()->attach($subscriber);
    }

    public function addSite(SiteInterface $site)
    {
        $this->sites[] = $site;
    }

    public function getSites()
    {
        return $this->sites;
    }

    public function getRandomSite()
    {
        return $this->getSites()[array_rand($this->getSites())];
    }

    public function setRandomSite()
    {
        $this->currentSite = $this->getRandomSite();

        return $this;
    }

    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Tries to login to current site.
     */
    public function login()
    {
        $crawler = $this->request('GET', $this->currentSite->getLoginAddress());
        $url = $this->getHistory()->current()->getUri();
        $title = $this->_getTitle($crawler);

        if ($crawler->html() == "<body><p>Forbidden</p></body>") {
            return array(
                "error" => array(
                    "message" => "FORBIDDEN",
                    "info" => "Giriş için yapılan ilk istekte"
                )
            );
        }

        // Otomatik giriş yapmışız
        if ($url == $this->currentSite->getLoginParameters()['successUrl']) {
            return array(
                "success" => array(
                    "message" => "OK"
                )
            );
        }

        // Giriş yapmamız lazım
        if (strpos($title, "Log in") !== FALSE) {
            $form = $crawler->selectButton('Log in')->form();

            $crawler = $this->submit($form, array(
                'username' => $this->config->getUsername(),
                'password' => $this->config->getPassword())
            );

            $url = $this->getHistory()->current()->getUri();
            $title = $this->_getTitle($crawler);

            if ($crawler->html() == "<body><p>Forbidden</p></body>") {
                return array(
                    "error" => array(
                        "message" => "FORBIDDEN",
                        "info" => "Giriş için gönderilen formda"
                    )
                );
            }

            if ($url == $this->currentSite->getLoginParameters()['successUrl']) {
                return array(
                    "success" => array(
                        "message" => "OK"
                    )
                );
            }

            $err = $crawler->filter("p.alert-red");
            if (iterator_count($err)) {
                return array(
                    "error" => array(
                        "message" => $err->text(),
                        "info" => "Giriş için gönderilen formda"
                    )
                );
            }

            // Uygulamaya izin vermemiz lazım
            if (strpos($title, "Authorization Request") !== FALSE) {
                $form = $crawler->selectButton('Authorize')->form();

                $crawler = $this->submit($form);
                $url = $this->getHistory()->current()->getUri();
                $title = $this->_getTitle($crawler);

                if ($crawler->html() == "<body><p>Forbidden</p></body>") {
                    return array(
                        "error" => array(
                            "message" => "FORBIDDEN",
                            "info" => "Uygulamaya izin verme formunda"
                        )
                    );
                }

                if ($url == $this->currentSite->getLoginParameters()['successUrl']) {
                    return array(
                        "success" => array(
                            "message" => "OK"
                        )
                    );
                }
            }
        }

        if (strpos($title, "Authorization Request") !== FALSE) {
            $form = $crawler->selectButton('Authorize')->form();

            $crawler = $this->submit($form);
            $url = $this->getHistory()->current()->getUri();
            $title = $this->_getTitle($crawler);

            if ($crawler->html() == "<body><p>Forbidden</p></body>") {
                return array(
                    "error" => array(
                        "message" => "FORBIDDEN",
                        "info" => "Uygulamaya izin verme formunda"
                    )
                );
            }

            if ($url == $this->currentSite->getLoginParameters()['successUrl']) {
                return array(
                    "success" => array(
                        "message" => "OK"
                    )
                );
            }
        }

        // Buraya kadar geldiysek, bir şeyler çok ters gtimiş olmalı
        return array(
            "error" => array(
                "message" => "Fonksiyon sonu",
                "vars" => array($url, $title, $this->getHistory()->current()->getUri())
            )
        );
    }

    public function logout()
    {
        $params = $this->currentSite->getLogoutParameters($id);
        $urlParams = $params['urlParameters'];
        $postParams = $params['postParameters'];

        if (!is_array($this->currentSite->getLogoutAddress())) {
            $adresses = array($this->currentSite->getLogoutAddress());
        }

        foreach ($adresses AS $address) {
            $adress = $this->_buildUrlParameters($adress, $urlParams);
            $this->request("GET", $adress);
        }

        // Delete the cookies
        unlink(getcwd() . '/cookie/cookies.txt');
        touch(getcwd() . '/cookie/cookies.txt');
    }

    /**
     * Like a media by id.
     *
     * @param  int    $id ID of media to be liked
     * @return Object The JSON response
     */
    public function like($id)
    {
        $params = $this->currentSite->getLikeParameters($id);
        $urlParams = $params['urlParameters'];
        $postParams = $params['postParameters'];

        $url = $this->_buildUrlParameters($this->currentSite->getLikeAddress(), $urlParams);

        $crawler = $this->request(
            "POST", $url, $postParams
        );

        return json_decode(strip_tags($this->getGuzzleResponse()->getBody()));
    }

    /**
     * Follow a user by its ID
     *
     * @param  int    $id ID of user to be followed
     * @return Object The JSON response
     */
    public function follow($id)
    {
        $params = $this->currentSite->getFollowParameters($id);
        $postParams = $params['postParameters'];

        $url = $this->_buildUrlParameters($this->currentSite->getFollowAddress(), $params['urlParameters']);

        $crawler = $this->request(
                "POST", $url, $postParams
        );

        return json_decode(strip_tags($this->getGuzzleResponse()->getBody()));
    }

    /**
     * Unfollow a user by its ID
     *
     * @param  int    $id ID of user to be unfollowed
     * @return Object The JSON response
     */
    public function unfollow($id)
    {
        $params = $this->currentSite->getUnfollowParameters($id);
        $postParams = $params['postParameters'];

        $url = $this->_buildUrlParameters($this->currentSite->getUnfollowAddress(), $params['urlParameters']);

        $crawler = $this->request(
            "POST", $url, $postParams
        );

        return json_decode(strip_tags($this->getGuzzleResponse()->getBody()));
    }

    /**
     * Posts a comment to a media.
     *
     * @param id $mediaId ID of media to comment
     *
     * @return Object The JSON response
     */
    public function comment($mediaId)
    {
        $comment = $this->config->getComments()[array_rand($this->config->getComments())];

        $params = $this->currentSite->getCommentParameters($mediaId, $comment);
        $postParams = $params['postParameters'];

        $url = $this->_buildUrlParameters($this->currentSite->getCommentAddress(), $params['urlParameters']);

        $crawler = $this->request(
            "POST", $url, $postParams
        );

        return json_decode(strip_tags($this->getGuzzleResponse()->getBody()));
    }

    /**
     * Instagram kullanıcı adından, kullanıcı id sini bulur.
     *
     * @param  string    $username IG kullanıcı adı
     * @return int|false Returns the user id if found, FALSE otherwise.
     */
    public function getUserIdByName($username)
    {
        // TODO: URL is hardcoded
        $crawler = $this->request(
            'GET',
            sprintf("https://api.instagram.com/v1/users/search?q=%s&client_id=%s",
                    $username, $this->clientIds[array_rand($this->clientIds)]
            )
        );

        $data = json_decode(strip_tags($this->getGuzzleResponse()->getBody()), TRUE);
        if (!is_array($data)) {
            // TODO: Log the exception and response
            print_r($data);

            return FALSE;
        }

        if (200 !== $data['meta']['code']) {
            // TODO: Log the error
            $this->io->isVerbose() &&
                $this->io->write($data);

            return FALSE;
        }

        return reset($data['data'])['id'];
    }

    public function doALL()
    {
        $data = $this->currentSite->getMediaAndUserIdsFromHashtags($this->getConfig()->getHashTags(), $this);

        foreach ($data AS $hashtag => $hashtagArray) {
            foreach ($hashtagArray as $mediaInfo) {
                /*** LIKE ***/
                $jsonResponse = $this->like($mediaInfo['media_id']);

                if (!is_object($jsonResponse)) {
                    $this->io->write("<error>HATA: (like) Bir üstü kontrol et</error>");
                    //exit;
                }

                if ($jsonResponse->status == "OK" && $jsonResponse->message == "LIKED") {
                    // Write info about like
                    $this->io->isVerbose() &&
                            $this->io->write("<info>INFO: #$hashtag için $mediaInfo[media_id] beğenildi!</info>");
                } elseif ($jsonResponse->status == "NG") {
                    if ($jsonResponse->message) {
                        $this->io->isVerbose() &&
                                $this->io->write("<error>HATA: " . $jsonResponse->message . "(#$hashtag, $mediaInfo[media_id])</error>");
                    } else {
                        // TODO: Log the error
                        $this->io->isVerbose() &&
                                $this->io->write("<error>HATA: Bilinmeyen hata. (#$hashtag, $mediaInfo[media_id])</error>");
                    }
                }
                sleep(rand(3, 6));

                /*** COMMENT ***/
                $jsonResponse = $this->comment($mediaInfo['media_id']);

                if (!is_object($jsonResponse)) {
                    $this->io->write("<error>HATA: (comment) Bir üstü kontrol et</error>");
                    //exit;
                }

                if ($jsonResponse->status == "OK") {
                    // Write info about like
                    $this->io->isVerbose() &&
                            $this->io->write("<info>INFO: #$hashtag için $mediaInfo[media_id] yorum yapıldı!</info>");
                } elseif ($jsonResponse->status == "NG") {
                    if ($jsonResponse->message) {
                        $this->io->isVerbose() &&
                                $this->io->write("<error>HATA: " . $jsonResponse->message . "(#$hashtag, $mediaInfo[media_id])</error>");
                    } else {
                        // TODO: Log the error
                        $this->io->isVerbose() &&
                                $this->io->write("<error>HATA: Bilinmeyen hata. (#$hashtag, $mediaInfo[media_id])</error>");
                    }
                }
                sleep(rand(3, 6));

                /*** FOLLOW ***/
                $jsonResponse = $this->follow($mediaInfo['user_id']);
                if (!is_object($jsonResponse)) {
                    $this->io->write("<error>HATA: (follow) Bir üstü kontrol et</error>");
                    //exit;
                }

                if ($jsonResponse->status == "OK") {
                    // Write info about follow
                    $this->io->isVerbose() &&
                            $this->io->write("<info>INFO: #$hashtag için $mediaInfo[username] takip edildi!</info>");
                } else {
                    // TODO: Log the request for investigating the error
                    $this->io->isVerbose() &&
                            $this->io->write("<error>HATA: Bilinmeyen hata. (#$hashtag, $mediaInfo[username])</error>");
                }
                sleep(rand(10, 30));
            }
        }
    }

    protected function _getTitle($crawler)
    {
        $title = $crawler->filter("title");

        return iterator_count($title) >= 1 ? $title->text() : FALSE;
    }

    protected function _buildUrlParameters($url, $params)
    {
        $queryString = (count($params) == 1) ? '' : "?";

        foreach ($params as $key => $value) {
            $queryString .= $key . (empty($key) && !is_numeric($key) ? "" : "=") . $value . '&';
        }

        return $url . trim($queryString, '&');
    }

    /**
     * @return \Guzzle\Http\Message\Response
     */
    public function getGuzzleResponse()
    {
        return $this->guzzleResponse;
    }
}
