<?php

namespace InstagramTakipci;

use Symfony\Component\Finder\Finder;
use InstagramTakipci\SiteInterface;
use InstagramTakipci\Config;
use Goutte\Client;

class Bot
{

    protected $sites = array();
    
    /**
     *
     * @var Client
     */
    protected $client;
    
    /**
     *
     * @var Config
     */
    protected $config;

    public function registerBuiltInSites()
    {
        foreach (Finder::create()->files()->in(__DIR__ . '/Site') as $file) {
            $class = 'InstagramTakipci\\Site\\' . basename($file, '.php');
            $this->addSite(new $class());
        }
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

    public function setClient($client)
    {
        $this->client = $client;
    }
    
    public function getClient()
    {
        return $this->client;
    }
    
    public function setConfig($config)
    {
        $this->config = $config;
    }
    
    public function getConfig()
    {
        return $this->config;
    }

}
