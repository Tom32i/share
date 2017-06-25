<?php

namespace Family\PhotoBundle\Twig;

/**
 * Family Photo Twig Extension
 */
class FamilyPhotoExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * Title
     *
     * @var string
     */
    protected $title;

    /**
     * Presets
     *
     * @var array
     */
    protected $presets;


    /**
     * Construct
     *
     * @param string $title
     * @param array $presets
     */
    public function __construct($title, array $presets)
    {
        $this->title = $title;
        $this->presets = $presets;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals()
    {
        return [
            'site_title' => $this->title,
            'presets' => $this->presets,
        ];
    }
}
