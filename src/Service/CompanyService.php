<?php

namespace App\Service;

use Symfony\Component\Security\Core\Security;

class CompanyService
{
    private Security $security;

    public function __construct(Security $security){

        $this->security = $security;
    }
    public function getCurrentObject()
    {
        $user = $this->security->getUser();

        if($user->getCompanyObject() !== null){
            return $user->getCompanyObject();
        }
        else{
            return false;
        }

    }

    public function getCurrentCompany(){
        $user = $this->security->getUser();
        return $user->getCompany();
    }

    public function getCompanyObjects(){
       return $this->getCurrentCompany()->getCompanyObjects();
    }
}