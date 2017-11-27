<?php

namespace minewar\items;

use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\block\Block;
use minewar\entities\Lucky;
use pocketmine\event\player\PlayerInteractEvent;
use minewar\create\CreateEntity;

class LuckySlime extends Item{

const LUCKY_ID = 409;

public function __construct($count = 1){
parent::__construct(LuckySlime::LUCKY_ID, 1, $count, TextFormat::RESET.TextFormat::YELLOW."Lucky ".TextFormat::GREEN."Slime".TextFormat::RESET);
}

public function canBeActivated(){
return true;
}

public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
CreateEntity::createLucky($block->getX(), $block->getY(), $block->getZ(), $player);
if($player->isSurvival()){
$this->setCount($this->getCount()-1);
$player->getInventory()->setItemInHand($this);
}
return true;
}


}