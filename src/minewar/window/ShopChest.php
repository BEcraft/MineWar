<?php

namespace minewar\window;

use minewar\Loader;
use minewar\window\Window;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\tile\Chest;
use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\nbt\tag\StringTag;

class ShopChest extends Chest{

public function __construct(Level $level, CompoundTag $tag){
parent::__construct($level, $tag);
$this->block = [$this->getBlock()->getId(), $this->getBlock()->getDamage()];
}

private function getReplacement(): Block{
return Block::get(...$this->block);
}

public function sendReplacement(Player $who){
$block = $this->getReplacement();
$block->x = $this->x;
$block->y = $this->y;
$block->z = $this->z;
$block->level = $this->getLevel();
if($block->level !== null){
$block->level->sendBlocks([$who], [$block]);
}
}

public function addAdditionalSpawnData(CompoundTag $nbt){
}

public function getInventory(): Window{
return (new Window($this));
}

public function getPosition(): Position{
return (new Position(floor($this->x), floor($this->y), floor($this->z), $this->level));
}



}