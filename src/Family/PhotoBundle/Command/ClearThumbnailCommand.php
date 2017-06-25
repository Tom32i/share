<?php

namespace Family\PhotoBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;

class ClearThumbnailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('thumbnail:clear')
            ->setDescription('Clear all cached thumbnails')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Clearing all thumbnails...');

        $processor = $this->getContainer()->get('family_photo.processor');
        $browser = $this->getContainer()->get('family_photo.browser');

        foreach ($browser->listEvents() as $event) {
            $output->writeln(sprintf('<comment>%s</comment>', trim($event['title'])));
            $progress = new ProgressBar($output, count($event['photos']));

            $progress->start();

            foreach ($event['photos'] as $index => $photo) {
                $processor->clear(sprintf('%s/%s', $event['name'], $photo['name']));
                $progress->setMessage($photo['name']);
                $progress->advance();
            }

            $progress->finish();
            $output->writeln('');
        }

        $output->writeln('<info>Done!</info>');
    }
}
