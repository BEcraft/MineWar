<?php

namespace minewar\lucky;

use minewar\Loader;
use minewar\lucky\Lucky;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\PluginTask;

class Timer extends PluginTask{

private $time = 5;

private $lucky = null;

public function __construct(Lucky $lucky){
parent::__construct($this->getPlugin(), $lucky);
$this->lucky = $lucky;
}

public function getPlugin(): Loader{
return Loader::api();
}

public function onRun($tick){
$this->time--;
$tag = TextFormat::GREEN.str_repeat("█", $this->time).TextFormat::RED.str_repeat("█", 5-$this->time);
$this->lucky->setNameTag($this->lucky->getName()."\n  ".$tag);
if($this->time === 0){
$this->lucky->getLevel()->addParticle(new DestroyBlockParticle($this->lucky, Block::get(41, 1)));
$items = [Item::get(Item::EMERALD, 0, 1), Item::get(Item::GOLD_INGOT, 0, 2), Item::get(Item::GOLDEN_APPLE, 0, 1), Item::get(Item::STRING, 0, 2), Item::get(Item::FLINT_AND_STEEL, 0, 1), Item::get(Item::BUCKET, 8, 1), Item::get(Item::FISHING_ROD, 0, 1)];
$this->lucky->getLevel()->dropItem($this->lucky, $items[array_rand($items)]);
$this->lucky->close();
$this->getPlugin()->getServer()->getScheduler()->cancelTask($this->getTaskId());
}
}

}