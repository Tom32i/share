<?php

// All Deployer recipes are based on `recipe/common.php`.
require 'recipe/symfony3.php';

// Define a server for deployment.
// Let's name it "prod" and use port 22.
server('demo', 'deployer.dev', 22)
    ->user('tom32i')
    ->forwardAgent() // You can use identity key, ssh config, or username/password to auth on the server.
    ->stage('production')
    ->env('deploy_path', '/home/tom32i/family-photos'); // Define the base path to deploy your project to.

// Define a server for deployment.
// Let's name it "prod" and use port 22.
server('prod', 'dédié', 22)
    ->user('tom32i')
    ->forwardAgent() // You can use identity key, ssh config, or username/password to auth on the server.
    ->stage('production')
    ->env('deploy_path', '/home/tom32i/family-photos'); // Define the base path to deploy your project to.

// Specify the repository from which to download your project's code.
// The server needs to have git installed for this to work.
// If you're not using a forward agent, then the server has to be able to clone
// your project from this repository.
set('repository', 'git@github.com:Tom32i/Family-Photos.git');

set('http_user', 'www-data');
set('writable_use_sudo', false);
set('clear_use_sudo', false);

set('shared_dirs', array_merge(get('writable_dirs'), ['web/photos']));
set('writable_dirs', array_merge(get('writable_dirs'), ['var/sessions/prod', 'web/photos']));

task('deploy:npm', function () {
    run("cd {{release_path}} && npm install");
})->desc('Install nodejs dependencies');

task('deploy:assetic:dump', function () {
    run('cd {{release_path}} && ./node_modules/.bin/gulp build');
})->desc('Dump assets');

after('deploy:vendors', 'deploy:npm');
