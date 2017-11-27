<?php

namespace minewar\utilities;

use minewar\Loader;
use pocketmine\Player;
use minewar\window\Window;
use minewar\entities\Shop;
use minewar\lucky\Lucky;
use minewar\lucky\Timer;
use pocketmine\tile\Tile;
use pocketmine\utils\Config;
use pocketmine\math\Vector3;
use minewar\window\ShopChest;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ByteTag;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Utils{

public function __construct(Loader $plugin){
}

public function getPlugin(): Loader{
return Loader::api();
}

public function gameExists($game): bool{
return array_key_exists($game, $this->getPlugin()->games);
}

public function checkGame($game): Array{
if($this->gameExists($game)){
return;
}
$errors = [];
$arena = new Config($this->getPlugin()->getDataFolder()."games/".$game.".yml");
if(empty($arena->getAll())){
$errors[] = "Missing arena configuration.";
return $errors;
}
$name = $arena->get("name");
if(empty($name)){
$errors[] = "Missing name";
}
$world = $arena->get("world");
if(!$this->getPlugin()->getServer()->isLevelLoaded($world)){
$this->getPlugin()->getServer()->loadLevel($world);
}
$level = $this->getPlugin()->getServer()->getLevelByName($world);
if(empty($world) || is_null($level)){
$errors[] = "Missing world";
}
$shop = $arena->get("shops");
if(empty($shop) or count($shop) !== 2){
$errors[] = "Missing shop positions";
}
$positions = $arena->get("positions");
if(empty($positions) or count($positions) !== 2){
$errors[] = "Missing players position";
}
$border = $arena->get("border");
if(empty($border) or count($border) !== 2){
$errors[] = "Missing border positions";
}
$lobby = $arena->get("lobby");
if(empty($lobby)){
$errors[] = "Missing lobby position";
}
$max = $arena->get("maxheight");
if(empty($max) || $max === 0){
$errors[] = "Missing max height";
}
$min = $arena->get("minheight");
if(empty($min) || $min === 0){
$errors[] = "Missing min height";
}
$lobby = $arena->get("lobbynpc");
if(empty($lobby)){
$errors[] = "Missing lobby-npc position";
}
$waitlobby = $arena->get("waitpos");
if(empty($waitlobby)){
$errors[] = "Missing waiting lobby";
}
if(!is_file($this->getPlugin()->getDataFolder()."/maps/".$world.".zip")){
$errors[] = "Mising world file";
}
return $errors;
}

public function copyMap($name){
$zip = new ZipArchive;
$path = $this->getPlugin()->getServer()->getDataPath();
$zip->open($this->getPlugin()->getDataFolder()."/maps/".$name.".zip", ZipArchive::CREATE);
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path."worlds/".$name));
foreach($files as $file){
if(is_file($file)){
$zip->addFile($file, str_replace("\\", "/", ltrim(substr($file, strlen($path."worlds/".$name)), "/\\")));
}
}
$zip->close();
}

public function getGame($player){
if(count($this->getPlugin()->games) === 0){
return null;
}
$arena = null;
foreach($this->getPlugin()->games as $game){
if($game->isPlaying($player)){
$arena = $game;
break;
}
}
return $arena;
}

}