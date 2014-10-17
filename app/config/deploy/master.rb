server 't.brodev.com', :app, :web, :primary => true
set :stage_folder, "master"

set :domain, "t.brodev.com"
set :user, "beeketing"
set :deploy_to, "/home/beeketing/landing_master"

role :web, domain
role :app, domain
role :db, domain, :primary => true

# git branch
set :branch, "master"