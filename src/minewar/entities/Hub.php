<?php

namespace minewar\entities;

use pocketmine\utils\TextFormat;

class Hub extends MineWarEntity{

const NETWORK_ID = 21;

public function getName(){
return TextFormat::BOLD.TextFormat::YELLOW."CLICK TO RETURN";
}

}