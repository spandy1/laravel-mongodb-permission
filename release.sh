set -e
VERSION=$1
if [ -z "$VERSION" ]; then echo "usage: ./release.sh 1.2.0"; exit 1; fi

git tag -a v$VERSION -m "v$VERSION"
git push origin v$VERSION