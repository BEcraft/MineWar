<?php

namespace minewar\events;

use minewar\Loader;
use pocketmine\Player;
use minewar\entities\Shop;
use minewar\window\ShopChest;
use minewar\window\Window;
use pocketmine\item\Item;
use minewar\entities\ArenaNPC;
use minewar\lucky\Lucky;
use pocketmine\nbt\tag\StringTag;
use pocketmine\block\Block;
use pocketmine\utils\TextFormat;
use pocketmine\item\enchantment\Enchantment;
use minewar\entities\MineWarEntity;
use minewar\bow\Arrow as ExplosiveArrow;
use pocketmine\level\Position;
use pocketmine\tile\Chest;
use minewar\items\LuckySlime;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityExplodeEvent;
use pocketmine\entity\Entity;
use pocketmine\event\player\PlayerLoginEvent;
use minewar\create\CreateEntity;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use minewar\entities\Hub;
use pocketmine\inventory\SimpleTransactionGroup;
use minewar\items\CustomArrow;
use pocketmine\item\Dye;
use pocketmine\math\Vector3;
use pocketmine\event\entity\ProjectileLaunchEvent;
use minewar\enchant\EnchantWindow;
use pocketmine\level\particle\DestroyBlockParticle;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\entity\Effect;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\inventory\PlayerInventory;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;


