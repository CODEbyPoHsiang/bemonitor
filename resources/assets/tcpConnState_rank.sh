#!/bin/bash
OUTPUT=/resources/assets/tcpConnState_rank.txt
JSON=/resources/assets/tcpConnState_rank.json

cd /resources/assets/tcp
if [ -z $1 ] ; then
    sed -e /listen/d -e /127.0.0.1/d *tcpConnState | cut -d. -f7-10 | sort | uniq -c | sort -rn | awk '{print $2" "$1}' > $OUTPUT
    truncate -s -1 $OUTPUT # 去除最後一行的 enter
    jq -sR 'split("\n") | map(split(" ")) | map({"ip":.[0],"count":.[1]})' $OUTPUT > $JSON
else # with ip
    grep $1 *tcpConnState
fi