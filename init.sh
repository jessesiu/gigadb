#!/bin/bash
FILE=./assets
ZIPFILE=<link to zipfile>

if [[ -a $FILE ]];
then
    echo "assets folder exists!"
else
    echo "Assets folder does not exist. Downloading zip file..."
    wget --no-check-certificate -O ./gigadb-cogini.zip $ZIPFILE
    unzip -q ./gigadb-cogini.zip
    cd gigadb-cogini
    pwd
    ls -l
    echo "Moving files..."
    mv assets ..
    mv images ..
    mv protected/config/local.php ../protected/config
    mv protected/config/data ../protected/config
    mv protected/data/data_fresh.sql ../protected/data
    mv protected/runtime ../protected
    mv protected/scripts/data/dbchanges.txt ../protected/scripts/data
    mv protected/scripts/data/lastIndexer.txt ../protected/scripts/data
    mv protected/scripts/data/lastdataset.txt ../protected/scripts/data
    mv protected/scripts/set_env.sh ../protected/scripts/set_env.sh
    cd ..
    echo "Cleaning up files..."
    rm -fr gigadb-cogini
    rm gigadb-cogini.zip
    echo "Done!"
fi
