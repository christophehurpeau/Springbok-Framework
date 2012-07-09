<?php

foreach(Image::findValues('id') as $imageId)
	CImages::generateThumbnails($imageId);

echo "Thumbnails generated";
