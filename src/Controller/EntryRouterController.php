<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Core\Security;

class EntryRouterController extends AbstractController
{
    private Security $security;
    private ?\Symfony\Component\Security\Core\User\UserInterface $user;
    private EmployerController $employerController;
    private AdminController $adminController;

    public function __construct(Security $security,EmployerController $employerController,AdminController $adminController){

        $this->security = $security;
        $this->user = $security->getUser();

        $this->employerController = $employerController;
        $this->adminController = $adminController;
    }
    #[Route('/router', name: 'entryRouter')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        foreach ($this->user->getRoles() as $role){
            if($role == "ROLE_ADMIN"){
                return $this->adminController->index();
            }
        }
        return  $this->employerController->index();

    }
}