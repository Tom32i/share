<?php

namespace Family\PhotoBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;

class GenerateThumbnailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('thumbnail:generate')
            ->setDescription('Generate thumbnails for all photos')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Generating missing thumbnails...');

        $processor = $this->getContainer()->get('family_photo.processor');
        $browser = $this->getContainer()->get('family_photo.browser');

        foreach ($browser->listEvents() as $event) {
            $output->writeln(sprintf('<comment>%s</comment>', trim($event['title'])));
            $progress = new ProgressBar($output, count($event['photos']));

            $progress->start();

            foreach ($event['photos'] as $index => $photo) {
                $processor->process(sprintf('%s/%s', $event['name'], $photo['name']), 'full');
                $processor->process(sprintf('%s/%s', $event['name'], $photo['name']), 'thumbnail');
                $progress->setMessage($photo['name']);
                $progress->advance();
            }

            $progress->finish();
            $output->writeln('');
        }

        $output->writeln('<info>Done!</info>');
    }
}
