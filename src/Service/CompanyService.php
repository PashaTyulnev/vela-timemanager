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
        if($user->getCompany() === null){
            return $user->getCompanyObject()->getCompany();
        }else{
            return $user->getCompany();
        }

    }

    public function getCompanyObjects(): array
    {
        $companyObjects = $this->getCurrentCompany()->getCompanyObjects();
        $objectsForReturn = [];
        $i = 0;
        foreach ($companyObjects as $object){
            $objectsForReturn[$i]['street'] = $object->getStreet();
            $objectsForReturn[$i]['number'] = $object->getNumber();
            $objectsForReturn[$i]['id'] = $object->getId();

            if($object->getMainUser()->getValues() != []){
                $email = $object->getMainUser()->getValues()[0]->getEmail();
            }else{
                $email = "bitte Seite neustarten";
            }
            $objectsForReturn[$i]['username']  = $email;
            $objectsForReturn[$i]['password']  = $object->getPassword();
            $i++;
        }

       return $objectsForReturn;
    }
}