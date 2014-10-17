set :application, "SCN-Beeketing-Landing"
set :app_path,    "app"

set :repository,  "git@bitbucket.org:brodev/beeketing-landing.git"
set :scm,         :git

set :model_manager, "doctrine"

set  :keep_releases,  3
set  :use_sudo,      false

# Need to clear *_dev controllers
set :clear_controllers, false

# Be more verbose by uncommenting the following line
logger.level = Logger::MAX_LEVEL

# stages
set :stages,        %w(production staging master)
set :default_stage, "master"
set :stage_dir,     "app/config/deploy"
require 'capistrano/ext/multistage'

task :upload_parameters do
  path = latest_release;

  # upload parameters file
  capifony_pretty_print "--> Uploading parameters file"
  parameters_file = "app/config/parameters." + stage_folder + ".yml"
  parameters_relative_target = "app/config/parameters.yml"
  parameters_target = path + "/" + parameters_relative_target
  try_sudo "mkdir -p #{File.dirname(parameters_target)}"
  top.upload(parameters_file, parameters_target)
  capifony_puts_ok
end

before 'deploy:share_childs', 'upload_parameters'

set :use_composer, true
set :copy_vendors, true

set :shared_files,      []
set :shared_children,     [app_path + "/logs", "tools/gulp/node_modules"]
set :use_composer, true
set :copy_vendors, true
set :composer_options,  "--verbose --prefer-dist"
set :writable_dirs,       [app_path + "/cache", app_path + "/logs"]
set :asset_children,       ['web/dist', 'web/bundles']
set :webserver_user,      "www-data"

# run import:config command
before 'symfony:cache:warmup', 'symfony:import_config'
namespace :symfony do
  desc "Import config, create symlink etc... for system"
  task :import_config, :except => { :no_release => true } do

    # update assets version
    capifony_pretty_print "--> Increase assets version"

    timestamp = Time.now.to_i
    run "cd #{latest_release} && #{php_bin} app/console assets_version:set #{timestamp} -e prod"
    run "cd #{latest_release} && #{php_bin} app/console cache:clear -e prod"
    capifony_puts_ok

    capifony_pretty_print "--> Dump assets"
    # dump helper js
    run "cd #{latest_release} && mkdir -p web/dist/js && #{php_bin} app/console scn:dump-helper -e prod"

    # dump js routing
    run "cd #{latest_release} && #{php_bin} app/console fos:js-routing:dump -e prod"

    # build assets with gulp
    run "cd #{latest_release} && bower install"
    run "cd #{latest_release}/tools/gulp && npm install && gulp prod"
    run "cd #{latest_release}/tools/gulp && gulp prod"
    capifony_puts_ok

  end
end

# touch log files
before 'symfony:composer:copy_vendors', 'symfony:touch_logs'
namespace :symfony do
  desc "Touch log files"
  task :touch_logs, :except => { :no_release => true } do
      capifony_pretty_print "--> Touching log files to by pass permission issue"

      ["prod", "dev"].each do |env|
        run "logFile=#{shared_path}/app/logs/#{env}.log;mkdir -p $(dirname $logFile);touch $logFile;chmod -R 777 $logFile;"
      end
      capifony_puts_ok
  end
end

# skip update composer
namespace :symfony do
  # composer
  namespace :composer do
    desc "Gets composer and installs it"
    task :get, :roles => :app, :except => { :no_release => true } do

          capifony_pretty_print "--> Skipping update composer"
          capifony_puts_ok
    end
  end
end

# override install assets
namespace :symfony do
    namespace :assets do
        desc "Install assets"
        task :install, :roles => :app, :except => { :no_release => true } do
            capifony_pretty_print "--> Installing bundle's assets"
            run "cd #{latest_release} && #{php_bin} app/console assets:install web --symlink --env=prod"
            capifony_puts_ok
        end
    end
end

# clear cache before warmup
before 'symfony:cache:warmup', 'symfony:clear_cache'
after 'symfony:cache:warmup', 'symfony:set_permissions'
namespace :symfony do
  desc "Clear cache"
  task :clear_cache, :except => { :no_release => true } do
    # clear cache
    capifony_pretty_print "--> Clear cache"
    run "cd #{latest_release} && #{php_bin} app/console cache:clear -e prod"
    capifony_puts_ok
  end

  desc "Set permission for writable dirs"
  task :set_permissions, :except => { :no_release => true } do
    puts "Setting permission"

    capifony_pretty_print "--> Set permission for writable dirs"
    writable_dirs.each do |dir|
        run "sudo chmod -R 777 #{latest_release}/#{dir}"
    end
    capifony_puts_ok
  end
end

# sudo clean up
desc "Clean up with sudo"
task :sudo_cleanup, :roles => :app, :except => { :no_release => true } do
    run "ls -1dt #{releases_path}/* | tail -n +4 |  xargs sudo rm -rf"
end