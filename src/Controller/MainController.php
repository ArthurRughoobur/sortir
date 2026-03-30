<?php

namespace App\Controller;

use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/mentions-légales', name: 'main_mentions')]
    public function mentionsLegales(): Response
    {
        return $this->render('main/mentions.html.twig',);
    }
    #[Route('/qui-sommes-nous', name: 'main_about')]
    public function aboutUs(): Response
    {
        return $this->render('main/about.html.twig',);
    }

}
