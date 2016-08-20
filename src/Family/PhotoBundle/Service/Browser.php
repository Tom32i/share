<?php

namespace Family\PhotoBundle\Service;

/**
 * File Browser
 */
class Browser
{
    /**
     * Web path
     *
     * @var String
     */
    protected $web;

    /**
     * Photos path
     *
     * @var String
     */
    protected $path;

    /**
     * Constructor
     *
     * @param string $web
     * @param string $path
     */
    public function __construct($web, $path)
    {
        $this->web  = preg_replace('#^(.*)/?#', '$1', $web);
        $this->path = preg_replace('#^(.*)/?#', '$1', $path);
    }

    /**
     * List events
     *
     * @return array
     */
    public function listEvents()
    {
        $directory = $this->web . '/' . $this->path;
        $events    = [];

        if (!file_exists($directory) || !is_dir($directory)) {
            return null;
        }

        if ($handle = opendir($directory)) {

            while (false !== ($entry = readdir($handle))) {

                $entryPath = $directory . '/' . $entry;
                $titlePath = $entryPath . '/' . '.title';

                if (!preg_match('#^\.#i', $entry) && is_dir($entryPath)) {
                    $events[] = $this->readEvent($entry);
                }
            }

            closedir($handle);
        }

        usort($events, [$this, 'sortByDate']);

        return $events;
    }

    /**
     * Read an event
     *
     * @param string $name
     *
     * @return array
     */
    public function readEvent($name)
    {
        $path      = $this->path . '/' . $name;
        $directory = $this->web . '/' . $path;
        $photos    = [];
        $download  = null;
        $title     = null;
        $date      = null;

        if (!file_exists($directory) || !is_dir($directory)) {
            return null;
        }

        if ($handle = opendir($directory)) {

            while (false !== ($entry = readdir($handle))) {

                if (preg_match('#^.*\.(jpg|jpeg|png|gif)$#i', $entry)) {

                    $exif = exif_read_data($directory . '/' . $entry);

                    $photos[] = [
                        'name' => $entry,
                        'path' => $path . '/' . $entry,
                        'exif' => $exif,
                        'date' => $exif ? $exif['DateTime'] : null,
                    ];

                    if (!$date) {
                        $date = $photos[count($photos)-1]['date'];
                    }
                }

                if (preg_match('#^.*\.zip$#i', $entry)) {
                    $download = [
                        'name' => $entry,
                        'path' => $path . '/' . $entry,
                    ];
                }

                if (preg_match('#^\.title$#i', $entry)) {
                    $title = file_get_contents($directory . '/' . $entry);
                }
            }

            closedir($handle);
        }

        usort($photos, [$this, 'sortByDateAsc']);

        return [
            'name'     => $name,
            'title'    => $title,
            'date'     => $date,
            'photos'   => $photos,
            'download' => $download,
        ];
    }

    /**
     * Sort by date
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function sortByDate($a, $b)
    {
        return $a['date'] == $b['date'] ? 0 : ($a['date'] > $b['date'] ? -1 : 1);
    }

    /**
     * Sort by date
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function sortByDateAsc($a, $b)
    {
        return $a['date'] == $b['date'] ? 0 : ($a['date'] > $b['date'] ? 1 : -1);
    }
}