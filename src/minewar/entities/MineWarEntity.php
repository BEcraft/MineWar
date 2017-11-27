<?php

namespace minewar\entities;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\math\Vector2;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\utils\TextFormat;

abstract class MineWarEntity extends Entity{

public function __construct(Level $level, CompoundTag $nbt){
parent::__construct($level, $nbt);
}

public function spawnTo(Player $player){
$pk = new AddEntityPacket();
$pk->entityRuntimeId = $this->getId();
$pk->type = static::NETWORK_ID;
$pk->speedX = 0;
$pk->speedY = 0;
$pk->speedZ = 0;
$pk->x = $this->x;
$pk->y = $this->y;
$pk->z = $this->z;
$pk->yaw = $player->yaw;
$pk->pitch = $player->pitch;
$pk->attributes = [];
$pk->metadata = $this->dataProperties;
$pk->links = [];
$player->dataPacket($pk);
$this->hasSpawned[$player->getLoaderId()] = $player;
}

public function lookPlayer(Player $player){
$x = $player->x - $this->x;
$y = $player->y - $this->y;
$z = $player->z - $this->z;
$angle = atan2($z, $x);
$yaw = (($angle*180)/M_PI)-90;
$vector = new Vector2($x, $z);
$distance = $vector->distance($player->x, $player->z);
$angle = atan2($distance, $y);
$pitch = (($angle*180)/M_PI)-90;
$pk = new MoveEntityPacket();
$pk->eid = $this->id;
$pk->x = $this->x;
$pk->y = $this->y;
$pk->z = $this->z;
$pk->yaw = $yaw;
$pk->pitch = $pitch;
$pk->headYaw = $pk->yaw;
$player->dataPacket($pk);
}

public function iniEntity(){
parent::initEntity();
}

public function saveNBT(){
parent::saveNBT();
}

public function getDrops(){
return [];
}


}