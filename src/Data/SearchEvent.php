<?php

namespace App\Data;

use App\Entity\Site;
use DateTime;
use Symfony\Component\Validator\Constraints\Date;

class SearchEvent
{
    /**
     * @var Site
     */
    public $site;

    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var DateTime
     */
    public $betweenFirstDate;

    /**
     * @var DateTime
     */
    public $betweenLastDate;

    /**
     * @var boolean
     */
    public $isHost = false;

    /**
     * @var boolean
     */
    public $isMember = false;

    /**
     * @var boolean
     */
    public $notMember = false;
    /**
     * @var boolean
     */
    public $passed = false;
}
