<?php

namespace minewar\commands;

use minewar\Loader;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;

class Create extends PluginCommand{

public function __construct(Loader $plugin){
parent::__construct("minewar", $plugin);
$this->setDescription("Minewar Command");
$this->setAliases(["miwa", "mwr"]);
}

public function getPlugin(): Loader{
return Loader::api();
}

public function execute(CommandSender $sender, $label, array $args){
if(!$sender->isOp()){
$sender->sendMessage(TextFormat::RED."You are not allowed to use this command.");
return false;
}
if(!$sender instanceof Player){
$sender->sendMessage(TextFormat::RED."Run this command in game.");
return false;
}
if($sender->getLevel() === $this->getPlugin()->getServer()->getDefaultLevel()){
$sender->sendMessage(TextFormat::GOLD."You are in the lobby, go to another world.");
return false;
}
if(empty($args[0])){
$sender->sendMessage(TextFormat::RED."/minwar <arena>");
return false;
}
$arena = $args[0];
if(is_numeric($arena)){
$sender->sendMessage(TextFormat::RED."Choose a better name.");
return false;
}
if(is_file($this->getPlugin()->getDataFolder()."/maps/". $sender->getLevel()->getFolderName().". zip")){
$sender->sendMessage(TextFormat::RED."There is a game using this map.");
return false;
}
$exists = null;
foreach(scandir($this->getPlugin()->getDataFolder()."/games/") as $file){
if($file !== ".." and $file !== "."){
if(strtolower($file) === strtolower($arena)){
$exists = true;
break;
}
}
}
if(!is_null($exists)){
$sender->sendMessage(TextFormat::RED."There is a game with this name.");
return false;
}
$this->getPlugin()->getUtils()->copyMap($sender->getLevel()->getFolderName());
//default config game
$config = new Config($this->getPlugin()->getDataFolder()."/games/".$arena.".yml", Config::YAML);
$config->set("name", $arena);
$config->set("world", $sender->getLevel()->getFolderName());
$config->set("shops", ["274:59:257", "238:59:257"]);
$config->set("positions", ["250:62:255", "262:62:255"]);
$config->set("border", ["256:48:272", "256:77:243"]);
$config->set("blocks", ["159:5", "47:0"]);
$config->set("lobby", "284:4:279");
$config->set("maxheight", 76);
$config->set("minheight", 45);
$config->set("lobbynpc", "192:76:261");
$config->set("waitpos", "190:77:259");
$config->save();
$sender->sendMessage("You created a game, edit the file and restart your server.");
return true;
}


}