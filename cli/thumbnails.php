<?php

foreach(Image::findValues('id') as $imageId)
	CImages::generateThumbnails(DATA.'images/'.$imageId);

echo "Thumbnails generated";
