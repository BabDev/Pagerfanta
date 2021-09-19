#!/usr/bin/env bash

set -e
set -x

CURRENT_BRANCH="2.x"

function split()
{
    SHA1=`splitsh-lite --prefix=$1`
    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

function remote()
{
    git remote add $1 $2 || true
}

git pull origin $CURRENT_BRANCH

remote core git@github.com:Pagerfanta/core.git
remote doctrine-collections git@github.com:Pagerfanta/doctrine-collections-adapter.git
remote doctrine-dbal git@github.com:Pagerfanta/doctrine-dbal-adapter.git
remote doctrine-mongodb-odm git@github.com:Pagerfanta/doctrine-mongodb-odm-adapter.git
remote doctrine-orm git@github.com:Pagerfanta/doctrine-orm-adapter.git
remote doctrine-phpcr-odm git@github.com:Pagerfanta/doctrine-phpcr-odm-adapter.git
remote elastica git@github.com:Pagerfanta/elastica-adapter.git
remote solarium git@github.com:Pagerfanta/solarium-adapter.git
remote twig git@github.com:Pagerfanta/twig.git

split 'lib/Core' core
split 'lib/Adapter/Doctrine/Collections' doctrine-collections
split 'lib/Adapter/Doctrine/DBAL' doctrine-dbal
split 'lib/Adapter/Doctrine/MongoDBODM' doctrine-mongodb-odm
split 'lib/Adapter/Doctrine/ORM' doctrine-orm
split 'lib/Adapter/Doctrine/PHPCRODM' doctrine-phpcr-odm
split 'lib/Adapter/Elastica' elastica
split 'lib/Adapter/Solarium' solarium
split 'lib/Twig' twig
