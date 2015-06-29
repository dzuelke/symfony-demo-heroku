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

We will now fork this app, and push a new version of the code to it. We can run migrations by hand in this case.

#### Fork

    $ heroku fork --from ... --to ...-staging
    $ heroku git:remote --remote staging --app ...-staging

#### Deploy

    $ git push staging master

#### Migrate

    $ heroku run --remote staging "composer release"

#### Test

    $ heroku open --remote staging

You should see posts on the blog. Take one further down the list in the admin panel, and mark the "featured" checkbox before saving. This post will now be at the top of the list.

### Pipelines!

#### Prepare

Make a pipeline on the staging app, with the first (production) app as the "downstream":

    $ heroku pipeline:add ... --app ...-staging

#### Promote

Promote staging to production:

    $ heroku pipeline:promote --remote staging
