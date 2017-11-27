<?php

namespace minewar\task;

use minewar\Loader;
use pocketmine\scheduler\PluginTask;

class Timer extends PluginTask{

public function __construct(Loader $plugin){
parent::__construct($plugin);
}

public function onRun($tick){
$games = $this->getOwner()->games;
if(count($games) === 0){
return;
}
foreach($games as $arena){
$arena->updateGame();
}
}

}