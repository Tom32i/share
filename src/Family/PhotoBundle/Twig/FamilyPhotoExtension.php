<?php

namespace Family\PhotoBundle\Twig;

/**
 * Family Photo Twig Extension
 */
class FamilyPhotoExtension extends \Twig_Extension
{
    /**
     * Title
     *
     * @var string
     */
    protected $title;

    /**
     * Construct
     *
     * @param string $title
     */
    public function __construct($title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return ['site_title' => $this->title];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'family_photo_extension';
    }
}