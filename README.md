# Heroku Release Phase Demo App

A little example based on https://github.com/symfony/symfony-demo, and adapted for Postgres and Heroku, with release phase migrations sprinkled in.

First things first:

    $ git clone https://github.com/dzuelke/symfony-demo-heroku
    $ cd symfony-demo-heroku/

## Local execution

    $ composer install

Then figure it out from there :)

## Push to Heroku

### Version One

#### Prepare

    $ heroku create
    $ heroku buildpacks:set https://github.com/dzuelke/heroku-buildpack-php#releasephase
    $ heroku addons:create heroku-postgresql
    $ heroku config:set SYMFONY_ENV=prod
    $ heroku sudo labs:enable release-phase

#### Deploy

    $ git push heroku demo-step-1^{}:refs/heads/master

The last command will push tag "demo-step-1", which is what we want for now.

#### Check logs

    $ heroku logs --tail

You should see a migration successfully completing; this is creating the tables in the previously blank database:

    2015-06-29T22:42:57.303871+00:00 app[run.2658]: Migrating up to 20150629223845 from 0
    2015-06-29T22:42:58.211984+00:00 app[run.2658]:      -> CREATE SEQUENCE Users_id_seq INCREMENT BY 1 MINVALUE 1 START 1
    2015-06-29T22:42:58.311641+00:00 app[run.2658]:      -> CREATE INDEX IDX_A6E8F47C4B89032C ON Comments (post_id)
    2015-06-29T22:42:58.199064+00:00 app[run.2658]: 
    2015-06-29T22:42:58.199073+00:00 app[run.2658]:   ++ migrating 20150629223845
    2015-06-29T22:42:58.199074+00:00 app[run.2658]: 
    2015-06-29T22:42:58.199076+00:00 app[run.2658]:      -> CREATE SEQUENCE Comments_id_seq INCREMENT BY 1 MINVALUE 1 START 1
    2015-06-29T22:42:58.199377+00:00 app[run.2658]:      -> CREATE SEQUENCE Posts_id_seq INCREMENT BY 1 MINVALUE 1 START 1
    2015-06-29T22:42:58.215170+00:00 app[run.2658]:      -> CREATE TABLE Comments (id INT NOT NULL, post_id INT NOT NULL, content TEXT NOT NULL, authorEmail VARCHAR(255) NOT NULL, publishedAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
    2015-06-29T22:42:58.363843+00:00 app[run.2658]:      -> CREATE TABLE Posts (id INT NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, summary TEXT NOT NULL, content TEXT NOT NULL, authorEmail VARCHAR(255) NOT NULL, publishedAt TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
    2015-06-29T22:42:59.452448+00:00 app[run.2658]:      -> CREATE TABLE Users (id INT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, PRIMARY KEY(id))
    2015-06-29T22:42:59.883332+00:00 app[run.2658]:      -> CREATE UNIQUE INDEX UNIQ_D5428AEDF85E0677 ON Users (username)
    2015-06-29T22:43:00.010801+00:00 app[run.2658]:      -> CREATE UNIQUE INDEX UNIQ_D5428AEDE7927C74 ON Users (email)
    2015-06-29T22:43:00.134792+00:00 app[run.2658]:      -> ALTER TABLE Comments ADD CONSTRAINT FK_A6E8F47C4B89032C FOREIGN KEY (post_id) REFERENCES Posts (id) NOT DEFERRABLE INITIALLY IMMEDIATE
    2015-06-29T22:43:00.458834+00:00 app[run.2658]: 
    2015-06-29T22:43:00.458839+00:00 app[run.2658]:   ++ migrated (3.16s)
    2015-06-29T22:43:00.474627+00:00 app[run.2658]: 
    2015-06-29T22:43:00.474636+00:00 app[run.2658]:   ------------------------
    2015-06-29T22:43:00.474639+00:00 app[run.2658]: 
    2015-06-29T22:43:00.477469+00:00 app[run.2658]:   ++ finished in 3.16
    2015-06-29T22:43:00.479373+00:00 app[run.2658]:   ++ 10 sql queries
    2015-06-29T22:43:00.477833+00:00 app[run.2658]:   ++ 1 migrations executed

#### Load some data

Let's generate some demo data to play around with:

    $ heroku run "app/console doctrine:fixtures:load --no-interaction"

#### Test

    $ heroku open

You should see posts on the blog, and you should be able to edit posts yourself after logging in.

### Version Two

We will now fork this app, and push a new version of the code to it.

#### Fork

    $ heroku fork --from ... --to ...-staging
    $ heroku git:remote --remote staging --app ...-staging
    $ heroku sudo labs:enable release-phase --remote staging

(replace "..." with your app name)

#### Deploy

    $ git push staging master

You will notice that the migrations automatically run after the deploy, updating the database to give the posts table a "featured" flag column.

#### Test

    $ heroku open --remote staging

You should see posts on the blog. Take one further down the list in the admin panel, and mark the "featured" checkbox before saving. This post will now be pinned to the top of the list in the main blog page with a star symbol (the admin panel still has random/weird order).

At the same time, the main production app is obviously still without that flag.

### Pipelines!

It's time to use pipelines to promote the new feature, and have the database migration happen automatically.

#### Prepare

Make a pipeline on the staging app, with the first (production) app as the "downstream":

    $ heroku pipeline:add ... --app ...-staging

(replace "..." with your app name)

#### Promote

Promote staging to production:

    $ heroku pipeline:promote --remote staging

#### Check logs

    $ heroku logs --tail --remote heroku

You should see the migration to add the "featured" flag successfully completing on the main production application upon promotion of the release:

    2015-06-29T22:51:41.768066+00:00 heroku[api]: Promote vast-dawn-4816-staging v7 30d337a by dz@heroku.com
    2015-06-29T22:51:41.768066+00:00 heroku[api]: Release v6 created by dz@heroku.com
    2015-06-29T22:51:41.655532+00:00 heroku[api]: Starting process with command `/app/.heroku/bin/release` by dz@heroku.com
    2015-06-29T22:51:42.101377+00:00 heroku[web.1]: State changed from up to starting
    2015-06-29T22:51:45.663170+00:00 heroku[web.1]: Stopping all processes with SIGTERM
    2015-06-29T22:51:46.399290+00:00 app[web.1]: Going down, terminating child processes...
    2015-06-29T22:51:47.834978+00:00 heroku[web.1]: Process exited with status 0
    2015-06-29T22:51:49.510886+00:00 heroku[run.4993]: Starting process with command `/app/.heroku/bin/release`
    2015-06-29T22:51:50.106768+00:00 heroku[run.4993]: State changed from starting to up
    2015-06-29T22:51:52.722625+00:00 app[run.4993]:                                                               
    2015-06-29T22:51:52.722650+00:00 app[run.4993]:                     Application Migrations                    
    2015-06-29T22:51:52.722657+00:00 app[run.4993]:                                                               
    2015-06-29T22:51:52.722659+00:00 app[run.4993]: 
    2015-06-29T22:51:52.736363+00:00 app[run.4993]: Migrating up to 20150629232638 from 20150629223845
    2015-06-29T22:51:52.813183+00:00 app[run.4993]: 
    2015-06-29T22:51:52.813187+00:00 app[run.4993]:   ++ migrating 20150629232638
    2015-06-29T22:51:52.813189+00:00 app[run.4993]: 
    2015-06-29T22:51:52.817191+00:00 app[run.4993]:      -> ALTER TABLE posts ADD featured BOOLEAN NOT NULL DEFAULT false
    2015-06-29T22:51:54.030625+00:00 app[run.4993]: 
    2015-06-29T22:51:54.030630+00:00 app[run.4993]:   ++ migrated (1.29s)
    2015-06-29T22:51:54.041970+00:00 app[run.4993]: 
    2015-06-29T22:51:54.041974+00:00 app[run.4993]:   ------------------------
    2015-06-29T22:51:54.041975+00:00 app[run.4993]: 
    2015-06-29T22:51:54.042137+00:00 app[run.4993]:   ++ finished in 1.29
    2015-06-29T22:51:54.042250+00:00 app[run.4993]:   ++ 1 migrations executed
    2015-06-29T22:51:54.042385+00:00 app[run.4993]:   ++ 1 sql queries
    2015-06-29T22:51:55.021863+00:00 heroku[run.4993]: Process exited with status 0
    2015-06-29T22:51:55.387101+00:00 heroku[run.4993]: State changed from up to complete
    2015-06-29T22:51:49.085824+00:00 heroku[web.1]: Starting process with command `$(composer config bin-dir)/heroku-php-apache2 web/`
    2015-06-29T22:51:51.951229+00:00 app[web.1]: DOCUMENT_ROOT changed to 'web/'
    2015-06-29T22:51:52.003119+00:00 app[web.1]: Optimizing defaults for 1X dyno...
    2015-06-29T22:51:52.192666+00:00 app[web.1]: 4 processes at 128MB memory limit.
    2015-06-29T22:51:52.204698+00:00 app[web.1]: Starting php-fpm...
    2015-06-29T22:51:54.210908+00:00 app[web.1]: Starting httpd...
    2015-06-29T22:51:54.588372+00:00 heroku[web.1]: State changed from starting to up

#### Test

    $ heroku open --remote heroku

The production blog now has the "featured" flag capability as well; give it a try!