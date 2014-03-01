#!/bin/bash

set -e

if [ $# -ne 1 ]; then
  echo "Usage: `basename $0` <tag>"
  exit 65
fi

TAG=$1

#
# Tag & build master branch
#
git checkout master
git tag ${TAG}
box build

#
# Copy executable file into GH pages
#
cd fuzzy-octo-batman
git checkout gh-pages

cp ../instagramtakipci.phar downloads/instagramtakipci-${TAG}.phar
git add downloads/instagramtakipci-${TAG}.phar

SHA1=$(openssl sha1 ../instagramtakipci.phar)

JSON='name:"instagramtakipci.phar"'
JSON="${JSON},sha1:\"${SHA1}\""
JSON="${JSON},url:\"http://kobayakawa.github.io/fuzzy-octo-batman/downloads/instagramtakipci-${TAG}.phar\""
JSON="${JSON},version:\"${TAG}\""

if [ -f ../instagramtakipci.phar.pubkey ]; then
    cp ../instagramtakipci.phar.pubkey pubkeys/instagramtakipci-${TAG}.phar.pubkeys
    git add pubkeys/instagramtakipci-${TAG}.phar.pubkeys
    JSON="${JSON},publicKey:\"http://kobayakawa.github.io/fuzzy-octo-batman/pubkeys/instagramtakipci-${TAG}.phar.pubkey\""
fi

#
# Update manifest
#
cat manifest.json | jsawk -a "this.push({${JSON}})" | python -mjson.tool > manifest.json.tmp
mv manifest.json.tmp manifest.json
git add manifest.json

git commit -m "Bump version ${TAG}"

#
# Go back to master
#
cd ..
git checkout master

echo "New version created. Now you should run:"
echo "git push origin gh-pages"
echo "git push ${TAG}"