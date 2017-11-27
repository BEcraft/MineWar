<?php

namespace minewar\lucky;

use minewar\entities\MineWarEntity;
use pocketmine\utils\TextFormat;

class Lucky extends MineWarEntity{

const NETWORK_ID = 37;

public function getName(){
return TextFormat::YELLOW.TextFormat::BOLD."Lucky ".TextFormat::GREEN."Slime".TextFormat::RESET;
}

}