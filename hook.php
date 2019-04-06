<?php
/**
 * Changing the chat photo shows a hint in the group and pushes the chat to the top (new unread message).
 * The Telegram-Bot Webhook should remove the last message created by changing the chat photo.
 */

// config
$botToken = ''; // telegram bot token, ask the @BotFather
$chatId = ''; // telegram group chat id, should be a negative number

// ---
$hook = $_POST ?: @json_decode(file_get_contents('php://input'), true);

// write anything in the group and the bot will reply with the $chatId
// (bot needs write permission)
if (!$chatId && isset($hook['message'])) {
	$ch = curl_init('https://api.telegram.org/bot'.$botToken.'/sendMessage');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
			'chat_id' => $hook['message']['chat']['id'],
			'text' => 'The $chatId is `'.$hook['message']['chat']['id'].'`',
			'parse_mode' => 'Markdown'
		]));
	curl_exec($ch);
	curl_close($ch);
	exit;
}

// if this call is triggered by a chat photo change, remove the hint from the group
if (
	isset($hook['message'])
	&& $hook['message']['from']['is_bot']
	&& $hook['message']['chat']['id'] == $chatId
	&& $hook['message']['new_chat_photo']
) {
	$ch = curl_init('https://api.telegram.org/bot'.$botToken.'/deleteMessage');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
			'chat_id' => $hook['message']['chat']['id'],
			'message_id' => $hook['message']['message_id']
		]));
	curl_exec($ch);
	curl_close($ch);
	exit;
}
