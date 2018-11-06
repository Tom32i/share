<?php
namespace Deployer;

require 'recipe/symfony3.php';

// Configuration

set('repository', function () { return runLocally('git config --get remote.origin.url'); });
set('branch', function () { return runLocally('git rev-parse --abbrev-ref HEAD'); });
set('symfony_env', 'prod');
set('user', 'tom32i');
set('allow_anonymous_stats', false);
set('http_user', 'tom32i');
set('writable_mode', 'chmod');
set('writable_use_sudo', false);
set('clear_use_sudo', false);

add('shared_files', []);
add('shared_dirs', ['var/photos', 'var/photos-cache']);
add('writable_dirs', ['var/photos', 'var/photos-cache']);

// Hosts

host('tom32i.fr')
    ->stage('production')
    ->set('deploy_path', '/home/tom32i/family-photos');

host('deployer.vm')
    ->stage('production')
    ->set('deploy_path', '/home/tom32i/family-photos');

// Tasks

desc('Install nodejs dependencies');
task('deploy:node:vendors', function () {
    run('cd {{release_path}} && npm --no-spin install');
});
after('deploy:vendors', 'deploy:node:vendors');

desc('Build assets');
task('deploy:assets:build', function () {
    run('cd {{release_path}} && make build@prod');
});
after('deploy:assetic:dump', 'deploy:assets:build');

desc('Generate missing thumbnails');
task('thumbnail:generate', function () {
    run('cd {{release_path}} && make thumbnail@prod');
});

desc('Clear all thumbnails');
task('thumbnail:clear', function () {
    run('cd {{release_path}} && make clear-thumbnail@prod');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
before('deploy:symlink', 'thumbnail:generate');