class Events implements Listener{

public function __construct(Loader $plugin){
$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
}

public function getPlugin(): Loader{
return Loader::api();
}

public function onMove(PlayerMoveEvent $event){
$player = $event->getPlayer();
foreach($player->getLevel()->getNearbyEntities($player->boundingBox->grow(4, 4, 4), $player) as $entity){
$distance = $player->distance($entity);
if(!$entity instanceof MineWarEntity){
return;
}
if($distance < 3){
$entity->lookPlayer($player);
}
}
$game = $this->getPlugin()->getUtils()->getGame($player->getName());
if(is_null($game)){
return;
}
if($game->getStatus() === "Playing" || $game->getStatus() === "Restarting"){
if($player->getFloorY() >= $game->getMaxY() || $player->getFloorY() <= $game->getMinY()){
$player->teleport($game->getPlayerSpawn($player->getName()));
}
}
}

public function shootArrow(EntityShootBowEvent $event){
$bow = $event->getBow();
$projectile = $event->getProjectile();
$player = $event->getEntity();
if($player instanceof Player){
$game = $this->getPlugin()->getUtils()->getGame($player->getName());
if(is_null($game)){
return;
}
if(isset($bow->namedtag["ExplosiveBow"]) and $bow->namedtag["ExplosiveBow"]->getValue() === true){
$projectile = new ExplosiveArrow($player->getLevel(), $projectile->namedtag, $player, false);
}
$event->setProjectile($projectile);
$bow->setDamage($bow->getDamage()+48);
}
}

public function onChat(PlayerChatEvent $event){
if($event->isCancelled()){
return;
}
$player = $event->getPlayer();
$game = $this->getPlugin()->getUtils()->getGame($player->getName());
if(is_null($game)){
return;
}
$me = $event->getMessage();
$final = "";
$len = mb_strlen($me)-1;
$colores = ["§c", "§6", "§e", "§a", "§9", "§1","§5"];
$i = 0;
$type = 0;
while($i <= $len){
$final .= $colores[$type].$me[$i];
$i++;
$type++;
if($type == count($colores)){
$type = 0;
}
}
$event->setMessage($final);
$event->setFormat(TextFormat::DARK_BLUE.TextFormat::BOLD."Game".TextFormat::WHITE."» ".TextFormat::RESET.TextFormat::GOLD.$player->getName().TextFormat::DARK_GRAY." » ".$event->getMessage());
}

public function onJoin(PlayerJoinEvent $event){
//...
}

public function onLogin(PlayerLoginEvent $event){
$event->getPlayer()->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
}

public function onQuit(PlayerQuitEvent $event){
$player = $event->getPlayer();
$game = $this->getPlugin()->getUtils()->getGame($player->getName());
if(is_null($game)){
return;
}
$game->removePlayer($player);
}

public function onKick(PlayerKickEvent $event){
$player = $event->getPlayer();
$game = $this->getPlugin()->getUtils()->getGame($player->getName());
if(is_null($game)){
return;
}
$game->removePlayer($player, true);
}

public function onBreak(BlockBreakEvent $event){
if($event->isCancelled()){
return;
}
$player = $event->getPlayer();
$block = $event->getBlock();
$game = $this->getPlugin()->getUtils()->getGame($player->getName());
if(is_null($game)){
return;
}
if($player->getLevel() !== $game->getLevel()){
return;
}
if($game->getStatus() === "Waiting" || $game->getStatus() === "Starting"){
$event->setCancelled();
$player->sendPopup(TextFormat::RED."-Blocked-")();
}
if($game->isBlockedBlock($block->getId().":".$block->getDamage())){
$event->setCancelled();
}
if($game->isBorderActive()){
$vectortext = $block->getFloorX().":".$block->getFloorY().":".$block->getFloorZ();
if($game->isBorder($vectortext)){
$event->setCancelled();
$player->sendPopup(TextFormat::RED."-Blocked-");
}
}
switch($block->getId()){
case Block::GOLD_ORE:
$rand = mt_rand(1, 100);
if($rand >= 5 and $rand <= 100){
$event->setDrops([Item::get(Item::GOLD_INGOT, 0, 1)]);
}else{
$lucky = new LuckySlime();
$event->setDrops([$lucky->setCustomName($lucky->getName())]);
$player->addTitle(TextFormat::YELLOW."Lucky ".TextFormat::GREEN."Slime", TextFormat::GRAY."Check your inventory!", 5, 5, 5);
}
break;
case Block::IRON_ORE:
$rand = mt_rand(1, 100);
if($rand >= 5 and $rand <= 100){
$event->setDrops([Item::get(Item::IRON_INGOT, 0, 1)]);
}else{
$lucky = new LuckySlime();
$event->setDrops([$lucky->setCustomName($lucky->getName())]);
$player->addTitle(TextFormat::YELLOW."Lucky ".TextFormat::GREEN."Slime", TextFormat::GRAY."Check your inventory!", 5, 5, 5);
}
break;
case Block::COAL_ORE:
$rand = mt_rand(1, 100);
if($rand >= 5 and $rand <= 100){
$event->setDrops([Item::get(Item::COAL, 0, mt_rand(1, 2))]);
}else{
$lucky = new LuckySlime();
$event->setDrops([$lucky->setCustomName($lucky->getName())]);
$player->addTitle(TextFormat::YELLOW."Lucky ".TextFormat::GREEN."Slime", TextFormat::GRAY."Check your inventory!", 5, 5, 5);
}
break;
case Block::DIAMOND_ORE:
$rand = mt_rand(1, 100);
if($rand >= 5 and $rand <= 100){
$event->setDrops([Item::get(Item::DIAMOND, 0, 1)]);
}else{
$lucky = new LuckySlime();
$event->setDrops([$lucky->setCustomName($lucky->getName())]);
$player->addTitle(TextFormat::YELLOW."Lucky ".TextFormat::GREEN."Slime", TextFormat::GRAY."Check your inventory!", 5, 5, 5);
}
break;
case Block::LAPIS_ORE:
$rand = mt_rand(1, 100);
if($rand >= 5 and $rand <= 100){
$event->setDrops([Item::get(Item::DYE, 4, mt_rand(1, 3))]);
}else{
$lucky = new LuckySlime();
$event->setDrops([$lucky->setCustomName($lucky->getName())]);
$player->addTitle(TextFormat::YELLOW."Lucky ".TextFormat::GREEN."Slime", TextFormat::GRAY."Check your inventory!", 5, 5, 5);
}
break;
case Block::COBWEB:
$item = $player->getInventory()->getItemInHand();
if($item->isSword()){
$player->getLevel()->dropItem($block, Item::get(Item::STRING, 0, mt_rand(1, 2)));
$player->getLevel()->setBlockIdAt($block->getX(), $block->getY(), $block->getZ(), 0);
}else{
$player->getLevel()->setBlockIdAt($block->getX(), $block->getY(), $block->getZ(), 0);
}
break;
}
//...
}

public function onDeath(PlayerDeathEvent $event){
//...
}

public function onPlace(BlockPlaceEvent $event){
$player = $event->getPlayer();
$block = $event->getBlock();
$game = $this->getPlugin()->getUtils()->getGame($player->getName());
if(is_null($game)){
return;
}
if($player->getLevel() !== $game->getLevel()){
return;
}
if($game->getStatus() === "Waiting" || $game->getStatus() === "Starting"){
$event->setCancelled();
$player->sendPopup(TextFormat::RED."-Blocked-");
}
if($game->isBlockedBlock($block->getId().":".$block->getDamage())){
$event->setCancelled();
}
if($game->isBorderActive()){
$vectortext = $block->getFloorX().":".$block->getFloorY().":".$block->getFloorZ();
if($game->isBorder($vectortext)){
$event->setCancelled();
$player->sendPopup(TextFormat::RED."-Blocked-");
}
}
//...
}

public function onExplode(EntityExplodeEvent $event){
$entity = $event->getEntity();
$arena = null;
if(count($this->getPlugin()->games) === 0){
return;
}
foreach($this->getPlugin()->games as $game){
if($game->getLevel() === $entity->getLevel()){
$arena = $game;
}
}
if(is_null($arena)){
return;
}
$blocks = $event->getBlockList();
foreach($blocks as $textcoor => $block){
if($arena->isBorder($textcoor) || $arena->isBlockedBlock($block->getId().":".$block->getDamage())){
unset($blocks[$textcoor]);
}
}
$event->setBlockList($blocks);
}

public function onDamage(EntityDamageEvent $event){
$entity = $event->getEntity();
if($entity instanceof MineWarEntity){
$event->setCancelled();
}
if($entity instanceof Player){
$game = $this->getPlugin()->getUtils()->getGame($entity->getName());
if(!is_null($game) and $event->getDamage() >= $entity->getHealth()){
$event->setCancelled();
$game->removePlayer($entity);
$entity->addTitle(TextFormat::RED."You died!", TextFormat::GRAY."Tap the ".TextFormat::YELLOW."wool ".TextFormat::GRAY."to return.", 100, 100, 100);
$entity->setGamemode(3);
$entity->getLevel()->addParticle(new DestroyBlockParticle($entity->add(0, 1, 0), Block::get(Block::REDSTONE_BLOCK, 0)));
$pk = new AddEntityPacket();
$pk->entityRuntimeId = Entity::$entityCount++;
$pk->type = 93;
$pk->x = $entity->x;
$pk->y = $entity->y;
$pk->z = $entity->z;
foreach($entity->getLevel()->getPlayers() as $players){
$players->dataPacket($pk);
}
$entity->getInventory()->setItem(5, Item::get(Block::WOOL, 4, 1)->setCustomName(TextFormat::RED."Return to the lobby"));
}
}
if($event instanceof EntityDamageByChildEntityEvent){
$projectile = $event->getChild();
if(($projectile instanceof Projectile) and $projectile instanceof ExplosiveArrow){
$entity->setOnFire(2);
}
}
if($event instanceof EntityDamageByEntityEvent){
$damager = $event->getDamager();
if(!$damager instanceof Player){
return;
}
if($entity instanceof Player){
$game1 = $this->getPlugin()->getUtils()->getGame($entity->getName());
if(is_null($game1)){
return;
}
$game2 = $this->getPlugin()->getUtils()->getGame($damager->getName());
if(is_null($game2)){
return;
}
if($game1 !== $game2){
return;
}
if($game1->isBorderActive()){
$event->setCancelled();
}
}
if($entity instanceof Shop){
$entity->showMenu($damager);
}
if($entity instanceof Hub){
$game = $this->getPlugin()->getUtils()->getGame($damager->getName());
if(is_null($game)){
return;
}
$game->removePlayer($damager);
$damager->sendMessage(TextFormat::GREEN."Returning to the lobby...");
$damager->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
}
if($entity instanceof ArenaNPC){
if(!isset($this->getPlugin()->games[$entity->getArena()])){
$damager->sendMessage(TextFormat::RED."This game is not available...");
return;
}
$game = $this->getPlugin()->games[$entity->getArena()];
if(!$game->isAvailable()){
$damager->sendMessage(TextFormat::RED."Game started!");
return;
}
if(!$game->getLevel()->isChunkLoaded($chunkX = $game->getLobby()->x >> 4, $chunkZ = $game->getLobby()->z >> 4)){
$game->getLevel()->loadChunk($chunkX, $chunkZ);
}
$damager->teleport($game->getLobby(), 0.0, 0.0);
$damager->sendMessage(TextFormat::YELLOW."You joined to the game!");
$game->addPlayer($damager);
}
//...
}
//...
}

public function onHold(PlayerItemHeldEvent $event){
$player = $event->getPlayer();
$block = $event->getItem();
if($block->getId() === Block::WOOL and $block->getDamage() === 4 and $block->getCustomName() === TextFormat::RED."Return to the lobby"){
$player->setGamemode(0);
$player->getInventory()->removeItem(Item::get(Block::WOOL, 4, 1));
$player->sendMessage(TextFormat::GREEN."Returning to the lobby...");
$player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
}
}

public function onInteract(PlayerInteractEvent $event){
$player = $event->getPlayer();
$game = $this->getPlugin()->getUtils()->getGame($player->getName());
if(is_null($game)){
return;
}
if($event->getBlock()->getId() === Block::CRAFTING_TABLE){
$player->sendPopup(TextFormat::RED."-BLOCKED-");
$event->setCancelled();
}
}

public function onTransaction(InventoryTransactionEvent $event){
$transactions = $event->getTransaction()->getTransactions();
$inventories = $event->getTransaction()->getInventories();
$player = null;
foreach($transactions as $transaction){
foreach($inventories as $inventory){
if(!$inventory instanceof Window){
return;
}
$type = $inventory->getHolder();
if(!$type instanceof ShopChest){
return;
}
if($type instanceof PlayerInventory){
$event->setCancelled();
return;
}
$event->setCancelled();
$item = $transaction->getTargetItem();
$item2 = $transaction->getSourceItem();
if($item->getId() == 0){
$item = $item2;
}
$player = $inventory->getPlayerSource();
if($item->getId() === Item::GOLDEN_APPLE and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 12))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 12));
$player->getInventory()->addItem(Item::get(Item::GOLDEN_APPLE, 0, 2));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::IRON_HELMET and $player->getInventory()->contains(Item::get(Item::IRON_INGOT, 0, 4))){
$player->getInventory()->removeItem(Item::get(Item::IRON_INGOT, 0, 4));
$player->getInventory()->addItem(Item::get(Item::IRON_HELMET, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::IRON_CHESTPLATE and $player->getInventory()->contains(Item::get(Item::IRON_INGOT, 0, 7))){
$player->getInventory()->removeItem(Item::get(Item::IRON_INGOT, 0, 7));
$player->getInventory()->addItem(Item::get(Item::IRON_CHESTPLATE, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::IRON_LEGGINGS and $player->getInventory()->contains(Item::get(Item::IRON_INGOT, 0, 6))){
$player->getInventory()->removeItem(Item::get(Item::IRON_INGOT, 0, 6));
$player->getInventory()->addItem(Item::get(Item::IRON_LEGGINGS, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::IRON_BOOTS and $player->getInventory()->contains(Item::get(Item::IRON_INGOT, 0, 3))){
$player->getInventory()->removeItem(Item::get(Item::IRON_INGOT, 0, 3));
$player->getInventory()->addItem(Item::get(Item::IRON_BOOTS, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::IRON_SWORD and $player->getInventory()->contains(Item::get(Item::IRON_INGOT, 0, 3))){
$player->getInventory()->removeItem(Item::get(Item::IRON_INGOT, 0, 3));
$player->getInventory()->addItem(Item::get(Item::IRON_SWORD, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::BOW and !$item->hasEnchantments() and $player->getInventory()->contains(Item::get(Item::IRON_INGOT, 0, 4)) and $player->getInventory()->contains(Item::get(Item::STRING, 0, 4))){
$player->getInventory()->removeItem(Item::get(Item::IRON_INGOT, 0, 4));
$player->getInventory()->removeItem(Item::get(Item::STRING, 0, 4));
$player->getInventory()->addItem(Item::get(Item::BOW, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::ARROW and $player->getInventory()->contains(Item::get(Item::STRING, 0, 4))){
$player->getInventory()->removeItem(Item::get(Item::STRING, 0, 4));
$player->getInventory()->addItem(Item::get(Item::ARROW, 0, 32));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::DIAMOND_HELMET and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 6))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 6));
$player->getInventory()->addItem(Item::get(Item::DIAMOND_HELMET, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::DIAMOND_CHESTPLATE and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 10))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 10));
$player->getInventory()->addItem(Item::get(Item::DIAMOND_CHESTPLATE, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::DIAMOND_LEGGINGS and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 8))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 8));
$player->getInventory()->addItem(Item::get(Item::DIAMOND_LEGGINGS, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::DIAMOND_BOOTS and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 6))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 6));
$player->getInventory()->addItem(Item::get(Item::DIAMOND_BOOTS, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::DIAMOND_SWORD and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 4))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 4));
$player->getInventory()->addItem(Item::get(Item::DIAMOND_SWORD, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::BOW and $item->hasEnchantments() and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 15)) and $player->getInventory()->contains(Item::get(Item::STRING, 0, 12))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 15));
$player->getInventory()->removeItem(Item::get(Item::STRING, 0, 12));
$explosive = Item::get(Item::BOW, 0, 1);
$explosive->setCustomName(TextFormat::GREEN."Explosive Bow");
$explosive->addEnchantment(Enchantment::getEnchantment(Enchantment::TYPE_BOW_FLAME));
if(!isset($explosive->getNamedTag()->ExplosiveBow)){
$explosive->namedtag["ExplosiveBow"] = new StringTag("ExplosiveBow", true);
}
$player->getInventory()->addItem($explosive);
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Block::TNT and $player->getInventory()->contains(Item::get(Item::REDSTONE, 0, 8))){
$player->getInventory()->removeItem(Item::get(Item::REDSTONE, 0, 8));
$player->getInventory()->addItem(Item::get(Block::TNT, 0, 2));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Block::ENCHANTING_TABLE and $player->getInventory()->contains(Item::get(Item::IRON_INGOT, 0, 10)) and $player->getInventory()->contains(Item::get(Item::REDSTONE, 0, 4))){
$player->getInventory()->removeItem(Item::get(Item::IRON_INGOT, 0, 10));
$player->getInventory()->removeItem(Item::get(Item::REDSTONE, 0, 4));
$player->getInventory()->addItem(Item::get(Block::ENCHANTING_TABLE, 0, 1));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::BOTTLE_O_ENCHANTING and $player->getInventory()->contains(Item::get(Item::REDSTONE, 0, 6))){
$player->getInventory()->removeItem(Item::get(Item::REDSTONE, 0, 6));
$player->getInventory()->addItem(Item::get(Item::BOTTLE_O_ENCHANTING, 0, 10));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::SPLASH_POTION and $item->getDamage() === 30 and $player->getInventory()->contains(Item::get(Item::REDSTONE, 0, 3)) and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 3))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 3));
$player->getInventory()->removeItem(Item::get(Item::REDSTONE, 0, 3));
$player->addEffect(Effect::getEffect(10)->setAmplifier(0)->setDuration(15*20));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
if($item->getId() === Item::SPLASH_POTION and $item->getDamage() === 16 and $player->getInventory()->contains(Item::get(Item::REDSTONE, 0, 10)) and $player->getInventory()->contains(Item::get(Item::GOLD_INGOT, 0, 10))){
$player->getInventory()->removeItem(Item::get(Item::GOLD_INGOT, 0, 10));
$player->getInventory()->removeItem(Item::get(Item::REDSTONE, 0, 10));
$player->addEffect(Effect::getEffect(1)->setAmplifier(0)->setDuration(65*20));
$inventory->update();
}else{
$player->getInventory()->sendContents($player);
}
//
}
}
}

}