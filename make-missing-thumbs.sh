#!/bin/bash

## A helper script to be run on the hosting server if images have been uploaded and for 
## whatever reason thumbnails were not properly generated.  This needs to run as a user with 
## read/write privs to the `img` directory tree


IMG_DIR=./img


for large_image in `find ${IMG_DIR} -name \*jpg | grep -v thumb`; do
	base_name=${large_image%.*}
	thumb_name="${base_name}-thumb.jpg"
	if [ -n "${thumb_name}" ]; then
		echo "${large_image} to  ${thumb_name}"
	fi
	convert ${large_image} -resize 128x128 ${thumb_name}
done