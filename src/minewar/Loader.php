<?php

namespace minewar;

use minewar\stats\Connection;
use minewar\window\ShopChest;
use minewar\create\CreateEntity;
use minewar\utilities\Utils;
use minewar\events\Events;
use minewsr\entities\MineWarEntity;
use minewar\entities\ArenaNPC;
use minewar\entities\Hub;
use minewar\game\Arena;
use pocketmine\item\Item;
use minewar\items\LuckySlime;
use pocketmine\nbt\tag\StringTag;
use minewar\entities\Shop;
use pocketmine\utils\Config;
use pocketmine\entity\Human;
use minewar\task\Timer;
use minewar\lucky\Lucky;
use minewar\commands\Create;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase{

private static $api = null;

public $language = null;

public $games = [];

public $entities = [];

public function onEnable(){
self::$api = $this;
if(!is_dir($folder = $this->getDataFolder()) or !is_dir($folder."/games/") or !is_dir($folder."/maps/")){
@mkdir($folder);
@mkdir($folder."/games/");
@mkdir($folder."/maps/");
}
Item::$list[LuckySlime::LUCKY_ID] = LuckySlime::class;
Item::init();
$lucky = new LuckySlime();
Item::addCreativeItem($lucky->setCustomName($lucky->getName()));
Tile::registerTile(ShopChest::class);
Entity::registerEntity(Shop::class, true);
Entity::registerEntity(Lucky::class, true);
Entity::registerEntity(ArenaNPC::class, true);
Entity::registerEntity(Hub::class, true);
new Events($this);
$level = $this->getServer()->getDefaultLevel();
$this->getServer()->getCommandMap()->register("/minewar", new Create($this));
foreach(scandir($this->getDataFolder()."/games/") as $files){
if($files !== ".." and $files !== "."){
$arena = str_replace(".yml", "", $files);
$errors = $this->getUtils()->checkGame($arena);
if(count($errors) > 0){
$this->getLogger()->notice("Game ".$arena." couldnt be loaded, errors: ".implode(", ", $errors));
continue;
}
$this->games[$arena] = new Arena($config = new Config($this->getDataFolder()."/games/".$arena.".yml", Config::YAML));
$this->games[$arena]->restoreMap(true);
$position = explode(":", $config->get("lobby"));
$entity = CreateEntity::createArenaNPC($level, floatval($position[0]), floatval($position[1]), floatval($position[2]), $arena);
$this->getLogger()->info(TextFormat::GREEN."Game ".$arena." loaded correctly!");
$this->entities[$arena] = $entity;
}
}
$this->getServer()->getScheduler()->scheduleRepeatingTask(new Timer($this), 20);
$this->getLogger()->info("Minigame loaded! ^-^");
}

public function getUtils(): Utils{
return (new Utils($this));
}

public static function api(): Loader{
return self::$api;
}

public function onDisable(){
foreach($this->games as $arena => $data){
$data->restoreMap(true);
}
$level = $this->getServer()->getDefaultLevel();
foreach(array_values($this->entities) as $entidad){
$entidad->close();
}
$this->getLogger()->info(TextFormat::RED."Plugin disabled!");
}

}