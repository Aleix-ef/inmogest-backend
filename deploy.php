<?php

namespace Deployer;

require 'recipe/laravel.php';

set('application', 'inmogest-backend');
set('repository', getenv('DEPLOY_REPOSITORY') ?: 'git@github.com:TU_USUARIO/inmogest-backend.git');
set('keep_releases', 3);

add('shared_files', ['.env']);
add('shared_dirs', ['storage']);
add('writable_dirs', ['storage', 'bootstrap/cache']);

host(getenv('DEPLOY_HOST') ?: 'TU_IP_EC2')
    ->set('remote_user', getenv('DEPLOY_USER') ?: 'ubuntu')
    ->set('identity_file', getenv('DEPLOY_IDENTITY_FILE') ?: '~/.ssh/id_rsa')
    ->set('deploy_path', getenv('DEPLOY_PATH') ?: '/var/www/inmogest-backend');

task('deploy:composer_install', function () {
    run('cd {{release_path}} && composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist');
});

task('deploy:artisan_prepare', function () {
    run('cd {{release_path}} && php artisan config:clear');
    run('cd {{release_path}} && php artisan route:clear');
    run('cd {{release_path}} && php artisan view:clear');
    run('cd {{release_path}} && php artisan cache:clear');
    run('cd {{release_path}} && php artisan migrate --force');
    run('cd {{release_path}} && php artisan storage:link || true');
});

task('deploy:restart_services', function () {
    run('sudo systemctl restart php8.3-fpm || true');
    run('sudo systemctl restart nginx || true');
});

after('deploy:update_code', 'deploy:composer_install');
after('deploy:composer_install', 'deploy:artisan_prepare');
after('deploy:symlink', 'deploy:restart_services');

before('deploy', 'deploy:unlock');
after('deploy:failed', 'deploy:unlock');
