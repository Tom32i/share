<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Tom32i\ShowcaseBundle\Service\Browser;

class EventController extends AbstractController
{
    public function __construct(private Browser $browser)
    {
    }

    #[Route('/{slug}', name: 'event_show')]
    public function show(string $slug): Response
    {
        $event = $this->browser->read($slug, ['[slug]' => true]);

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }
}
