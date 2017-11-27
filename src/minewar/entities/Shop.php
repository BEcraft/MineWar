<?php

namespace minewar\entities;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\tile\Tile;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;

class Shop extends MineWarEntity{

const NETWORK_ID = 15;

public function showMenu(Player $player){
$nbt = new CompoundTag("", [new StringTag("Id", Tile::CHEST), new StringTag("CustomName", TextFormat::BOLD.TextFormat::YELLOW."MineWar: ".TextFormat::GOLD."Shop"), new IntTag("x", $player->getFloorX()), new IntTag("y", $player->getFloorY()-3), new IntTag("z", $player->getFloorZ())]);
$chest = Tile::createTile("ShopChest", $player->getLevel(), $nbt);
$block = Block::get(54);
$block->x = $chest->x;
$block->y = $chest->y;
$block->z = $chest->z;
$block->level = $player->getLevel();
$player->getLevel()->sendBlocks([$player], [$block]);
$chest->spawnTo($player);
$player->addWindow($chest->getInventory());
}

public function getName(){
return TextFormat::GOLD.TextFormat::BOLD."   Shop".TextFormat::RESET.PHP_EOL.TextFormat::GREEN."Tap to buy!".PHP_EOL;
}

}