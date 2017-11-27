<?php

namespace minewar\entities;

class ArenaNPC extends MineWarEntity{

const NETWORK_ID = 13;

public function setColor(int $color){
$this->setDataProperty(self::DATA_COLOR, self::DATA_TYPE_BYTE, $color);
}

public function getName(){
return "";
}

public function getArena(){
return $this->namedtag["Arena"];
}


}