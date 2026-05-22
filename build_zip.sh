#!/bin/bash

SKIP='build_zip.sh'
DIR="cargus";
echo "remove ${DIR} folder"
rm -r ${DIR}
mkdir "${DIR}";
for f in *
  do 
    if [ "${SKIP}" != "${f}" ] && [ "${DIR}" != "${f}" ]
      then
        cp -R "${f}" ${DIR}
    fi         
done

echo "done"