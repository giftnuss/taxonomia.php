#!/bin/bash

set -x
set -e

PYTHON=python2
VIRTUALENV_VERSION=15.1.0

pwd=$(pwd)
if [ -x "$pwd/bin/pdf2txt" ] ; then
   echo "pdf2txt already installed."
   exit
fi

tempdir=$(mktemp -d)
cd $tempdir

curl -L -O https://github.com/pypa/virtualenv/archive/${VIRTUALENV_VERSION}.zip
unzip ${VIRTUALENV_VERSION}.zip
cd virtualenv-${VIRTUALENV_VERSION}
$PYTHON virtualenv.py ../myVE
cd ..

. myVE/bin/activate

pip install pyinstaller

git clone --depth=1 https://github.com/euske/pdfminer.git

cd pdfminer
pyinstaller -F tools/pdf2txt.py
pyinstaller -F tools/dumppdf.py

mkdir -p $pwd/bin
cp dist/pdf2txt $pwd/bin
cp dist/dumppdf $pwd/bin
chmod +x $pwd/bin/pdf2txt
chmod +x $pwd/bin/dumppdf

rm -rf $tempdir
