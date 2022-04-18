<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\u;

class VinylController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function homepage(): Response
    {
        $tracks = [
            ['song' => 'track1', 'artist' => 'artist1'],
            ['song' => 'track2', 'artist' => 'artist2'],
            ['song' => 'track3', 'artist' => 'artist3'],
            ['song' => 'track4', 'artist' => 'artist4'],
        ];

        return $this->render('homepage.html.twig', [
            'title' => 'Homepage Title',
            'tracks' => $tracks,
        ]);
    }

    #[Route('/browse/{slug}', name: 'app_browse')]
    public function browse(string $slug = null): Response
    {
        $title = $slug ? u($slug)->title() : 'All';

        return $this->render('browse.html.twig', [
            'genre' => $title,
        ]);
    }
}