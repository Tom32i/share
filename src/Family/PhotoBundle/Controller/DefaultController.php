<?php

namespace Family\PhotoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        $events  = array_filter($browser->listEvents(), function ($event) {
            return !$event['private'];
        });

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

    /**
     * @Route("/{event}/{photo}", name="source", defaults={"preset"=null})
     * @Route("/{event}/thumbnail/{photo}", name="thumbnail", defaults={"preset"="thumbnail"})
     * @Route("/{event}/full/{photo}", name="full", defaults={"preset"="full"})
     */
    public function imageAction(Request $request, $event, $photo, $preset = null)
    {
        $processor = $this->container->get('family_photo.processor');
        $filepath = $processor->process(sprintf('%s/%s', $event, $photo), $preset);

        if (!$filepath) {
            throw $this->createNotFoundException();
        }

        $response = new BinaryFileResponse(
            $filepath,
            200,  // status
            [
                'expires' => '30d',
                'max_age' => 30 * 24 * 60 * 60,
            ],
            true, // public
            null, // contentDisposition
            true, // autoEtag
            true  // autoLastModified
        );

        $response->isNotModified($request);

        return $response;
    }
}
