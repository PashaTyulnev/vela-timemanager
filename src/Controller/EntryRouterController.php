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
    private LoginController $loginController;

    public function __construct(Security $security,EmployerController $employerController,AdminController $adminController,LoginController $loginController){

        $this->security = $security;
        $this->user = $security->getUser();

        $this->employerController = $employerController;
        $this->adminController = $adminController;
        $this->loginController = $loginController;
    }
    #[Route('/entryRouter', name: 'entryRouter')]
    public function index(): \Symfony\Component\HttpFoundation\Response
    {
        if($this->user != null){
            foreach ($this->user->getRoles() as $role){
                if($role == "ROLE_ADMIN"){
                    return $this->adminController->index();
                }
            }
            return  $this->employerController->index();
        }

    }
}