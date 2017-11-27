<?php

namespace minewar\create;

use minewar\Loader;
use pocketmine\Player;
use minewar\window\Window;
use minewar\entities\ArenaNPC;
use minewar\entities\Shop;
use minewar\lucky\Lucky;
use minewar\lucky\Timer;
use minewar\horse\Horse;
use pocketmine\tile\Tile;
use pocketmine\entity\Human;
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

class CreateEntity{

public function getPluginManager(){
return Loader::api();
}

public static function createEntity($level, $x, $y, $z, $type){
$nbt = new CompoundTag;
$nbt->Pos = new ListTag("Pos", [new DoubleTag("", $x), new DoubleTag("", $y), new DoubleTag("", $z)]);
$nbt->Motion = new ListTag("Motion", [new DoubleTag("", 0), new DoubleTag("", 0), new DoubleTag("", 0)]);
$nbt->Rotation = new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]);
$entity = Entity::createEntity($type, $level, $nbt);
$entity->setNameTag($entity->getName());
$entity->setNametagVisible(true);
$entity->setNameTagAlwaysVisible(true);
$entity->setImmobile(true);
$entity->spawnToAll();
return $entity;
}

public static function createLucky($x, $y, $z, $player){
$nbt = new CompoundTag;
$nbt->Pos = new ListTag("Pos", [new DoubleTag("", $x), new DoubleTag("", $y), new DoubleTag("", $z)]);
$nbt->Motion = new ListTag("Motion", [new DoubleTag("", 0), new DoubleTag("", 0), new DoubleTag("", 0)]);
$nbt->Rotation = new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]);
$entity = Entity::createEntity("Lucky", $player->getLevel(), $nbt);
$entity->setNameTag($entity->getName());
$entity->setNametagVisible(true);
$entity->setNameTagAlwaysVisible(true);
$entity->setImmobile(true);
$entity->spawnToAll();
Loader::api()->getServer()->getScheduler()->scheduleRepeatingTask(new Timer($entity), 15);
}

public static function createArenaNPC($level, $x, $y, $z, $arena){
$nbt = new CompoundTag;
$nbt->Pos = new ListTag("Pos", [new DoubleTag("", $x), new DoubleTag("", $y), new DoubleTag("", $z)]);
$nbt->Motion = new ListTag("Motion", [new DoubleTag("", 0), new DoubleTag("", 0), new DoubleTag("", 0)]);
$nbt->Rotation = new ListTag("Rotation", [new FloatTag("", 0), new FloatTag("", 0)]);
$nbt->Arena = new StringTag("Arena", $arena);
$entity = Entity::createEntity("ArenaNPC", $level, $nbt);
$entity->setNameTag($entity->getName());
$entity->setNametagVisible(true);
$entity->setNameTagAlwaysVisible(true);
$entity->setImmobile(true);
$entity->spawnToAll();
return $entity;
}

}