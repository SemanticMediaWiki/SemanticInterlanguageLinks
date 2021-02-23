#!/bin/bash
set -ex

BASE_PATH=$(pwd)
MW_INSTALL_PATH=$BASE_PATH/../mw

## Install
echo -e "Running MW root composer install build on $TRAVIS_BRANCH \n"

cd $MW_INSTALL_PATH

if [ "$SIL" != "" ]
then
  composer require 'mediawiki/semantic-interlanguage-links='$SIL --update-with-dependencies
else
  composer init --stability dev
  composer require mediawiki/semantic-interlanguage-links "dev-master" --dev --update-with-dependencies

  cd extensions
  cd SemanticInterlanguageLinks

  # Pull request number, "false" if it's not a pull request
  # After the install via composer an additional get fetch is carried out to
  # update th repository to make sure that the latests code changes are
  # deployed for testing
  if [ "$TRAVIS_PULL_REQUEST" != "false" ]
  then
    git fetch origin +refs/pull/"$TRAVIS_PULL_REQUEST"/merge:
    git checkout -qf FETCH_HEAD
  else
    git fetch origin "$TRAVIS_BRANCH"
    git checkout -qf FETCH_HEAD
  fi

  cd ../..
fi

composer dump-autoload


## Configure
cd $MW_INSTALL_PATH
echo 'wfLoadExtension( "SemanticInterlanguageLinks" );' >> LocalSettings.php
