<?php 

require_once __DIR__."/vendor/autoload.php";
require_once "recipe/common.php";

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

set('ssh_type', 'ext-ssh2');
set('default_stage', 'staging');
env('deploy_path', '/var/www');
set('copy_dirs', [
    'app/commands',
    'app/config',
    'app/controllers',
    'app/database',
    'app/lang',
    'app/models',
    'app/src',
    'app/start',
    'app/tests',
    'app/views',
    'app/filters.php',
    'app/routes.php',
    'bootstrap',
    'public',
    'composer.json',
    'composer.lock',
    'artisan',
    '.env',
]);
// set('shared_files', ['.env']);
set('shared_dirs', [
    'app/storage/cache',
    'app/storage/logs',
    'app/storage/meta',
    'app/storage/sessions',
    'app/storage/views',
]);
set('writable_dirs', get('shared_dirs'));
set('http_user', 'www-data');

server('digitalocean', '104.131.27.106')
    ->identityFile()
    ->user($_ENV['staging_server_user'])
    ->password($_ENV['staging_server_password'])
    ->stage('staging');

task('deploy:upload', function() {
    $files = get('copy_dirs');
    $releasePath = env('release_path');

    foreach ($files as $file)
    {
        upload($file, "{$releasePath}/{$file}");
    }
});

task('deploy:staging', [
    'deploy:prepare',
    'deploy:release',
    'deploy:upload',
    'deploy:shared',
    'deploy:writable',
    'deploy:symlink',
    'deploy:vendors',
    'current',// print current release number
])->desc('Deploy application to staging.');

after('deploy:staging', 'success');
