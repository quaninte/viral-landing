server 'scn.brodev.com', :app, :web, :primary => true
set :stage_folder, "production"

set :domain, "do.brodev.com"
set :user, "bkfront"
set :deploy_to, "/home/bkfront/sites/landing_prod"

role :web, domain
role :app, domain
role :db, domain, :primary => true

# git branch
set :branch, "prod"