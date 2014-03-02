<?php

namespace InstagramTakipci\Console;

use Symfony\Component\Console\Application as BaseApplication;
use InstagramTakipci\Console\Command\MainCommand;
use InstagramTakipci\Console\Command\UpdateCommand;

class Application extends BaseApplication
{
	/**
     * Constructor.
     */
    public function __construct()
    {
        error_reporting(-1);

        parent::__construct('InstagramTakipci', '@package_version@');

        $this->add(new MainCommand());
		$this->add(new UpdateCommand());
    }
}