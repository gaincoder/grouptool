<?php


namespace GrouptoolUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class GrouptoolUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}