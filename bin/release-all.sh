#!/usr/bin/env bash

set -e
set -x

# Make sure the release version is provided.
if (( "$#" != 1 ))
then
    echo "Version has to be provided."

    exit 1
fi

VERSION=$1

# Always prepend with "v"
if [[ $VERSION != v*  ]]
then
    VERSION="v$VERSION"
fi

function split()
{
    SHA1=`splitsh-lite --prefix=$1 --origin="tags/$VERSION"`
    git push $2 "$SHA1:refs/tags/$VERSION"
}

function remote()
{
    git remote add $1 $2 || true
}

git tag -s $VERSION -m "Tagging $VERSION"
git push --tags

remote core git@github.com:pagerfanta-packages/core.git
remote doctrine-collections git@github.com:pagerfanta-packages/doctrine-collections-adapter.git
remote doctrine-dbal git@github.com:pagerfanta-packages/doctrine-dbal-adapter.git
remote doctrine-mongodb-odm git@github.com:pagerfanta-packages/doctrine-mongodb-odm-adapter.git
remote doctrine-orm git@github.com:pagerfanta-packages/doctrine-orm-adapter.git
remote doctrine-phpcr-odm git@github.com:pagerfanta-packages/doctrine-phpcr-odm-adapter.git
remote elastica git@github.com:pagerfanta-packages/elastica-adapter.git
remote solarium git@github.com:pagerfanta-packages/solarium-adapter.git
remote twig git@github.com:pagerfanta-packages/twig.git

split 'lib/Core' core
split 'lib/Adapter/Doctrine/Collections' doctrine-collections
split 'lib/Adapter/Doctrine/DBAL' doctrine-dbal
split 'lib/Adapter/Doctrine/MongoDBODM' doctrine-mongodb-odm
split 'lib/Adapter/Doctrine/ORM' doctrine-orm
split 'lib/Adapter/Doctrine/PHPCRODM' doctrine-phpcr-odm
split 'lib/Adapter/Elastica' elastica
split 'lib/Adapter/Solarium' solarium
split 'lib/Twig' twig
