<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MainController extends AbstractController
{
    #[Route('/mentionsLegales', name: 'main_mentions_légales')]
    public function mentionsLegales(): Response
    {
        return $this->render('main/mentions_légales.html.twig',);
    }
    #[Route('/aboutUs', name: 'main_about_us')]
    public function aboutUs(): Response
    {
        return $this->render('main/aboutUs.html.twig',);
    }
}
