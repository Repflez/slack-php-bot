<?php
	/**
	 * Repflez's shitty Slack bot
	 *
	 * @package shitty Slack bot
	 * @author Repflez
	 * @copyright 2015 Repflez
	 * @license BSD
	 *
	 * @version 0.1.0
	 */

	////////////////////////////
	//  CONFIG SECTION START  //
	////////////////////////////

	// Slack token
	$token = 'TOKEN_KEY';

	// Trigger word(s). Must be the same as the one in Slack's panel
	$triggerWord = '!';

	// Custom command handler
	// If your command must use any other method name than the default for custom commands, it
	// should be on this list
	$customCommands = [
		//'hi' => 'bot_command_welcome',
	];

	/////////////////////////
	// CONFIG SECTION END  //
	/////////////////////////

	/////////////////////////////////
	//  CUSTOM BOT COMMANDS START  //
	////////////////////////////////////////////////////////////////////////////////////////
	// Custom commands for the bot are based on the naming of bot_command_<name>.         //
	// It must accept only one parameter and it must return the text to post to the       //
	// channel. If you must set another name for commands, set them in the config above.  //
	////////////////////////////////////////////////////////////////////////////////////////

	function bot_command_welcome($commandData)
	{
		return 'Welcome <@' . $commandData['user_id'] . '|' . $commandData['user_name'] . '>!';
	}

	///////////////////////////////
	//  CUSTOM BOT COMMANDS END  //
	///////////////////////////////

	///////////////////////
	//  BOT STARTS HERE  //
	////////////////////////////////////////////////////////////////////////////////////////
	//  Modifying anything past this point can have adverse effects to the bot including  //
	//  crashes, malfunctioning, posting MLP porn, halitosis, bot marriage, death, rain,  //
	//  more death and possibly a restricted osu! account.  ////////////////////////////////
	////////////////////////////////////////////////////////////////////////////////////////

	// Initial check. Is this a POST?
	// This "bot" only works with POST requests
	if (empty($_POST)) exit;

	$tempElements   = [];
	$postContents   = [];
	$slackMsg       = [];
	$slackFields    = [];
	$commandData    = [];

	// POST fields that Slack sends on every request
	$slackFields = [
		'token','team_id','team_domain','channel_id','channel_name',
		'timestamp','user_id','user_name','text','trigger_word'
	];

	// Extracting the fields we need from the clutter that is $_POST
	foreach ($slackFields as $field) {
		$postContents[$field] = $_POST[$field];
	}

	unset($field, $_POST);

	// Check if the token sent by Slack is the same as the one we have in the config
	// If not, send a message and stop
	if ($postContents['token'] !== $token) {
		$slackMsg['text'] = "AUTH ERROR: Token is wrong. Check bot config and Slack config.";
		echo json_encode($slackMsg);
		exit;
	}

	// Prepare the text sent by the user to be used as a command
	$tempElements['prepared'] = substr($postContents['text'], strlen($triggerWord));

	// Separate the text as an array for easy manipulation later
	// The use of str_getcsv allows to use "names like this" and be itself a single element
	// in the array instead of more than needed.
	$tempElements['early_command_array'] = str_getcsv($tempElements['prepared'], ' ');

	// Set the command name to know what command to use
	$tempElements['command_to_use'] = $tempElements['early_command_array'][0];

	// Set the array to be sent to the command minus the command name
	$tempElements['command_params'] = $tempElements['early_command_array'];
	array_shift($tempElements['command_params']);

	// Set the "raw" params of the command in case the command needs it
	$tempElements['raw_params'] = substr($tempElements['prepared'], strlen($tempElements['command_to_use']) + 1);

	// Generate the command data array that command methods will use
	$commandData = [
		'channel_id' => $postContents['channel_id'],
		'channel_name' => $postContents['channel_name'],
		'user_id' => $postContents['user_id'],
		'user_name' => $postContents['user_name'],
		'params' => $tempElements['raw_params'],
		'params_array' => $tempElements['command_params'],
	];

	// Check if the command exists on the script
	if (is_callable('bot_command_' . $tempElements['command_to_use'], false, $tempElements['command_method'])) {
		$tempElements['command_reply'] = call_user_func($tempElements['command_method'], $commandData);

	// Check if the command is on the custom list and use it if it's there
	} elseif (isset($customCommands[$tempElements['command_to_use']])) {
		$tempElements['command_reply'] = call_user_func($customCommands[$tempElements['command_to_use']], $commandData);
	} else {
		exit;
	}

	// Prepare for sending the reply to Slack
	$slackMsg['text'] = $tempElements['command_reply'];

	// Send the command to Slack!
	header('Content-Type: text/html; charset=utf-8');
	echo json_encode($slackMsg, JSON_UNESCAPED_UNICODE);
