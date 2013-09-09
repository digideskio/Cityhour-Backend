#!/bin/bash

url="http://127.0.0.1:5000/v1/push/"
all=$1

pack=0
i=0
last=0

echo ${all} | tr "," "\\n" | \
{ while read line
    do
        if [ 9 -le ${i} ]
        then
            curl -silent -output > /dev/null ${url}'?ids='${pack} &
            i=0
            pack=0
        fi
        pack=${pack}','${line}
        i=$((i+1))
        last=${pack}
    done
    if [ ${pack} != '0,' ]
    then
        curl -silent -output > /dev/null /dev/null ${url}'?ids='${pack} &
    fi
    wait
}