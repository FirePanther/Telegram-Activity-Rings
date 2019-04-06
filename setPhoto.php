<?php
/**
 * Generates the activity rings graphic and sets the chat photo.
 */

// config
$botToken = ''; // telegram bot token, ask the @BotFather
$chatId = ''; // telegram group chat id, should be a negative number

// percentages, e.g. calculated by database values
$percentages = [
	'outer' => rand(0, 100),
	'middle' => rand(0, 100),
	'inner' => rand(0, 100)
];
$theme = 'gradient'; // alternative: flat

// ---
array_walk($percentages, function($value) {
	return max(min($value, 100), 0) | 0; // limits 0-100, floored
});

// you can cache it if you want, just change the telegram profile photo, if any value changes
$checksum = md5(json_encode($percentages));

$destination = '/tmp/tg-photo-'.$checksum.'.png';
$size = 900;
$images = [
	'canvas' => imagecreatetruecolor($size, $size)
];

$color = [
	'white' => imagecolorallocate($images['canvas'], 255, 255, 255),
	'black' => imagecolorallocate($images['canvas'], 0, 0, 0)
];

imagefill($images['canvas'], 0, 0, $color['black']); // background color

// add layers
foreach (['outer', 'middle', 'inner'] as $ring) {
	$images[$ring] = imagecreatefrompng(__DIR__.'/rings/'.$theme.'/'.$ring.$percentages[$ring].'.png');
	imagecopy($images['canvas'], $images[$ring], 0, 0, 0, 0, $size, $size);
}

// save photo temporary
imagepng($images['canvas'], $destination);

// command the bot to change the chat photo
$ch = curl_init('https://api.telegram.org/bot'.$botToken.'/setChatPhoto');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, [
	'chat_id' => $chatId,
	'photo' => curl_file_create($destination)
]);
$result = curl_exec($ch);
curl_close($ch);

if (php_sapi_name() !== 'cli') {
	// show photo (just for testing) and deinit images
	header('Content-Type: image/png');
	imagepng($images['canvas']);
}

unlink($destination);
array_walk($images, function($image) {
	imagedestroy($image);
});
