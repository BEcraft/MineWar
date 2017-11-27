<?php

namespace minewar\window;

use minewar\window\ShopChest;
use pocketmine\Player;
use pocketmine\item\Item;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat;
use minewar\items\CustomSword;
use minewar\items\CustomApple;
use minewar\items\CustomArrow;
use minewar\items\CustomBucket;
use minewar\items\CustomChestplate;
use minewar\items\CustomLeggings;
use pocketmine\inventory\ChestInventory;

class Window extends ChestInventory{

public function __construct(ShopChest $chest){
parent::__construct($chest);
}

public function onClose(Player $who){
parent::onClose($who);
$this->holder->sendReplacement($who);
$this->holder->close();
}

public function onOpen(Player $who){
parent::onOpen($who);
$this->update();
}

private function getCount(Item $item){
$target = null;
$inventory = $this->getPlayerSource()->getInventory();
for($i = 0; $i < $inventory->getSize(); ++$i){
$item2 = $inventory->getItem($i);
if($item2->getId() === $item->getId()){
$target = $item2;
break;
}
}
if(is_null($target)){
$count = TextFormat::GRAY."(".TextFormat::RED."0".TextFormat::GRAY.")";
return $count;
}
if($target->getCount() >= $item->getCount()){
$count = TextFormat::GRAY."(".TextFormat::GREEN.$target->getCount().TextFormat::GRAY.")";
return $count;
}
if($target->getCount() < $item->getCount()){
$count = TextFormat::GRAY."(".TextFormat::RED.$target->getCount().TextFormat::GRAY.")";
return $count;
}
}

public function update(){
$apple = Item::get(Item::GOLDEN_APPLE, 0, 2);
$apple->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$apple->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to regain your life.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."12 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 12)));
$ironhelmet = Item::get(Item::IRON_HELMET, 0, 1);
$ironhelmet->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$ironhelmet->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to protect you.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."4 ".TextFormat::GRAY."Iron ".$this->getCount(Item::get(Item::IRON_INGOT, 0, 4)));
$ironchest = Item::get(Item::IRON_CHESTPLATE, 0, 1);
$ironchest->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$ironchest->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to protect you.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."7 ".TextFormat::GRAY."Iron ".$this->getCount(Item::get(Item::IRON_INGOT, 0, 7)));
$ironleggings = Item::get(Item::IRON_LEGGINGS, 0, 1);
$ironleggings->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$ironleggings->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to protect you.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."6 ".TextFormat::GRAY."Iron ".$this->getCount(Item::get(Item::IRON_INGOT, 0, 6)));
$ironboots = Item::get(Item::IRON_BOOTS, 0, 1);
$ironboots->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$ironboots->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to protect you.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."3 ".TextFormat::GRAY."Iron ".$this->getCount(Item::get(Item::IRON_INGOT, 0, 3)));
$ironsword = Item::get(Item::IRON_SWORD, 0, 1);
$ironsword->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$ironsword->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to kill your enemy.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."3 ".TextFormat::GRAY."Iron ".$this->getCount(Item::get(Item::IRON_INGOT, 0, 3)));
$bow = Item::get(Item::BOW, 0, 1);
$bow->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$bow->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to shoot your enemy.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."6 ".TextFormat::GRAY."String ".$this->getCount(Item::get(Item::STRING, 0, 6)).", ".TextFormat::GREEN."4 ".TextFormat::GRAY."Iron ".$this->getCount(Item::get(Item::IRON_INGOT, 0, 4)));
$arrow = Item::get(Item::ARROW, 0, 32);
$arrow->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$arrow->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it with your bow.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."4 ".TextFormat::GRAY."String ".$this->getCount(Item::get(Item::STRING, 0, 4)));
$diamondhelmet = Item::get(Item::DIAMOND_HELMET, 0, 1);
$diamondhelmet->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$diamondhelmet->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to protect you.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."6 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 6)));
$diamondchest = Item::get(Item::DIAMOND_CHESTPLATE, 0, 1);
$diamondchest->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$diamondchest->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to protect you.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."10 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 10)));
$diamondleggings = Item::get(Item::DIAMOND_LEGGINGS, 0, 1);
$diamondleggings->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$diamondleggings->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to protect you.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."8 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 8)));
$diamondboots = Item::get(Item::DIAMOND_BOOTS, 0, 1);
$diamondboots->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$diamondboots->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to protect you.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."6 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 6)));
$diamondsword = Item::get(Item::DIAMOND_SWORD, 0, 1);
$diamondsword->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$diamondsword->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to kill your enemy.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."4 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 4)));
$explosivebow = Item::get(Item::BOW, 0, 1);
$explosivebow->addEnchantment(Enchantment::getEnchantment(Enchantment::TYPE_BOW_FLAME));
$explosivebow->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD."Explosive Bow".PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to kill your enemy.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."15 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 15)).", ".TextFormat::GREEN."12 ".TextFormat::GRAY."String ".$this->getCount(Item::get(Item::STRING, 0, 12)));
$tnt = Item::get(Block::TNT, 0, 2);
$tnt->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$tnt->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to explode the map :D.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."8 ".TextFormat::GRAY."Redstone ".$this->getCount(Item::get(Item::REDSTONE, 0, 8)));
$enchanting = Item::get(Block::ENCHANTING_TABLE, 0, 1);
$enchanting->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$enchanting->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to enchant your items.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."10 ".TextFormat::GRAY."Iron ".$this->getCount(Item::get(Item::IRON_INGOT, 0, 10)).", ".TextFormat::GREEN."4 ".TextFormat::GRAY."Redstone ".$this->getCount(Item::get(Item::REDSTONE, 0, 4)));
$bottle = Item::get(Item::BOTTLE_O_ENCHANTING, 0, 1);
$bottle->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD."Bottle O' Enchanting".PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to enchant your items.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."6 ".TextFormat::GRAY."Redstone ".$this->getCount(Item::get(Item::REDSTONE, 0, 6)));
$regeneration = Item::get(Item::SPLASH_POTION, 30, 1);
$regeneration->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$regeneration->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to regain your health.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."3 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 3)).", ".TextFormat::GREEN."3 ".TextFormat::GRAY."Redstone ".$this->getCount(Item::get(Item::REDSTONE, 0, 3)));
$swiftness = Item::get(Item::SPLASH_POTION, 16, 1);
$swiftness->setCustomName(TextFormat::RESET.TextFormat::YELLOW.TextFormat::BOLD."Item: ".TextFormat::RESET.TextFormat::GOLD.$swiftness->getName().PHP_EOL.TextFormat::RED.TextFormat::BOLD."Description: ".TextFormat::RESET.TextFormat::GRAY."Use it to run faster.".PHP_EOL.TextFormat::YELLOW.TextFormat::BOLD."Price: ".TextFormat::RESET.TextFormat::GREEN."10 ".TextFormat::GRAY."Gold ".$this->getCount(Item::get(Item::GOLD_INGOT, 0, 10)).", ".TextFormat::GREEN."10 ".TextFormat::GRAY."Redstone ".$this->getCount(Item::get(Item::REDSTONE, 0, 10)));

$items = [$ironhelmet, $ironchest, $ironleggings, $ironboots, $ironsword, $bow, $arrow, $diamondhelmet, $diamondchest, $diamondleggings, $diamondboots, $diamondsword, $explosivebow, $tnt, $enchanting, $bottle, $regeneration, $swiftness, $apple];
for($i = 0; $i < count($items); ++$i){
$this->setItem($i, $items[$i]);
}
}

public function getPlayerSource(){
$player = array_values($this->getViewers());
return $player[0];
}

}