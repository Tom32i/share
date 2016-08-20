<?php

namespace Family\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Default Controller
 */
class DefaultController extends Controller
{
    /**
     * @Route("/", name="events")
     * @Template()
     */
    public function indexAction()
    {
        $browser = $this->container->get('family_photo.browser');
        $events  = $browser->listEvents();

        return ['events' => $events];
    }

    /**
     * @Route("/{name}", name="event")
     * @Template()
     */
    public function eventAction($name)
    {
        $browser = $this->container->get('family_photo.browser');
        $event   = $browser->readEvent($name);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return ['event' => $event];
    }
}
