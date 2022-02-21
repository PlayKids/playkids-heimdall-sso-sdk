<?php

namespace Heimdall\Service;

use Heimdall\Object\Heimdall;

class HeimdallService
{

    /**
     * @var Heimdall
     */
    private $heimdall;

    public function setClient(Heimdall $heimdall) {
        $this->heimdall = $heimdall;
    }

    public function getClient()
    {
        return $this->heimdall;
    }
}