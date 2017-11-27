<?php

namespace minewar\game;

use minewar\Loader;
use pocketmine\Player;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\Location;
use minewar\entities\ArenaNPC;
use minewar\create\CreateEntity;
use pocketmine\block\Block;
use pocketmine\tile\Chest;
use minewar\entities\Shop;
use pocketmine\item\Item;
use minewar\entities\Hub;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;
use pocketmine\entities\MineWarEntity;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\utils\Config;
use ZipArchive;

class Arena{

private $name = "";
private $level = null;
private $players = [];
private $border = null;
private $lobbynpc = null;
private $shops = null;
private $waitlobby = null;
private $maxheight = 0;
private $minheight = 0;
private $blocked = null;

static $wall = true;

private $status = 1;
private $blocks = null;
private $start = 30;
private $time = 0; //maximun 15 minutes
private $reset = 10;
private $entities = [];

public function __construct(Config $config){
$this->name = $config->get("name");
$this->level = $config->get("world");
$this->spawns = $config->get("positions");
$this->blocked = $config->get("blocks");
$this->waitlobby = $config->get("waitpos");
$this->maxheight = $config->get("maxheight");
$this->minheight = $config->get("minheight");
$this->border = $config->get("border");
$this->lobbynpc = $config->get("lobbynpc");
$this->shops = $config->get("shops");
$pos1 = explode(":", $this->border[0]);
$pos2 = explode(":", $this->border[1]);
$xmin = $pos1[0] > $pos2[0] ? $pos2[0] : $pos1[0];
$xmax = $pos1[0] > $pos2[0] ? $pos1[0] : $pos2[0];
$ymin = $pos1[1] > $pos2[1] ? $pos2[1] : $pos1[1];
$ymax = $pos1[1] > $pos2[1] ? $pos1[1] : $pos2[1];
$zmin = $pos1[2] > $pos2[2] ? $pos2[2] : $pos1[2];
$zmax = $pos1[2] > $pos2[2] ? $pos1[2] : $pos2[2];
for($x = $xmin; $x <= $xmax; ++$x){
for($y = $ymin; $y <= $ymax; ++$y){
for($z = $zmin; $z <= $zmax; ++$z){
if($x === "" and $y === "" and $z === ""){
continue;
}
//$block = $this->getLevel()->getBlock(new Vector3($x, $y, $z));
//$this->blocks[Level::blockHash($block->getFloorX(), $block->getFloorY(), $block->getFloorZ())] = $block;
$this->blocks["$x:$y:$z"] = true;
}
}
}
//continue
}

public function isBorder($textcoor): bool{
return array_key_exists($textcoor, $this->blocks);
}

public function isBorderActive(): bool{
return self::$wall;
}

public function getMaxY(): int{
return intval($this->maxheight);
}

public function getMinY(): int{
return intval($this->minheight);
}

public function isBlockedBlock($data): bool{
return in_array($data, $this->blocked);
}

public function getPlayerSpawn($player): Vector3{
$position = $this->players[$player]["Position"];
$spawn = explode(":", $this->spawns[$position]);
return (new Vector3(floatval($spawn[0]), floatval($spawn[1]), floatval($spawn[2])));
}

public function startMatch(){
foreach(array_keys($this->players) as $name){
$player = $this->getPlugin()->getServer()->getPlayer($name);
$player->setGamemode(0);
$player->getInventory()->clearAll();
$player->removeAllEffects();
$pos = $this->getPlayerSpawn($name);
if(!$this->getLevel()->isChunkLoaded($chunkX = $pos->x >> 4, $chunkZ = $pos->z >> 4)){
$this->getLevel()->loadChunk($chunkX, $chunkZ);
}
$player->teleport($pos);
$packet = new LevelEventPacket;
$packet->evid = LevelEventPacket::EVENT_GUARDIAN_CURSE;
$packet->data = 0;
$player->dataPacket($packet);
$player->getInventory()->setContents([Item::get(Item::IRON_PICKAXE), Item::get(Item::STONE_SWORD, 0, 1), Item::get(Item::BREAD, 0, 32)]);
$player->getInventory()->sendContents($player);
$player->getInventory()->setArmorContents([Item::get(Item::LEATHER_CAP), Item::get(Item::LEATHER_TUNIC), Item::get(Item::LEATHER_PANTS), Item::get(Item::LEATHER_BOOTS)]);
$player->getInventory()->sendArmorContents($player);
$player->addTitle(TextFormat::YELLOW."Mine".TextFormat::GOLD."War", TextFormat::GRAY.TextFormat::BOLD."» ".TextFormat::RESET.TextFormat::WHITE."Enjoy your game!".TextFormat::BOLD.TextFormat::GRAY." «".TextFormat::RESET, 25, 25, 25);
}
foreach($this->getLevel()->getTiles() as $chest){
if($chest instanceof Chest){
$inventory = $chest->getInventory();
$inventory->clearAll();
$normal = [Item::get(Item::GOLD_INGOT, 0, mt_rand(1, 2)), Item::get(Item::IRON_INGOT, 0, mt_rand(1, 2)), Item::get(Item::ARROW, 0, mt_rand(1, 10)), Item::get(Item::TORCH, 0, mt_rand(5, 10)), Item::get(Item::PLANK, 0, mt_rand(5, 10))];
$lucky = [];//v2 lucky chests?
for($i = 0; $i < 3; ++$i){
$max = $inventory->getSize();
$inventory->setItem(mt_rand(0, $max), $normal[array_rand($normal)]);
}
}
}
}

public function getPlugin(): Loader{
return Loader::api();
}

public function getLevel(): Level{
$level = $this->getPlugin()->getServer()->getLevelByName($this->getLevelName());
return $level;
}

public function getName(): string{
return $this->name;
}

public function isAvailable(): bool{
return $this->getStatus() === "Waiting";
}

private function getBorder(): Array{
return $this->border;
}

private function getCount(): int{
return count($this->players);
}

public function getStatus(): string{
if($this->status === 1){
return "Waiting";
}
if($this->status === 2){
return "Starting";
}
if($this->status === 3){
return "Playing";
}
if($this->status === 4){
return "Restarting";
}
return "Unknown";
}

public function prepareGame(){
$lobbynpc = explode(":", $this->lobbynpc);
$lobby = CreateEntity::createEntity($this->getLevel(), floatval($lobbynpc[0]), floatval($lobbynpc[1]), floatval($lobbynpc[2]), "Hub");
$this->entities[] = $lobby;
$shop1 = explode(":", $this->shops[0]);
$shopOne = CreateEntity::createEntity($this->getLevel(), floatval($shop1[0]), floatval($shop1[1]), floatval($shop1[2]), "Shop");
$this->entities[] = $shopOne;
$shop2 = explode(":", $this->shops[1]);
$shopTwo = CreateEntity::createEntity($this->getLevel(), floatval($shop2[0]), floatval($shop2[1]), floatval($shop2[2]), "Shop");
$this->entities[] = $shopTwo;
$this->getLevel()->setTime(7000);
$this->getLevel()->stopTime();
}

private function setStatus(int $value){
$this->status = $value;
}

public function addPlayer(Player $player){
$this->players[$player->getName()] = ["Position" => $this->getCount()];
$player->setGamemode(2);
$player->setHealth(20);
$player->getInventory()->clearAll();
$player->removeAllEffects();
$player->setFood(20);
}

public function removePlayer(Player $player, $updateGameMode = true){
unset($this->players[$player->getName()]);
if($updateGameMode){
$player->setGamemode(0);
}
$player->setHealth(20);
$player->getInventory()->clearAll();
$player->removeAllEffects();
$player->setFood(20);
}

private function updateNPC(){
$entity = $this->getPlugin()->entities[$this->getName()];
if(is_null($entity)){
return;
}
$colors = [TextFormat::LIGHT_PURPLE, TextFormat::DARK_PURPLE];
$status = $this->getStatus() === "Waiting" ? TextFormat::YELLOW."CLICK TO PLAY" : ($this->getStatus() === "Starting" ? TextFormat::GOLD."STARTING..." : ($this->getStatus() === "Playing" ? TextFormat::RED."  -CLOSED-" : TextFormat::RED."-RESTARTING-"));
$entity->setNameTag(TextFormat::BOLD." ".$status.PHP_EOL."     ".TextFormat::BOLD.$colors[mt_rand(0, 1)].strtoupper($this->getName())." ".PHP_EOL."    ".TextFormat::RESET.TextFormat::GREEN.$this->getCount()." PLAYING");
$rand = mt_rand(1, 15);
$entity->setColor($rand);
}

public function isPlaying($player): bool{
return array_key_exists($player, $this->players);
}

private function getTime(): int{
return $this->time;
}

private function getCountdown(): int{
return $this->start;
}

private function startCountdown(){
$this->start--;
}

private function resetCountdown(){
$this->start = 30;
}

private function stopCountdown(){
$this->start++;
}

private function startTime(){
$this->time++;
}

private function stopTime(){
$this->time = 0;
}

private function startReset(){
$this->reset--;
}

private function removeReset(){
$this->reset = 10;
}

private function getReset(): int{
return $this->reset;
}

private function stopReset(){
$this->reset++;
}

public function resetGame(){
foreach($this->entities as $entity){
$entity->close();
}
foreach($this->getLevel()->getPlayers() as $player){
if($this->isPlaying($player->getName())){//security xD
$this->removePlayer($player, true);
}
if($player->getGamemode() === 3){
$player->setGamemode(0);
}
$player->sendMessage(TextFormat::GREEN."Restarting game...");
$player->getInventory()->clearAll();
$player->setHealth(20);
$player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
}
$this->removeReset();
$this->stopReset();
$this->restoreMap(false);
}

public function getLevelName(): string{
return $this->level;
}

public function restoreMap($delete = true){
if($delete){
foreach($this->entities as $ent){
$ent->close();
}
}
$zip = new ZipArchive();
$this->getPlugin()->getServer()->unloadLevel($this->getLevel());
if($zip->open($this->getPlugin()->getDataFolder()."/maps/".$this->getLevelName().".zip")){
$zip->extractTo($this->getPlugin()->getServer()->getDataPath()."worlds/".$this->getLevelName());
$this->getPlugin()->getServer()->loadLevel($this->getLevelName());
}
self::$wall = true;
$this->prepareGame();
$this->stopTime();
$this->resetCountdown();
$this->setStatus(1);
}

public function getLobby(): Position{
$pos = explode(":", $this->waitlobby);
return (new Position(floatval($pos[0]), floatval($pos[1]), floatval($pos[2]), $this->getLevel()));
}

public function updateGame(){
$this->updateNPC();
if($this->getCount() === 0 and $this->getStatus() === "Waiting"){
$this->stopCountdown();
$this->setStatus(1);
}
if($this->getCount() === 1){
$player = $this->getPlugin()->getServer()->getPlayer(array_keys($this->players)[0]);
if(is_null($player)){
return;
}
static $dot = 0;
static $dots = "";
if($dot === 0){
$dots = ".";
$dot++;
}elseif($dot === 1){
$dots .= ".";
$dot++;
}elseif($dot === 2){
$dots .= ".";
$dot++;
}else{
$dots = "";
$dot = 0;
}
$player->sendTip(TextFormat::BOLD.TextFormat::GRAY."» ".TextFormat::RESET.TextFormat::RED."Waiting for your oponent".$dots);
}
if($this->getCount() === 2 and $this->getStatus() === "Waiting"){
$this->resetCountdown();
$this->startCountdown();
$this->setStatus(2);
}
if($this->getCountdown() > 1 and $this->getCountdown() < 30 and $this->getStatus() === "Starting"){
foreach(array_keys($this->players) as $name){
$player = $this->getPlugin()->getServer()->getPlayer($name);
$player->sendTip(TextFormat::GREEN."Starting in: ".gmdate("i:s", $this->getCountdown())." seconds.");
}
$this->startCountdown();
}
if($this->getCount() == 1 and $this->getStatus() === "Starting"){
$this->setStatus(1);
$this->resetCountdown();
$this->stopCountdown();
}
if($this->getCountdown() === 1 and $this->getCount() === 2){
$this->resetCountdown();
$this->stopCountdown();
$this->startTime();
$this->setStatus(3);
$this->startMatch();
}
if($this->getTime() === 60 || $this->getTime() === 60*2 || $this->getTime() === 60*3 || $this->getTime() === 60*4 || $this->getTime() === 60*5 || $this->getTime() === 60*5.5){
foreach(array_keys($this->players) as $name){
$player = $this->getPlugin()->getServer()->getPlayer($name);
$left = gmdate("i:s", 60*6-$this->getTime());
$player->sendMessage(TextFormat::BOLD.TextFormat::RED."NOTICE ".TextFormat::GRAY."» ".TextFormat::RESET.TextFormat::YELLOW."The wall will desappear in: ".TextFormat::GOLD.($left < gmdate("i:s", 60) ? $left.TextFormat::YELLOW." second/s." : $left.TextFormat::YELLOW." minute/s."));
}
}
if($this->getTime() === 60*6){
foreach(array_keys($this->blocks) as $textvector){
$coor = explode(":", $textvector);
if($this->getLevel()->getBlockIdAt($coor[0], $coor[1], $coor[2]) === 82){
continue;
}
$this->getLevel()->setBlockIdAt($coor[0], $coor[1], $coor[2], 0);
}
foreach(array_keys($this->players) as $name){
$player = $this->getPlugin()->getServer()->getPlayer($name);
$player->sendMessage(TextFormat::BOLD.TextFormat::RED."NOTICE ".TextFormat::GRAY."» ".TextFormat::RESET.TextFormat::GREEN."Border desappeared, Good Luck!");
}
self::$wall = false;
}
if($this->getStatus() === "Playing" and $this->getCount() === 2){
$this->startTime();
foreach(array_keys($this->players) as $name){
$player = $this->getPlugin()->getServer()->getPlayer($name);
$timer = gmdate("i:s", 60*6-$this->getTime());
if(!self::$wall){
$timer = "-DOWN-";
}
$player->sendTip(str_repeat(" ", 17).TextFormat::GOLD.TextFormat::BOLD."Mine".TextFormat::YELLOW."War".PHP_EOL.TextFormat::GREEN."Time: ".TextFormat::RESET.TextFormat::GRAY.gmdate("i:s", $this->getTime()).str_repeat(" ", 15).TextFormat::BOLD.TextFormat::GREEN." Wall: ".TextFormat::RESET.TextFormat::GRAY.$timer);
}
}
if($this->getCount() === 1 and $this->getStatus() === "Playing"){
$winner = $this->getPlugin()->getServer()->getPlayer(array_keys($this->players)[0]);
if(!is_null($winner)){
$this->getPlugin()->getServer()->broadcastMessage(TextFormat::BOLD.TextFormat::GOLD."Mine".TextFormat::YELLOW."War".TextFormat::GRAY." » ".TextFormat::RESET.TextFormat::GREEN.$winner->getName().TextFormat::GRAY." won a match at: ".TextFormat::DARK_PURPLE.$this->getName());
$winner->addTitle(TextFormat::GOLD."Victory!", TextFormat::YELLOW."Congratulations ".TextFormat::RED.$winner->getName(), 50, 50, 50);
$this->removePlayer($winner);
}
$this->removeReset();
$this->startReset();
$this->setStatus(4);
}
if($this->getCount() === 2 and $this->getTime() >= 60*15 and $this->getStatus() === "Playing"){
foreach(array_keys($this->players) as $name){
$player = $this->getPlugin()->getServer()->getPlayer($player);
$player->addTitle(TextFormat::RED."Game Over!", TextFormat::GRAY."Nobody won the match", 50, 50, 50);
$this->removePlayer($player, true);
$player->teleport($this->getPlugin()->getServer()->getDefaultLevel()->getSafeSpawn());
}
$this->removeReset();
$this->startReset();
$this->setStatus(4);
}
if($this->getReset() > 1 and $this->getReset() < 10 and $this->getStatus() === "Restarting"){
foreach($this->getLevel()->getPlayers() as $player){
$player->sendTip(TextFormat::RED."Reseting in: ".gmdate("i:s", $this->getReset()));
}
$this->startReset();
}
if($this->getReset() === 1){
$this->resetGame();
}
}

public function __destruct(){
}

}