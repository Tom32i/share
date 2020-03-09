<?php

namespace Family\PhotoBundle\Service;

/**
 * File Browser
 */
class Browser
{
    /**
     * Photos path
     *
     * @var String
     */
    protected $path;

    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = preg_replace('#^(.*)/?#', '$1', $path);
    }

    /**
     * List events
     *
     * @return array
     */
    public function listEvents()
    {
        if (!file_exists($this->path) || !is_dir($this->path)) {
            return null;
        }

        $events = [];

        if ($handle = opendir($this->path)) {

            while (false !== ($entry = readdir($handle))) {

                $entryPath = $this->path . '/' . $entry;

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
        $directory = sprintf('%s/%s', $this->path, $name);
        $photos    = [];
        $videos    = [];
        $download  = null;
        $title     = null;
        $private   = false;
        $date      = null;
        $sort      = 'sortByDateAsc';

        if (!file_exists($directory) || !is_dir($directory)) {
            return null;
        }

        if ($handle = opendir($directory)) {

            while (false !== ($entry = readdir($handle))) {

                $file = sprintf('%s/%s', $directory, $entry);

                if (preg_match('#^.*\.(jpg|jpeg|png|gif)$#i', $entry)) {

                    $exif = @exif_read_data($file);

                    $photos[] = [
                        'name' => $entry,
                        'exif' => $exif,
                        'date' => $exif && isset($exif['DateTime']) ? $exif['DateTime'] : null,
                    ];

                    if (!$date) {
                        $date = $photos[count($photos)-1]['date'];
                    }
                }

                if (preg_match('#^.*\.(mov)$#i', $entry)) {
                    $videos[] = [
                        'name' => $entry,
                    ];
                }

                if (preg_match('#^.*\.zip$#i', $entry)) {
                    $download = [
                        'name' => $entry,
                        'path' => $file,
                    ];
                }

                if (preg_match('#^\.title$#i', $entry)) {
                    $title =  trim(file_get_contents($file));
                }

                if (preg_match('#^\.sort$#i', $entry)) {
                    $sort = trim(file_get_contents($file));
                }

                if (preg_match('#^\.private$#i', $entry)) {
                    $private = true;
                }
            }

            closedir($handle);
        }

        try {
            usort($photos, [$this, $sort]);
        } catch (\Exception $exception) {
            usort($photos, [$this, 'sortByDateAsc']);
        }

        return [
            'name'     => $name,
            'title'    => $title,
            'private'  => $private,
            'date'     => $date,
            'photos'   => $photos,
            'videos'   => $videos,
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

    /**
     * Sort by name
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    protected function sortByName($a, $b)
    {
        return $a['name'] == $b['name'] ? 0 : ($a['name'] > $b['name'] ? 1 : -1);
    }
}
